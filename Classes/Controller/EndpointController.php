<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Service\OaiService;

class EndpointController extends AbstractController
{
    public function handle(): void
    {

        $oaiService = new OaiService();
        $oaiService->handleRequest();

        /*
        $xml = $oaiService->handleRequest();

        header('Content-Type: text/xml');
        echo $xml;
        */
    }
}
