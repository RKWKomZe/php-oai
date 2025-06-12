<?php

namespace RKW\OaiConnector\Repository;

interface RepoContextAwareInterface
{
    /**
     * Set the repository context (e.g. current oai_repo.id)
     */
    public function setContextRepoId(string $repoId): void;

    /**
     * Get the current repository context
     */
    public function getContextRepoId(): ?string;
}
