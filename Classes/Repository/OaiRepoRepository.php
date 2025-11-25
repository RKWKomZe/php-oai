<?php

namespace RKW\OaiConnector\Repository;

use RKW\OaiConnector\Model\OaiRepo;

/**
 * OaiRepoRepository
 *
 * Repository class for managing data operations related to the `oai_repo` table.
 *
 * This class extends AbstractRepository and includes methods for querying,
 * adding, updating, and retrieving records from the database specific to the
 * `oai_repo` table.
 */
class OaiRepoRepository extends AbstractRepository
{

    /**
     * modelClass
     * Fully qualified model class name, defaults to OaiRepo::class
     *
     * @var string|null
     */
    protected ?string $modelClass = OaiRepo::class;


    /**
     * tableName
     * Database table name, defaults to 'oai_repo'
     *
     * @var string|null
     */
    protected ?string $tableName = 'oai_repo';


    /**
     * Gets the column name used for repository filtering
     *
     * @return string
     */
    protected function getRepoColumnName(): string
    {
        return 'id';
    }

}
