<?php

namespace RKW\OaiConnector\Repository;

use RKW\OaiConnector\Model\OaiItemMeta;
use RKW\OaiConnector\Utility\Pagination;
use PDO;

/**
 * OaiItemMetaRepository
 *
 * Repository class for handling OAI Item Metadata-related database operations.
 */
class OaiItemMetaRepository extends AbstractRepository
{

    /**
     * modelClass
     * Fully qualified model class name, defaults to OaiItemMeta::class
     *
     * @var string|null
     */
    protected ?string $modelClass = OaiItemMeta::class;


    /**
     * tableName
     * Database table name, defaults to 'oai_item_meta'
     *
     * @var string|null
     */
    protected ?string $tableName = 'oai_item_meta';


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
        parent::applyRepoContextToWhere($whereClauses, $params);

        // Always ignore historical rows
        $whereClauses[] = 'history = 0';

        // Always ignore deleted rows
        $whereClauses[] = 'deleted = 0';
    }


    /**
     * Find all items by repo ID, optionally paginated.
     *
     * @param int $repoId
     * @param Pagination|null $pagination
     * @return array
     */
    public function findAllByRepoId(int $repoId, ?Pagination $pagination = null): array
    {
        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE repo = :repoId';

        if ($pagination) {
            $sql .= ' LIMIT ' . $pagination->getLimit() . ' OFFSET ' . $pagination->getOffset();
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['repoId' => $repoId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Count all items by repo ID.
     *
     * @param int $repoId
     * @return int
     */
    public function countByRepoId(int $repoId): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->getTableName() . ' WHERE repo = :repoId';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['repoId' => $repoId]);

        return (int)$stmt->fetchColumn();
    }


    /**
     * find all by ID list
     *
     * @deprecated Does not use "history" and "deleted" flags. Use "findBy" function instead
     *
     * @param string $repoId
     * @param array $prefixedIds
     * @return array
     */
    public function findByIdList(string $repoId, array $prefixedIds): array
    {
        if (empty($prefixedIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($prefixedIds), '?'));
        $sql = "SELECT identifier FROM oai_item_meta WHERE repo = ? AND identifier IN ($placeholders)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge([$repoId], $prefixedIds));

        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'identifier');

    }

}

