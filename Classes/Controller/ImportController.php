<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Integration\Shopware\ShopwareOaiFetcher;
use RKW\OaiConnector\Integration\Shopware\ShopwareOaiUpdater;
use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Redirect;
use Symfony\Component\VarDumper\VarDumper;

class ImportController extends AbstractController
{
    public function run(): void
    {
        $config = ConfigLoader::load();

        $shopwareConfig = $config['api']['shopware'];
        $dbConfig = $config['database'];
        $repo = $config['oai']['defaultRepoId'];
        $saveHistory = $config['oai']['save_history'] ?? true;

        // 1. Produkte via Shopware-API holen
        $fetcher = new ShopwareOaiFetcher();
        $records = $fetcher->fetchAndTransform();

        // 2. An OAI Updater übergeben
        $updater = new ShopwareOaiUpdater(
            $dbConfig['host'],
            $dbConfig['user'],
            $dbConfig['password'],
            $dbConfig['name'],
            $repo,
            $saveHistory,
            $records
        );

    //    $updater->setSpecArray(['products']); // falls erwartet
    //    $updater->setMetadataPrefixArray(['oai_dc']); // falls erwartet
    //    $updater->setRecords($records);
        $updater->run();

        FlashMessage::add(count($records) . ' Produkte erfolgreich importiert.', FlashMessage::TYPE_SUCCESS);

        // 3. View
        /*
        $this->render('import_result', [
            'imported' => count($records),
            'success' => true,
        ]);
        */

        //$this->render('index');

        Redirect::to('index', 'Index');
    }

}
