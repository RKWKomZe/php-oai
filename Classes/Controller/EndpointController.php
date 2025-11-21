<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Service\OaiService;

/**
 * EndpointController
 *
 * handles index queries
 */
class EndpointController extends AbstractController
{

    /**
     * handle
     *
     * do some queries
     */
    public function handle(): void
    {
        $oaiService = new OaiService();
        $oaiService->handleRequest();

    }

}
