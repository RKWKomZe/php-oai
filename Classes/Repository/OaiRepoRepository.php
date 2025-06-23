<?php

namespace RKW\OaiConnector\Repository;

use RKW\OaiConnector\Model\OaiRepo;

class OaiRepoRepository extends AbstractRepository
{
    protected ?string $modelClass = OaiRepo::class;
    protected ?string $tableName = 'oai_repo';

    /*
    protected function getTableName(): string
    {
        return 'oai_repo';
    }
    */

    protected function getRepoColumnName(): string
    {
        return 'id'; // da hier die repo-sicht selbst gefiltert wird
    }


    /*
        public function findAll(): array
        {
            $stmt = $this->pdo->query('SELECT * FROM oai_repo ORDER BY identifier ASC');
            return array_map(fn($row) => new Repo($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    */

    /*
    public function add(Repo $repo): void
    {
        $stmt = $this->pdo->prepare('
        INSERT IGNORE INTO oai_repo (
            id, repositoryName, baseURL, protocolVersion, adminEmails, earliestDatestamp,
            deletedRecord, granularity, maxListSize, tokenDuration, updated, comment
        ) VALUES (
            :id, :repositoryName, :baseURL, :protocolVersion, :adminEmails, :earliestDatestamp,
            :deletedRecord, :granularity, :maxListSize, :tokenDuration, :updated, :comment
        )
    ');
        $stmt->execute([
            ':id' => $repo->id,
            ':repositoryName' => $repo->repositoryName,
            ':baseURL' => $repo->baseURL,
            ':protocolVersion' => $repo->protocolVersion,
            ':adminEmails' => $repo->adminEmails,
            ':earliestDatestamp' => $repo->earliestDatestamp,
            ':deletedRecord' => $repo->deletedRecord,
            ':granularity' => $repo->granularity,
            ':maxListSize' => $repo->maxListSize,
            ':tokenDuration' => $repo->tokenDuration,
            ':updated' => $repo->updated,
            ':comment' => $repo->comment,
        ]);
    }
    */

    /*
    public function findById(string $id): ?Repo
    {
        $stmt = $this->pdo->prepare('SELECT * FROM oai_repo WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new Repo($row) : null;
    }

    public function update(Repo $repo): void
    {
        $stmt = $this->pdo->prepare('
        UPDATE oai_repo SET
            repositoryName = :repositoryName,
            baseURL = :baseURL,
            protocolVersion = :protocolVersion,
            adminEmails = :adminEmails,
            earliestDatestamp = :earliestDatestamp,
            deletedRecord = :deletedRecord,
            granularity = :granularity,
            maxListSize = :maxListSize,
            tokenDuration = :tokenDuration,
            updated = :updated,
            comment = :comment
        WHERE id = :id
    ');
        $stmt->execute([
            ':repositoryName' => $repo->repositoryName,
            ':baseURL' => $repo->baseURL,
            ':protocolVersion' => $repo->protocolVersion,
            ':adminEmails' => $repo->adminEmails,
            ':earliestDatestamp' => $repo->earliestDatestamp,
            ':deletedRecord' => $repo->deletedRecord,
            ':granularity' => $repo->granularity,
            ':maxListSize' => $repo->maxListSize,
            ':tokenDuration' => $repo->tokenDuration,
            ':updated' => $repo->updated,
            ':comment' => $repo->comment,
            ':id' => $repo->id,
        ]);
    }
    */
}
