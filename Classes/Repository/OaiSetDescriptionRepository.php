<?php

namespace RKW\OaiConnector\Repository;

use RKW\OaiConnector\Model\OaiSetDescription;

/**
 * OaiSetDescriptionRepository
 */
class OaiSetDescriptionRepository extends AbstractRepository
{
    /**
     * modelClass
     * Fully qualified model class name, defaults to OaiSetDescription::class
     *
     * @var string|null
     */
    protected ?string $modelClass = OaiSetDescription::class;


    /**
     * tableName
     * Database table name, defaults to 'oai_set_description'
     *
     * @var string|null
     */
    protected ?string $tableName = 'oai_set_description';


}
