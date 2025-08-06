<?php

namespace RKW\OaiConnector\Repository;

use RKW\OaiConnector\Model\OaiRepoDescription;

/**
 * OaiRepoDescriptionRepository
 */
class OaiRepoDescriptionRepository extends AbstractRepository
{
    /**
     * modelClass
     * Fully qualified model class name, defaults to OaiRepoDescription::class
     *
     * @var string|null
     */
    protected ?string $modelClass = OaiRepoDescription::class;

    /**
     * tableName
     * Database table name, defaults to 'oai_repo_description'
     *
     * @var string|null
     */
    protected ?string $tableName = 'oai_repo_description';

    /**
     * Gets the column name used for repository filtering
     *
     * @return string
     */
    protected function getRepoColumnName(): string
    {
        return 'repo';
    }

}

