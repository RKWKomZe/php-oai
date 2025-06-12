<?php

namespace RKW\OaiConnector\Repository;

use RKW\OaiConnector\Model\Repo;
use PDO;

class RepoDescriptionRepository extends AbstractRepository
{


    public function addMultiple(array $descriptions): void
    {
        $stmt = $this->pdo->prepare('
        INSERT IGNORE INTO oai_repo_description
        (repo, description, rank, updated, comment)
        VALUES (:repo, :description, :rank, :updated, :comment)
    ');
        foreach ($descriptions as $desc) {
            $stmt->execute([
                ':repo' => $desc->repo,
                ':description' => $desc->description,
                ':rank' => $desc->rank,
                ':updated' => $desc->updated,
                ':comment' => $desc->comment,
            ]);
        }
    }


    public function findByRepoId(string $repoId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM oai_repo_description WHERE repo = :repo');
        $stmt->execute([':repo' => $repoId]);
        return array_map(fn($row) => new RepoDescription($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    public function deleteByRepoId(string $repoId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM oai_repo_description WHERE repo = :repo');
        $stmt->execute([':repo' => $repoId]);
    }

}

