<?php
/**
 * Bootstrap file to manually load the OAI-PMH server library classes.
 *
 * This is necessary because the cbisiere/oai-pmh project does not use namespaces or Composer autoloading.
 * All relevant classes are located in /lib and are loaded here explicitly.
 */

require_once __DIR__ . '/lib/oai-pmh/php/oai-pmh/Oai_Backend.php';
require_once __DIR__ . '/lib/oai-pmh/php/oai-pmh/Oai_Connection.php';
require_once __DIR__ . '/lib/oai-pmh/php/oai-pmh/Oai_Utils.php';
require_once __DIR__ . '/lib/oai-pmh/php/oai-updater/Oai_Backend_Update.php';
require_once __DIR__ . '/lib/oai-pmh/php/oai-updater/Oai_Updater.php';
// Add more if needed