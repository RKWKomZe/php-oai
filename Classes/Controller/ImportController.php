<?php

namespace RKW\OaiConnector\Controller;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use RKW\OaiConnector\Factory\LoggerFactory;
use RKW\OaiConnector\Integration\Shopware\ShopwareOaiFetcher;
use RKW\OaiConnector\Integration\Shopware\ShopwareOaiUpdater;
use RKW\OaiConnector\Repository\OaiItemMetaRepository;
use RKW\OaiConnector\Repository\OaiMetaRepository;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\DbConnection;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Redirect;
use RKW\OaiConnector\Utility\ShopwareData;
use Symfony\Component\VarDumper\VarDumper;

/**
 * ImportController
 *
 * Controller responsible for handling import processes.
 */
class ImportController extends AbstractController
{

    /**
     * @var ?LoggerInterface|LoggerFactory|null
     */
    private LoggerInterface|null|LoggerFactory $logger = null;


    /**
     * @var OaiItemMetaRepository|null
     */
    private ?OaiItemMetaRepository $oaiItemMetaRepository = null;


    /**
     * @var OaiRepoRepository|null
     */
    private ?OaiRepoRepository $repoRepository = null;


    /**
     * @var OaiMetaRepository|null
     */
    private ?OaiMetaRepository $oaiMetaRepository = null;


    /**
     * Constructor method for initializing the base class and setting up the repositories.
     */
    public function __construct()
    {
        parent::__construct();
        $this->oaiItemMetaRepository = $this->getOaiItemMetaRepository();
        $this->repoRepository = $this->getRepoRepository();
        $this->oaiMetaRepository = $this->getOaiMetaRepository();

        $this->logger = LoggerFactory::get();
    }


    /**
     * @return OaiItemMetaRepository
     */
    protected function getOaiItemMetaRepository(): OaiItemMetaRepository
    {
        return $this->oaiItemMetaRepository ??= new OaiItemMetaRepository($this->settings['oai']['defaultRepoId']);
    }


    /**
     * @return OaiRepoRepository
     */
    protected function getRepoRepository(): OaiRepoRepository
    {
        return $this->repoRepository ??= new OaiRepoRepository($this->settings['oai']['defaultRepoId']);
    }


    /**
     * @return OaiMetaRepository
     */
    protected function getOaiMetaRepository(): OaiMetaRepository
    {
        return $this->oaiMetaRepository ??= new OaiMetaRepository($this->settings['oai']['defaultRepoId']);
    }


    /**
     * Renders a list of products fetched from Shopware within the given date range and pagination parameters.
     *
     * Retrieves data from Shopware OAI API based on provided filters (date range, limit, and page).
     * Identifies existing OAI item identifiers within the active repository.
     * Displays the list of products alongside repository information and pagination details in the view.
     *
     * The active repository is resolved from the request parameters, repository list, or default settings.
     *
     * If no repositories are found, an error message is displayed, notifying the user to create a repository.
     *
     * @throws \Exception|GuzzleException
     */
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

        $metadataPrefixList = $this->oaiMetaRepository->withModels()->findAll();

        $metadataPrefixListSorted = [];
        foreach ($metadataPrefixList as $metadataPrefix) {
            $repoId = $metadataPrefix->getRepo();
            $metadataPrefixListSorted[$repoId] ??= [];
            $metadataPrefixListSorted[$repoId][$metadataPrefix->getMetadataPrefix()] = $metadataPrefix->getMetadataPrefix();
        }

        if (!count($repoList)) {
            FlashMessage::add('No repositories found. Import not possible. Please create an OAI-Repo first.', FlashMessage::TYPE_DANGER);
        }

        // @toDo: Was für ein Überbleibsel ist die Zeile "?? ($array[0]['id'] ?? null)"?
        $activeRepoId = $_GET['repo']
            ?? ($array[0]['id'] ?? null)
            ?? $this->settings['oai']['defaultRepoId'];

        $activeMetadataPrefix = $_GET['metadataPrefix']
            ?? array_values($metadataPrefixListSorted[$activeRepoId])[0] ?? null;

        $shopwareIds = array_column($dataRequest['data'], 'id'); // z. B. ['abc123', 'def456', ...]
        $prefixedIds = array_map(fn($id) => "oai:$activeRepoId:" . $id, $shopwareIds);



        // filter
        /* @todo: Is this filter necessary? Or can it be removed? *&
        /*
        $unimportedProducts = array_filter($dataRequest['data'], function ($product) use ($existingIdentifiers, $activeRepoId) {
            return !in_array("oai:$activeRepoId:" . $product['id'], $existingIdentifiers, true);
        });
        */

        // for usability: Compare selected Shopware records with already imported database items
        //    $existingIdentifiers = $this->oaiItemMetaRepository->findByIdList($activeRepoId, $prefixedIds);
        $identifierList = ShopwareData::buildOaiIdentifiersFromItems($dataRequest['data'], $activeRepoId);
        $metaRows = $this->oaiItemMetaRepository->findBy([$identifierList]);
        $existingIdentifierPool = ShopwareData::buildSourceIdPoolFromMetaRows($metaRows);

        $this->render('list', [
            //'unimportedProducts' => $unimportedProducts,
            'productList' => $dataRequest['data'],
        //    'existingIdentifiers' => $existingIdentifiers,
            'existingIdentifierPool' => $existingIdentifierPool,
            'repoList' => $repoList,
            'metadataPrefixListSorted' => $metadataPrefixListSorted,
            'activeRepoId' => $activeRepoId,
            'activeMetadataPrefix' => $activeMetadataPrefix,
            'fromDate' => $fromDate->format('Y-m-d'),
            'untilDate' => $untilDate->format('Y-m-d'),
            'limit' => $limit,
            'page' => $page,
            'totalCount' => $dataRequest['total']
        ]);
    }


    /**
     * Imports a single product via Shopware API and updates the database.
     *
     * This method handles the process of importing a single product, starting from fetching the product
     * using the Shopware API, passing the records to the updater, and monitoring for any errors that could occur
     * during the update process. It supports both AJAX and non-AJAX requests.
     *
     * Key steps:
     * - Validates the presence of required parameters (identifier and repository ID).
     * - Loads configuration settings for database connection and additional options such as history saving.
     * - Initializes the Shopware API fetcher to retrieve product data based on the identifier.
     * - Creates an updater instance to perform the update operation.
     * - Monitors the update process using the update log table to detect errors.
     * - Responds appropriately based on the request type (AJAX or non-AJAX) and the presence of errors.
     *
     * Process assumptions:
     * - The identifier and repository ID are required for proper product importation.
     * - Private methods in the updater cannot be overridden, so error handling is performed externally.
     * - History saving is enabled by default unless disabled in the configuration.
     *
     * Error handling:
     * - If an error is detected post-update, an appropriate response (JSON or flash message) is returned.
     * - The process ensures that appropriate HTTP headers and messages are sent based on the request method and result.
     *
     * Throws:
     * - Redirects or exits in case of missing parameters or critical errors to prevent further execution.
     *
     * Response:
     * - For AJAX requests: Returns a JSON response indicating success or failure.
     * - For non-AJAX requests: Uses FlashMessage for user feedback and redirects to the import list.
     */
    public function importOne(): void
    {

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

        $identifier = $_GET['id'] ?? null;
        $repoId = $_GET['repo'] ?? null;
        $metadataPrefix = $_GET['metadataPrefix'] ?? null;

        if (!$identifier || !$repoId) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'import');
        }

        $config = ConfigLoader::load();

        $dbConfig = $config['database'];
        $saveHistory = $config['oai']['save_history'] ?? true;

        // 1. Fetch products via Shopware-API
        $fetcher = new ShopwareOaiFetcher();
        $records = $fetcher->fetchSingleById($identifier);


        // @toDo: Add validation for alle Felder mit (O) oder (O/F) aus Steffen dokument


        // 2. Passed to OAI Updater
        $updater = new ShopwareOaiUpdater(
            $dbConfig['host'],
            $dbConfig['user'],
            $dbConfig['password'],
            $dbConfig['name'],
            $repoId,
            $saveHistory,
            [$records]
        );

        // Problem: We can't override the function "run" because of used methods with "private"-declaration
        // But we want to return error message if something went wrong
        // Workaround: Check the update-log-table before and after
        $pdo = DbConnection::get();
        $lastLogId = (int) $pdo->query('SELECT MAX(id) FROM oai_update_log')->fetchColumn();

        $this->logger->info('Start import', ['Identifier' => $identifier]);

        // do the update run
        try {
            $updater->run(['all'], [$metadataPrefix]);


        } catch (\Throwable $e) {

            // @toDo: Fehlermeldung wird von Library selbst schon abefangen, falls etwa SQL-Daten falsch sind . Dieser ...
            // ... catch "catcht" also zumindest teilweise nicht
            $this->logger->critical('Unexpected error while writing records to database:', [
                'error' => $e->getMessage(),
            ]);
        }

        // check for errors
        $stmt = $pdo->prepare('
            SELECT * FROM oai_update_log
            WHERE id > :lastId
            ORDER BY id DESC
            LIMIT 1
        ');
        $stmt->execute(['lastId' => $lastLogId]);
        $logEntry = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($logEntry && $logEntry['error']) {
            $errorMessage = $logEntry['errmsg'];

            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $errorMessage]);
            exit;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        /* @todo: Maybe provide a translation file to collect messages there */
        FlashMessage::add("Product successfully imported.", FlashMessage::TYPE_SUCCESS);
        Redirect::to('list', 'import');

    }


    /**
     * Executes the full import process for products using the Shopware API and updates the database.
     *
     * This method orchestrates the process of importing products in bulk, transforming fetched data,
     * and passing it to the updater for database synchronization. Additionally, it provides feedback
     * to the user upon completion.
     *
     * Key steps:
     * - Loads configuration settings for database and options (e.g., default repository ID and history saving).
     * - Fetches and transforms product data from Shopware API using the fetcher instance.
     * - Initializes the updater with the fetched data and executes the update routine.
     * - Updates the user with the number of successfully imported products through flash messaging.
     * - Redirects the user to the fullImport page or a predefined Tool view upon completion.
     *
     * Process assumptions:
     * - Default repository ID is defined in the configuration and used for data operations.
     * - History saving for imported data is enabled by default unless explicitly disabled in the configuration.
     * - The updater instance processes and synchronizes all records provided, including transformed data.
     *
     * User feedback:
     * - A flash message is displayed indicating the count of successfully imported products.
     * - Performs a redirect to the tool's fullImport page upon successful completion.
     *
     * Response:
     * - This method redirects to another view, making it unsuitable for direct AJAX handling.
     * @throws GuzzleException
     * @throws \Exception
     */
    public function run(): void
    {
        $config = ConfigLoader::load();

        $dbConfig = $config['database'];
        $repo = $config['oai']['defaultRepoId'];
        $saveHistory = $config['oai']['save_history'] ?? true;

        // 1. Get products via Shopware API
        $fetcher = new ShopwareOaiFetcher();
        $records = $fetcher->fetchAndTransform();

        // 2. Passed to OAI Updater
        $updater = new ShopwareOaiUpdater(
            $dbConfig['host'],
            $dbConfig['user'],
            $dbConfig['password'],
            $dbConfig['name'],
            $repo,
            $saveHistory,
            $records
        );

        try {
            $updater->run();
        } catch (\Throwable $e) {
            // final safeguard (unexpected runtime errors)
            $this->logger->critical('Unexpected error while writing records to database:', [
                'error' => $e->getMessage(),
            ]);
        }

        FlashMessage::add(count($records) . ' Products successfully imported.', FlashMessage::TYPE_SUCCESS);
        Redirect::to('fullImport', 'Tool');

    }

}
