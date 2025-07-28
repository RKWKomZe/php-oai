<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Factory\PaginationFactory;
use RKW\OaiConnector\Repository\OaiItemMetaRepository;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\DbConnection;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Pagination;
use RKW\OaiConnector\Utility\Redirect;

class IndexController extends AbstractController
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


    public function index(): void
    {


        // @toDo: Allgemeine Gedanken / Ideen: Offenbar kann man mehrere OAI-Repos betreiben.
        // Mit welchem Nutzen? Repo je Quelle, oder je Harvester?
        // Ziemlich sicher brauchen wir ja ohnehin einen Controller fürs harvesten. Ggf hier mit URL Parameterübergabe arbeiten
        // So das man Repo name + speziellen Key / Token übergeben muss, damit Repo X abgefragt werden kann


        $pdo = DbConnection::get();

        $stmt = $pdo->query('
        SELECT *
        FROM oai_update_log
        ORDER BY id DESC
        LIMIT 30
    ');

        $logs = $stmt->fetchAll();

        $this->render('index', [
            'logs' => $logs,
        ]);
    }

    /*

    public function list(): void
    {
        $repoList = $this->repoRepository->findAll();

        $activeRepoId = $_GET['repo']
            ?? ($array[0]['id'] ?? null)
            ?? $this->settings['oai']['defaultRepoId'];

        $criteria = ['repo' => $activeRepoId];

        $pagination = PaginationFactory::fromRequestValues();

        $pagination->setTotalItems(count($this->oaiItemMetaRepository->findBy($criteria)));
        $records = $this->oaiItemMetaRepository->withPagination($pagination)->findBy($criteria);



        // @toDo: Das Pagination-Objekt direkt an View übergeben, anstatt einzelne Parameter

        // @toDo: Zur Verfügung stehenden Limits via Paginator-Klasse vordefinieren? Ggf auch über Config? (25, 50, 100, 200, 500)

        $this->render('list', [
            'repos' => $repoList,
            'activeRepo' => $activeRepoId,
            'items' => $records,
            'pagination' => $pagination
            //'limit' => $limit,
            //'page' => $page,
            //'totalPages' => $totalPages,
            //'allowedLimits' => $allowedLimits,
        ]);

    }



    public function show(): void
    {
        $identifier = $_GET['id'] ?? null;
        $repo = $_GET['repo'] ?? null;

        if (!$identifier || !$repo) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Index');
            return;
        }

        $conn = ConfigLoader::getDatabaseConnection();
        $stmt = $conn->prepare('
        SELECT identifier, datestamp, metadataPrefix, deleted, metadata, created, updated 
        FROM oai_item_meta 
        WHERE repo = ? AND identifier = ? AND history = 0
        LIMIT 1
    ');
        $stmt->bind_param('ss', $repo, $identifier);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) {
            FlashMessage::add('Record not found.', FlashMessage::TYPE_WARNING);
            Redirect::to('list', 'Index');
            return;
        }

        $this->render('show', [
            'record' => $result,
            'repo' => $repo,
        ]);
    }
*/
}
