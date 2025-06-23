<?php

namespace RKW\OaiConnector\Repository;


use RKW\OaiConnector\Model\OaiSetDescription;

class OaiSetDescriptionRepository extends AbstractRepository
{
    protected ?string $modelClass = OaiSetDescription::class;
    protected ?string $tableName = 'oai_set_description';

    protected function getRepoColumnName(): string
    {
        return 'repo';
    }



}
