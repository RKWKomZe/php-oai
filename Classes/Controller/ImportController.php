<?php

namespace RKW\OaiConnector\Controller;

use GuzzleHttp\Exception\GuzzleException;
use RKW\OaiConnector\Factory\PaginationFactory;
use RKW\OaiConnector\Integration\Shopware\ShopwareOaiFetcher;
use RKW\OaiConnector\Integration\Shopware\ShopwareOaiUpdater;
use RKW\OaiConnector\Repository\OaiItemMetaRepository;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Redirect;
use Symfony\Component\VarDumper\VarDumper;

class ImportController extends AbstractController
{
    private ?OaiItemMetaRepository $oaiItemMetaRepository = null;

    private ?OaiRepoRepository $repoRepository = null;

    protected function getOaiItemMetaRepository(): OaiItemMetaRepository
    {
        return $this->oaiItemMetaRepository ??= new OaiItemMetaRepository($this->settings['oai']['defaultRepoId']);
    }

    protected function getRepoRepository(): OaiRepoRepository
    {
        return $this->repoRepository ??= new OaiRepoRepository($this->settings['oai']['defaultRepoId']);
    }


    public function __construct()
    {
        parent::__construct();
        $this->oaiItemMetaRepository = $this->getOaiItemMetaRepository();
        $this->repoRepository = $this->getRepoRepository();
    }


    public function list(): void
    {
        $fromParam = $_GET['from'] ?? null;
        $untilParam = $_GET['until'] ?? null;
        $limit = $_GET['limit'] ?? '10';
        $page = $_GET['page'] ?? '1';

        $fromDate = $fromParam ? new \DateTime($fromParam) : (new \DateTime())->modify('-1 month');
        $untilDate = $untilParam ? new \DateTime($untilParam) : new \DateTime();
        $untilDate->setTime(23, 59, 59);

        // get shopware data
        $fetcher = new ShopwareOaiFetcher();
        $dataRequest = $fetcher->fetchAndTransform(
            [
                'fromDate' =>$fromDate,
                'untilDate' => $untilDate,
                'limit' => $limit,
                'page' => $page
            ],
            true);

        $repoList = $this->repoRepository->withModels()->findAll();

        $activeRepoId = $_GET['repo']
            ?? ($array[0]['id'] ?? null)
            ?? $this->settings['oai']['defaultRepoId'];


        $shopwareIds = array_column($dataRequest['data'], 'id'); // z. B. ['abc123', 'def456', ...]
        $prefixedIds = array_map(fn($id) => "oai:$activeRepoId:" . $id, $shopwareIds);

        $existingIdentifiers = $this->oaiItemMetaRepository->findByIdList($activeRepoId, $prefixedIds);

        // filter
        /*
        $unimportedProducts = array_filter($dataRequest['data'], function ($product) use ($existingIdentifiers, $activeRepoId) {
            return !in_array("oai:$activeRepoId:" . $product['id'], $existingIdentifiers, true);
        });
        */

        $this->render('list', [
            //'unimportedProducts' => $unimportedProducts,
            'productList' => $dataRequest['data'],
            'existingIdentifiers' => $existingIdentifiers,
            'repoList' => $repoList,
            'activeRepoId' => $activeRepoId,
            'fromDate' => $fromDate->format('Y-m-d'),
            'untilDate' => $untilDate->format('Y-m-d'),
            'limit' => $limit,
            'page' => $page,
            'totalCount' => $dataRequest['total']
        ]);
    }


    public function importOne(): void
    {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

        $identifier = $_GET['id'] ?? null;
        $repoId = $_GET['repo'] ?? null;

        if (!$identifier || !$repoId) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'import');
        }

        $config = ConfigLoader::load();

        $dbConfig = $config['database'];
        $saveHistory = $config['oai']['save_history'] ?? true;

        // 1. Produkte via Shopware-API holen
        $fetcher = new ShopwareOaiFetcher();
        $records = $fetcher->fetchSingleById($identifier);

        // 2. An OAI Updater übergeben
        $updater = new ShopwareOaiUpdater(
            $dbConfig['host'],
            $dbConfig['user'],
            $dbConfig['password'],
            $dbConfig['name'],
            $repoId,
            $saveHistory,
            [$records]
        );

        $updater->run();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } else {

            FlashMessage::add("Produkt erfolgreich importiert.", FlashMessage::TYPE_SUCCESS);

            Redirect::to('list', 'import');
        }

        /*
        header('Content-Type: application/json');
echo json_encode(['success' => true]);

        header('Content-Type: application/json', true, 400);
echo json_encode(['success' => false, 'message' => 'Produkt konnte nicht gefunden werden']);


         */
    }


    /**
     * @throws GuzzleException
     */
    public function run(): void
    {
        $config = ConfigLoader::load();

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

        $updater->run();

    //    $updater->setSpecArray(['products']); // falls erwartet
    //    $updater->setMetadataPrefixArray(['oai_dc']); // falls erwartet
    //    $updater->setRecords($records);


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
