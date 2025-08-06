<?php

namespace RKW\OaiConnector\Repository;


use RKW\OaiConnector\Model\OaiMeta;

/**
 * OaiMetaRepository
 */
class OaiMetaRepository extends AbstractRepository
{
    /**
     * modelClass
     * Fully qualified model class name, defaults to OaiMeta::class
     *
     * @var string|null
     */
    protected ?string $modelClass = OaiMeta::class;

    /**
     * tableName
     * Database table name, defaults to 'oai_meta'
     *
     * @var string|null
     */
    protected ?string $tableName = 'oai_meta';


    /**
     * Gets the column name used for repository filtering
     */
    protected function getRepoColumnName(): string
    {
        return 'repo';
    }



}
