<?php

namespace RKW\OaiConnector\Repository;


use RKW\OaiConnector\Model\OaiMeta;

class OaiMetaRepository extends AbstractRepository
{
    protected ?string $modelClass = OaiMeta::class;
    protected ?string $tableName = 'oai_meta';

    protected function getRepoColumnName(): string
    {
        return 'repo';
    }



}
