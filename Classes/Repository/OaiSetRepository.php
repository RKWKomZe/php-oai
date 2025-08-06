<?php

namespace RKW\OaiConnector\Repository;


use RKW\OaiConnector\Model\OaiSet;

/**
 * Repository class for managing OaiSet entities.
 * Provides access to the 'oai_set' table and related operations.
 */
class OaiSetRepository extends AbstractRepository
{
    /**
     * modelClass
     * Fully qualified model class name, defaults to OaiSet::class
     *
     * @var string|null
     */
    protected ?string $modelClass = OaiSet::class;

    /**
     * tableName
     * Database table name, defaults to 'oai_set'
     *
     * @var string|null
     */
    protected ?string $tableName = 'oai_set';

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
