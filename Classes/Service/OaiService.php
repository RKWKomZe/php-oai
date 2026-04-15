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
     * HINT: The cbisiere/oai-pmh library is NOT compatible with "sql_mode=ONLY_FULL_GROUP_BY"
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

        // Whitelist of permitted OAI-PMH parameters
        $allowedParams = [
            'verb', 'identifier', 'metadataPrefix', 'from', 'until', 'set', 'resumptionToken'
        ];

        // You may use repo internally, but do not pass it to the OAI endpoint.
        $repo = $_GET['repo'] ?? $config['oai']['defaultRepoId'];

        // $_GET temporary filter
        $_GET = array_intersect_key($_GET, array_flip($allowedParams));

        // To ensure that today's records can also be found when the "until" date is set to today's date
        $tz = new \DateTimeZone('Europe/Berlin');
        if (isset($_GET['until']) && $_GET['until'] !== null) {
            $tmp = new \DateTime($_GET['until'] . ' 00:00:00', $tz);
            $tmp->modify('+1 day');
            $_GET['until'] = $tmp->format('Y-m-d'); // bump one day to emulate end-of-day
        }

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
