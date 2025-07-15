<?php

namespace RKW\OaiConnector\Repository;

use PDO;
use PDOException;
use RKW\OaiConnector\Mapper\GenericModelMapper;
use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\Pagination;

/**
 * AbstractRepository
 */
abstract class AbstractRepository implements RepoContextAwareInterface
{
    protected array $settings = [];
    protected PDO $pdo;

    protected ?string $contextRepoId = '';

    protected ?\RKW\OaiConnector\Utility\Pagination $tempPagination = null;

    public function __construct(?string $repoName = null)
    {
        // Only needed if context is used. The "repo" has a similar context to the StoragePid in TYPO3
        if ($repoName) {
            $this->setContextRepoId($repoName);
        }

        $this->settings = ConfigLoader::load();
        $this->pdo = $this->initPdo();
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function findAll(): array
    {
        // Bevorzugt explizit übergebenes Argument, ansonsten temporären Zustand
        $pagination = $this->tempPagination;

        // Danach zurücksetzen, um Seiteneffekte zu vermeiden
        $this->tempPagination = null;

        $whereClauses = [];
        $params = [];
        $this->applyRepoContextToWhere($whereClauses, $params);

        $sql = 'SELECT * FROM ' . $this->getTableName();
        if ($whereClauses) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        if ($pagination) {
            $sql .= ' LIMIT ' . $pagination->getLimit() . ' OFFSET ' . $pagination->getOffset();
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $this->finalizeResult($rows);
    }


    /**
     * @param string $id
     * @return array|null
     * @throws \ReflectionException
     */
    public function findById(string $id): array|object|null
    {
        //$this->requireRepoContext();

        $whereClauses = ['id = :id'];
        $params = [':id' => $id];

        // Inject repo_id condition via context (throws if not set)
        $this->applyRepoContextToWhere($whereClauses, $params);

        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . implode(' AND ', $whereClauses) . ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ? $this->finalizeResult($result): null;
    }


    /**
     *  UNTESTED!
     */
    public function insert(object $model): bool
    {
        if (empty($this->tableName)) {
            throw new \RuntimeException('No table name defined in repository.');
        }

        $reflection = new \ReflectionClass($model);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED);

        $columns = [];
        $placeholders = [];
        $values = [];

        foreach ($properties as $property) {
            $name = $property->getName();
            $getter = 'get' . ucfirst($name);

            if (method_exists($model, $getter)) {
                $columns[] = $name;
                $placeholders[] = ':' . $name;
                $values[$name] = $model->$getter();
            }
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->tableName,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }


    public function upsert(object $model): bool
    {
        if (empty($this->tableName)) {
            throw new \RuntimeException('No table name defined in repository.');
        }

        $reflection = new \ReflectionClass($model);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED);

        $columns = [];
        $placeholders = [];
        $updateClauses = [];
        $values = [];

        foreach ($properties as $property) {
            $name = $property->getName();
            $getter = 'get' . ucfirst($name);

            if (!method_exists($model, $getter)) {
                continue;
            }

            $value = $model->$getter();
            $columns[] = $name;
            $placeholders[] = ':' . $name;
            $updateClauses[] = "{$name} = :update_{$name}";

            $values[$name] = $value;
            $values["update_{$name}"] = $value;
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $this->tableName,
            implode(', ', $columns),
            implode(', ', $placeholders),
            implode(', ', $updateClauses)
        );

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }




    /**
     *  UNTESTED!
     */
    public function update(object $model, array|string $primaryKey = 'id'): bool
    {
        if (empty($this->tableName)) {
            throw new \RuntimeException('No table name defined in repository.');
        }

        // Support both single key and multiple keys
        $primaryKeys = is_array($primaryKey) ? $primaryKey : [$primaryKey];

        $reflection = new \ReflectionClass($model);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED);

        $columns = [];
        $values = [];
        $whereClauses = [];

        foreach ($properties as $property) {
            $name = $property->getName();
            $getter = 'get' . ucfirst($name);

            if (!method_exists($model, $getter)) {
                continue;
            }

            $value = $model->$getter();

            if (in_array($name, $primaryKeys, true)) {
                if ($value === null || $value === '') {
                    throw new \InvalidArgumentException("Primary key '{$name}' must not be null or empty.");
                }
                $whereClauses[] = "{$name} = :__pk_{$name}";
                $values["__pk_{$name}"] = $value;
            } else {
                $columns[] = "{$name} = :{$name}";
                $values[$name] = $value;
            }
        }

        if (empty($whereClauses)) {
            throw new \InvalidArgumentException('No primary key values found.');
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $this->tableName,
            implode(', ', $columns),
            implode(' AND ', $whereClauses)
        );

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }




    /**
     *  UNTESTED!
     */
    public function delete(object $model, array|string $primaryKeys = 'id'): bool
    {
        if (empty($this->tableName)) {
            throw new \RuntimeException('No table name defined in repository.');
        }

        // Umwandlung in Array, falls nur ein Key übergeben wurde
        $primaryKeys = (array) $primaryKeys;
        $conditions = [];
        $parameters = [];

        foreach ($primaryKeys as $key) {
            $getter = 'get' . ucfirst($key);
            if (!method_exists($model, $getter)) {
                throw new \InvalidArgumentException("Model has no getter for primary key '{$key}'");
            }

            $value = $model->$getter();
            if ($value === null || $value === '') {
                throw new \InvalidArgumentException("Primary key '{$key}' must not be empty.");
            }

            $conditions[] = "$key = :$key";
            $parameters[$key] = $value;
        }

        $sql = sprintf(
            'DELETE FROM %s WHERE %s',
            $this->tableName,
            implode(' AND ', $conditions)
        );

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($parameters);
    }





    public function findBy(
        array $criteria = [],
        array $orderBy = []
    ): array {

        $this->requireRepoContext();
        //$this->requireCriteria($criteria);

        // Bevorzugt explizit übergebenes Argument, ansonsten temporären Zustand
        $pagination = $this->tempPagination;

        // Danach zurücksetzen, um Seiteneffekte zu vermeiden
        $this->tempPagination = null;

        $whereClauses = [];
        $params = [];

        foreach ($criteria as $column => $value) {
            $paramName = ':' . $column;
            $whereClauses[] = "$column = $paramName";
            $params[$paramName] = $value;
        }

        $this->applyRepoContextToWhere($whereClauses, $params);

        /*
        if ($this->contextRepoId !== null) {
            $whereClauses[] = 'repo_id = :__repoId';
            $params[':__repoId'] = $this->contextRepoId;
        }
        */


        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . implode(' AND ', $whereClauses);

        if ($pagination) {
            $sql .= ' LIMIT ' . $pagination->getLimit() . ' OFFSET ' . $pagination->getOffset();
        }

        if (!empty($orderBy)) {
            $orderParts = [];
            foreach ($orderBy as $column => $direction) {
                $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderParts[] = "$column $dir";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderParts);
        }

        /*
        if ($pagination) {
            $sql .= ' LIMIT ' . $pagination->getLimit() . ' OFFSET ' . $pagination->getOffset();
        }
        */

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->finalizeResult($rows);
    }




    public function findOneBy(array $criteria, array $orderBy = []): array|object|null
    {
        //$this->requireRepoContext();
        $this->requireCriteria($criteria);


        $whereClauses = [];
        $params = [];

        foreach ($criteria as $column => $value) {
            $paramName = ':' . $column;
            $whereClauses[] = "$column = $paramName";
            $params[$paramName] = $value;
        }

        // Füge repo-Kontext hinzu
        $this->applyRepoContextToWhere($whereClauses, $params);

        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . implode(' AND ', $whereClauses);

        if (!empty($orderBy)) {
            $orderParts = [];
            foreach ($orderBy as $column => $direction) {
                $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
                $orderParts[] = "$column $dir";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderParts);
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ? $this->finalizeResult($result): null;
    }



    public function countBy(array $criteria = []): int
    {
        $this->requireRepoContext();
        $this->requireCriteria($criteria);

        $whereClauses = [];
        $params = [];

        foreach ($criteria as $column => $value) {
            $paramName = ':' . $column;
            $whereClauses[] = "$column = $paramName";
            $params[$paramName] = $value;
        }

        // Kontextbedingung hinzufügen
        $this->applyRepoContextToWhere($whereClauses, $params);

        $sql = 'SELECT COUNT(*) FROM ' . $this->getTableName();
        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }



    protected function initPdo(): PDO
    {
        try {
            $pdo = new PDO(
                'mysql:host=' . $this->settings['database']['host'] . ';dbname=' . $this->settings['database']['name'] . ';charset=utf8mb4',
                $this->settings['database']['user'],
                $this->settings['database']['password']
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die('1749558565: Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Must be implemented in child class to define the database table.
     */
    protected function getTableName(): string
    {
        return $this->tableName;
    }

    protected function ensureContextRepoId(): void
    {
        if ($this->contextRepoId === null) {
            throw new \RuntimeException('1749558557: Repository context (repo_id) must be set before querying.');
        }
    }

    protected function applyRepoContextToWhere(array &$whereClauses, array &$params): void
    {
        // only if the repo context is set
        if ($this->getContextRepoId()) {
            $repoId = $this->getContextRepoId();
            if ($repoId === null) {
                throw new \RuntimeException('1749558537: Missing repository context. Use setRepoContext() or RepoContextManager::set().');
            }

            $column = $this->getRepoColumnName();

            $whereClauses[] = $column . ' = :__repoId';
            $params[':__repoId'] = $repoId;
        }

    }

    /**
     * Ensures that a valid repository context is available and returns it.
     *
     * @return void
     * @throws \RuntimeException if context is not set
     */
    protected function requireRepoContext(): void
    {
        $repoId = $this->getContextRepoId();

        if (!$repoId) {
            throw new \RuntimeException('1749558522: Missing repository context. Use setRepoContext() or RepoContextManager::set().');
        }
    }


    /**
     * Ensures that a non-empty criteria array is passed.
     *
     * @param array $criteria
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function requireCriteria(array $criteria): void
    {
        if (empty($criteria)) {
            throw new \InvalidArgumentException('1749558537: Missing criteria for repository query. At least one condition is required.');
        }
    }


    protected function getRepoColumnName(): string
    {
        return 'repo';
    }



    public function setContextRepoId(string $repoId): void
    {
        $this->contextRepoId = $repoId;
    }

    public function getContextRepoId(): ?string
    {
        return $this->contextRepoId;
    }

    public function withPagination(Pagination $pagination): static
    {
        $this->tempPagination = $pagination;
        return $this;
    }

    // Optional model class for mapping
    protected ?string $modelClass = null;

    // Internal flag for model-return mode
    protected bool $returnModels = false;

    public function withModels(): static
    {
        $this->returnModels = true;
        return $this;
    }

    /**
     * Finalizes the result by optionally mapping to model objects.
     *
     * @param array $rows
     * @return array
     * @throws \ReflectionException
     */
    protected function finalizeResult(array $rows): array|object|null
    {
        $useModel = $this->returnModels;
        $this->returnModels = false;

        // Case: Einzelner Datensatz (flaches Array mit string-keys)
        $isSingleRow = $this->isFlatAssocArray($rows);

        if ($useModel && $this->modelClass && class_exists($this->modelClass)) {
            if ($isSingleRow) {
                return \RKW\OaiConnector\Mapper\GenericModelMapper::map($rows, $this->modelClass);
            } else {
                return array_map(
                    fn($row) => \RKW\OaiConnector\Mapper\GenericModelMapper::map($row, $this->modelClass),
                    $rows
                );
            }
        }

        return $rows;
    }

    /**
     * Helper: detects whether array is a flat associative row, not a list
     */
    protected function isFlatAssocArray(array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        // If at least one key is a string, it's likely an assoc array (single row)
        return array_keys($data) !== range(0, count($data) - 1);
    }

}
