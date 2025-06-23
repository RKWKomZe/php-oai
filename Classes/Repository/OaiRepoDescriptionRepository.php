<?php

namespace RKW\OaiConnector\Repository;

use RKW\OaiConnector\Model\OaiRepoDescription;

class OaiRepoDescriptionRepository extends AbstractRepository
{
    protected ?string $modelClass = OaiRepoDescription::class;
    protected ?string $tableName = 'oai_repo_description';

    protected function getRepoColumnName(): string
    {
        return 'repo'; // da hier die repo-sicht selbst gefiltert wird
    }

}

