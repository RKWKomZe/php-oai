<?php

namespace RKW\OaiConnector\Controller;

use GuzzleHttp\Exception\GuzzleException;
use RKW\OaiConnector\Factory\PaginationFactory;
use RKW\OaiConnector\Integration\Shopware\ShopwareOaiFetcher;
use RKW\OaiConnector\Integration\Shopware\ShopwareOaiUpdater;
use RKW\OaiConnector\Repository\OaiItemMetaRepository;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\DbConnection;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Redirect;

/**
 * ImportController
 *
 * Controller responsible for handling import processes.
 */
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


    /**
     * Constructor method for initializing the base class and setting up the repositories.
     */
    public function __construct()
    {
        parent::__construct();
        $this->oaiItemMetaRepository = $this->getOaiItemMetaRepository();
        $this->repoRepository = $this->getRepoRepository();
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

        if (!count($repoList)) {
            FlashMessage::add('No repositories found. Import not possible. Please create an OAI-Repo first.', FlashMessage::TYPE_DANGER);
        }

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

        // Problem: We can't override the function "run" because of used methods with "private"-declaration
        // But we want to return error message if something went wrong
        // Workaround: Check the update-log-table before and after
        $pdo = DbConnection::get();
        $lastLogId = (int) $pdo->query('SELECT MAX(id) FROM oai_update_log')->fetchColumn();

        // do the update run
        $updater->run();

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
            $errorMessage = $logEntry['errmsg']; // enthält die Exception-Message
            // nun behandeln oder anzeigen

            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $errorMessage]);
            exit;
        }

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

        Redirect::to('fullImport', 'Tool');
    }

}
