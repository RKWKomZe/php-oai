<?php

namespace RKW\OaiConnector\Service;

use RKW\OaiConnector\Utility\ConfigLoader;
use Symfony\Component\VarDumper\VarDumper;


require_once __DIR__ . '/../../lib/oai-pmh/php/oai-pmh/Oai.php';
require_once __DIR__ . '/../../lib/oai-pmh/php/oai-pmh/Oai_Backend.php';
require_once __DIR__ . '/../../lib/oai-pmh/php/oai-pmh/Oai_Connection.php';
require_once __DIR__ . '/../../lib/oai-pmh/php/oai-pmh/Oai_Const.php';
require_once __DIR__ . '/../../lib/oai-pmh/php/oai-pmh/Oai_Date.php';
require_once __DIR__ . '/../../lib/oai-pmh/php/oai-pmh/Oai_Err.php';
require_once __DIR__ . '/../../lib/oai-pmh/php/oai-pmh/Oai_Exception.php';
require_once __DIR__ . '/../../lib/oai-pmh/php/oai-pmh/Oai_Logger.php';
require_once __DIR__ . '/../../lib/oai-pmh/php/oai-pmh/Oai_Utils.php';
require_once __DIR__ . '/../../lib/oai-pmh/php/oai-pmh/Xml_Utils.php';

class OaiService
{

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
