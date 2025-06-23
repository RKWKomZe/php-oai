<?php

namespace RKW\OaiConnector\Repository;


use RKW\OaiConnector\Model\OaiSet;

class OaiSetRepository extends AbstractRepository
{
    protected ?string $modelClass = OaiSet::class;
    protected ?string $tableName = 'oai_set';

    protected function getRepoColumnName(): string
    {
        return 'repo';
    }



}
