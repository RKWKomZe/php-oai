<?php

namespace RKW\OaiConnector\Repository;

use RKW\OaiConnector\Model\OaiItemMeta;
use RKW\OaiConnector\Utility\Pagination;
use PDO;
use Symfony\Component\VarDumper\VarDumper;

class OaiItemMetaRepository extends AbstractRepository
{
    protected ?string $modelClass = OaiItemMeta::class;

    protected ?string $tableName = 'oai_item_meta';

    /*
    protected function getTableName(): string
    {
        return 'oai_item_meta';
    }
    */

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

