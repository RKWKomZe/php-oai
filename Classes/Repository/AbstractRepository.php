<?php

namespace RKW\OaiConnector\Repository;

use PDO;
use PDOException;
use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\Pagination;

/**
 * AbstractRepository
 *
 * Abstract class providing a generic repository implementation with methods to handle database operations.
 */
abstract class AbstractRepository implements RepoContextAwareInterface
{

    /**
     * settings
     *
     * @var array
     */
    protected array $settings = [];


    /**
     * pdo
     *
     * @var \PDO
     */
    protected PDO $pdo;


    /**
     * contextRepoId
     *
     * @var string|null
     */
    protected ?string $contextRepoId = '';


    /**
     * tempPagination
     *
     * @var \RKW\OaiConnector\Utility\Pagination|null
     */
    protected ?\RKW\OaiConnector\Utility\Pagination $tempPagination = null;


    /**
     * @var array|null
     */
    protected ?array $tempSorts = [];


    /**
     * modelClass
     * Optional model class for mapping
     *
     * @var string|null
     */
    protected ?string $modelClass = null;


    /**
     * returnModels
     * Internal flag for model-return mode
     *
     * @var bool
     */
    protected bool $returnModels = false;


    /**
     * constructor
     *
     * @param string|null $repoName
     */
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
     * Retrieves all records from the database, applying repository context
     * and optional pagination if set.
     *
     * @return array The list of records as associative arrays.
     * @throws \ReflectionException
     */
    public function findAll(): array
    {
        // Prefer explicitly passed argument, otherwise temporary state
        $pagination = $this->tempPagination;
        $sorts      = $this->tempSorts;

        // Reset afterwards to avoid side effects
        $this->tempPagination = null;
        $this->tempSorts = [];

        $whereClauses = [];
        $params = [];
        $this->applyRepoContextToWhere($whereClauses, $params);

        $sql = 'SELECT * FROM ' . $this->getTableName();
        if ($whereClauses) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        if (!empty($sorts)) {
            $orderParts = [];
            foreach ($sorts as $s) {
                // Column/direction already validated in withSort()
                $orderParts[] = '`' . $s['column'] . '` ' . $s['direction'];
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderParts);
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
     * Fetches a record by its unique identifier.
     *
     * Executes a SQL query to retrieve a single record from the database
     * where the `id` matches the provided value. The function ensures
     * additional context conditions are applied to the query, if applicable.
     *
     * @param string $id The unique identifier of the record to retrieve.
     * @return array|object|null Returns the record as an array or object if found, or null if no matching record exists.
     *
     * @throws \PDOException If there is an issue with query execution.
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
     * Inserts a new record into the database based on the provided model.
     *
     * Generates an `INSERT` SQL query dynamically by extracting private and protected
     * properties of the provided object through reflection. It expects the object
     * to have getter methods conforming to the naming convention `get<PropertyName>`.
     *
     * @param object $model The object representing the data to be inserted. Its properties
     *                      are extracted and used as the column values for the insertion.
     * @return bool Returns true if the insertion is successful, false otherwise.
     *
     * @throws \RuntimeException If the table name is not defined in the repository.
     * @throws \PDOException If the query preparation or execution fails.
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
                // ✅ Backtick-escape column names
                $columns[] = '`' . $name . '`';
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

        try {
            return $this->pdo->prepare($sql)->execute($values);
        } catch (\PDOException $e) {
            // Duplicate primary key
            if ((int)$e->errorInfo[1] === 1062) {
                throw new \RuntimeException(
                    sprintf('A record with the same primary key already exists in "%s".', $this->tableName),
                    1062,
                    $e
                );
            }

            // Re-throw all other DB errors
            throw $e;
        }
    }


    /**
     * Inserts or updates a record in the database based on its unique key(s).
     *
     * @param object $model The data model instance to insert or update in the database.
     *                       The model should have private or protected properties with
     *                       corresponding public getter methods for each property.
     * @return bool Returns true on successful execution of the query, false otherwise.
     *
     * @throws \RuntimeException If the table name is not defined in the repository.
     * @throws \ReflectionException If the given model class cannot be reflected.
     * @throws \PDOException If there is an issue with executing the SQL query.
     */
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

            // escape column names with backticks to avoid reserved keyword conflicts
            $escapedName = '`' . str_replace('`', '``', $name) . '`';

            $columns[] = $escapedName;
            $placeholders[] = ':' . $name;
            $updateClauses[] = "{$escapedName} = :update_{$name}";

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
     * Updates a record in the database based on the provided model and primary key.
     *
     * @param object $model The model object containing the data to update.
     * @param array|string $primaryKey The primary key(s) to identify the record. Defaults to 'id'.
     * @return bool Returns true if the update operation was successful, or false otherwise.
     *
     * @throws \RuntimeException If the table name is not defined in the repository.
     * @throws \InvalidArgumentException If primary key value(s) are null, empty, or cannot be resolved.
     * @throws \PDOException If there is an issue with query execution.
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

            // Quote all column names with backticks to avoid MySQL reserved word conflicts
            $quotedName = '`' . str_replace('`', '``', $name) . '`';

            if (in_array($name, $primaryKeys, true)) {
                if ($value === null || $value === '') {
                    throw new \InvalidArgumentException("Primary key '{$name}' must not be null or empty.");
                }
                $whereClauses[] = "{$quotedName} = :__pk_{$name}";
                $values["__pk_{$name}"] = $value;
            } else {
                $columns[] = "{$quotedName} = :{$name}";
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

        return $this->pdo->prepare($sql)->execute($values);
    }


    /**
     * Deletes a specific record from the database based on its primary key(s).

     * @param object $model The model instance representing the record to delete. The model must provide appropriate getter methods for the primary keys.
     * @param array|string $primaryKeys The primary key(s) used to identify the record. Can be a string for a single key or an array of keys.
     * @return bool Returns true on successful deletion, or false on failure.
     *
     * @throws \RuntimeException If the table name in the repository is not defined.
     * @throws \InvalidArgumentException If the model does not have getter methods for the provided primary keys or if any primary key value is empty.
     * @throws \PDOException If there is an issue with query preparation or execution.
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

        return $this->pdo->prepare($sql)->execute($parameters);
    }


    /**
     * Retrieves multiple records based on specific criteria and ordering.
     *
     * @param array $criteria An associative array where the keys are column names
     *                        and the values are the corresponding conditions for filtering results.
     * @return array Returns an array of records matching the criteria.
     *
     * @throws \ReflectionException
     */
    public function findBy(array $criteria = []): array {

        $this->requireRepoContext();
        //$this->requireCriteria($criteria);

        // Prefer explicitly passed argument, otherwise temporary state
        $pagination = $this->tempPagination;
        $sorts      = $this->tempSorts;

        // Reset afterwards to avoid side effects
        $this->tempPagination = null;
        $this->tempSorts = [];

        $whereClauses = [];
        $params = [];

        foreach ($criteria as $column => $value) {

            // Support IN() queries when value is an array
            if (is_array($value)) {
                // Empty array → condition can never be true, avoid "IN ()" syntax error
                if ($value === []) {
                    $whereClauses[] = '1 = 0';
                    continue;
                }

                $placeholders = [];
                foreach ($value as $idx => $singleValue) {
                    $paramName = ':' . $column . '_' . $idx;
                    $placeholders[] = $paramName;
                    $params[$paramName] = $singleValue;
                }

                $whereClauses[] = sprintf(
                    '%s IN (%s)',
                    $column,
                    implode(', ', $placeholders)
                );
            } else {
                $paramName = ':' . $column;
                $whereClauses[] = "$column = $paramName";
                $params[$paramName] = $value;
            }
        }

        $this->applyRepoContextToWhere($whereClauses, $params);

        /*
        if ($this->contextRepoId !== null) {
            $whereClauses[] = 'repo_id = :__repoId';
            $params[':__repoId'] = $this->contextRepoId;
        }
        */


        $sql = 'SELECT * FROM ' . $this->getTableName();

        if (!empty($whereClauses)) {
            $sql .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        if (!empty($sorts)) {
            $orderParts = [];
            foreach ($sorts as $s) {
                // Column/direction already validated in withSort()
                $orderParts[] = '`' . $s['column'] . '` ' . $s['direction'];
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderParts);
        }

        if ($pagination) {
            $sql .= ' LIMIT ' . $pagination->getLimit() . ' OFFSET ' . $pagination->getOffset();
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $this->finalizeResult($rows);
    }


    /**
     * Retrieves a single record based on specific criteria.
     *
     * @param array $criteria An associative array of column-value pairs representing the query conditions.
     * @return array|object|null Returns the matched record as an array or object, or null if no result is found.
     *
     * @throws \InvalidArgumentException If the $criteria array is empty or invalid.
     * @throws \PDOException If there is a problem executing the query.
     */
    public function findOneBy(array $criteria): array|object|null
    {
        //$this->requireRepoContext();
        $this->requireCriteria($criteria);

        // Prefer explicitly passed argument, otherwise temporary state
        $sorts = $this->tempSorts;

        // Reset afterwards to avoid side effects
        $this->tempSorts = [];

        $whereClauses = [];
        $params = [];

        foreach ($criteria as $column => $value) {
            $paramName = ':' . $column;
            $whereClauses[] = "$column = $paramName";
            $params[$paramName] = $value;
        }

        // Add repo context
        $this->applyRepoContextToWhere($whereClauses, $params);

        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . implode(' AND ', $whereClauses);

        if (!empty($sorts)) {
            $orderParts = [];
            foreach ($sorts as $s) {
                // Column/direction already validated in withSort()
                $orderParts[] = '`' . $s['column'] . '` ' . $s['direction'];
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderParts);
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ? $this->finalizeResult($result): null;
    }


    /**
     * Counts the number of records matching the specified criteria.
     *
     * @param array $criteria An associative array where the keys are column names,
     *                         and the values are the corresponding filter values.
     *                         Example: ['column1' => 'value1', 'column2' => 'value2'].
     *
     * @return int The number of records that match the given criteria.
     *
     * @throws \InvalidArgumentException If the provided criteria are invalid.
     * @throws \PDOException If an error occurs during query preparation or execution.
     */
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


    /**
     * Initializes and returns a PDO instance for database interaction.
     *
     * @return \PDO The initialized PDO instance for database operations.
     *
     * @throws \PDOException If there is an issue while creating the PDO instance.
     */
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
     * Retrieves the name of the database table associated with the current repository.
     *
     * @return string The name of the database table.
     */
    protected function getTableName(): string
    {
        return $this->tableName;
    }


    /**
     * Ensures that the repository context identifier is set.
     *
     * @throws \RuntimeException If the `repo_id` context is not set.
     */
    protected function ensureContextRepoId(): void
    {
        if ($this->contextRepoId === null) {
            throw new \RuntimeException('1749558557: Repository context (repo_id) must be set before querying.');
        }
    }


    /**
     * Applies repository-specific context to SQL query conditions.
     *
     * @param array &$whereClauses An array of SQL `WHERE` clause components to be modified.
     * @param array &$params An associative array of parameters used in the SQL query, to which the repository context will be appended.
     *
     * @return void
     *
     * @throws \RuntimeException If the repository context is not set but expected.
     */
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


    /**
     * Retrieves the column name used for the repository identifier.
     *
     * @return string The name of the repository identifier column.
     */
    protected function getRepoColumnName(): string
    {
        return 'repo';
    }


    /**
     * Sets the repository context identifier.
     *
     * @param string $repoId The unique identifier for the repository context.
     * @return void
     */
    public function setContextRepoId(string $repoId): void
    {
        $this->contextRepoId = $repoId;
    }


    /**
     * Retrieves the repository context identifier.
     *
     * Returns the identifier of the repository context currently in use, or null
     * if no context has been set.
     *
     * @return string|null The repository context identifier or null if not set.
     */
    public function getContextRepoId(): ?string
    {
        return $this->contextRepoId;
    }


    /**
     * Adds a pagination configuration to the current query context.
     *
     * @param Pagination $pagination The pagination object that defines limit, offset, and other pagination parameters.
     * @return static Returns the current instance for method chaining.
     */
    public function withPagination(Pagination $pagination): static
    {
        $this->tempPagination = $pagination;
        return $this;
    }


    /**
     * Set a temporary sort for the next read query.
     *
     * @param string $column   Column to sort by (e.g. 'datestamp')
     * @param string $direction 'ASC' or 'DESC' (default: 'ASC')
     * @return self
     */
    public function withSort(string $column, string $direction = 'ASC'): self
    {
        // Basic whitelist for column identifier to avoid SQL injection via ORDER BY
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new \InvalidArgumentException("Invalid sort column: {$column}");
        }

        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException("Invalid sort direction: {$direction}");
        }

        $this->tempSorts[] = ['column' => $column, 'direction' => $direction];
        return $this;
    }


    /**
     * @param array $sorts
     * @return $this
     */
    public function withSorts(array $sorts): self
    {
        // Normalize to list of [column, direction]
        $normalized = [];

        // Associative array form: ['name' => 'ASC', 'datestamp' => 'DESC']
        $isAssoc = array_keys($sorts) !== range(0, count($sorts) - 1);
        if ($isAssoc) {
            foreach ($sorts as $col => $dir) {
                $normalized[] = [(string)$col, (string)$dir];
            }
        } else {
            // List form: [['name','ASC'], ['datestamp','DESC']]
            foreach ($sorts as $pair) {
                if (!is_array($pair) || count($pair) < 1) {
                    throw new \InvalidArgumentException('Invalid sorts entry.');
                }
                $col = (string)$pair[0];
                $dir = isset($pair[1]) ? (string)$pair[1] : 'ASC';
                $normalized[] = [$col, $dir];
            }
        }

        foreach ($normalized as [$column, $direction]) {
            $this->withSort($column, $direction); // validation reused
        }

        return $this;
    }


    /**
     * @return void
     */
    public function clearSort(): void
    {
        $this->tempSorts = [];
    }


    /**
     * Enables the return of results as model objects.
     *
     * @return static Returns the current instance for method chaining.
     */
    public function withModels(): static
    {
        $this->returnModels = true;
        return $this;
    }


    /**
     * Finalizes the result by optionally mapping to model objects.
     *
     * @param array $rows
     * @return array|object|null
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
     * Helper: detects whether an array is a flat associative row, not a list
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
