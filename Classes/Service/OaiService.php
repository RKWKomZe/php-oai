<?php

namespace RKW\OaiConnector\Service;

use RKW\OaiConnector\Utility\ConfigLoader;


/**
 * OaiService
 *
 * Handles the OAI-PMH request, configuring necessary parameters, database credentials,
 * and executing the request through the OAI system.
 *
 * - Loads configuration settings.
 * - Filters and validates allowed OAI-PMH parameters.
 * - Determines repository based on input or default settings.
 * - Executes the OAI request with the specified parameters.
 */
class OaiService
{

    /**
     * Handles the incoming HTTP request for OAI-PMH processing.
     *
     * - Loads necessary configuration settings, including database and environment specifics.
     * - Defines debug mode based on the application's environment (development or production).
     * - Filters and restricts the allowed OAI-PMH parameters from the $_GET request to ensure security.
     * - Determines the repository identifier to use, either from the request or a default value.
     * - Executes the OAI request using the provided configuration and parameters.
     */
    public function handleRequest(): void
    {
        $config = ConfigLoader::load();
        $dbConfig = $config['database'];

        $stylesheet = null;
        $limit = null;

        defined('OAI_DEBUG') || define('OAI_DEBUG', $config['environment'] === 'development' ? 1 : 0);

        // Whitelist erlaubter OAI-PMH-Parameter
        $allowedParams = [
            'verb', 'identifier', 'metadataPrefix', 'from', 'until', 'set', 'resumptionToken'
        ];

        // repo darfst du intern nutzen, aber nicht an den OAI-Endpunkt geben
        $repo = $_GET['repo'] ?? $config['oai']['defaultRepoId'];

        // $_GET temporär filtern
        $_GET = array_intersect_key($_GET, array_flip($allowedParams));


        \Oai::execRequest(
            $dbConfig['host'],
            $dbConfig['user'],
            $dbConfig['password'],
            $dbConfig['name'],
            $repo,
            $stylesheet,
            $limit
        );
    }
}
