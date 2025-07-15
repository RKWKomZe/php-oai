<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Factory\PaginationFactory;
use RKW\OaiConnector\Repository\OaiItemMetaRepository;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Redirect;

/**
 *
 * @toDo: Hier prüfen, ob man nicht auf "Oai_Backend.php" zurückgreift zum auslesen von Informationen!
 *
 */
class ItemController extends AbstractController
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


        //$this->render();
    }


    /**
     * @throws \ReflectionException
     */
    public function list(): void
    {
        $repoList = $this->repoRepository->withModels()->findAll();

        $activeRepoId = $_GET['repo']
            ?? ($array[0]['id'] ?? null)
            ?? $this->settings['oai']['defaultRepoId'];

        $criteria = ['repo' => $activeRepoId];

        $pagination = PaginationFactory::fromRequestValues();

        $pagination->setTotalItems(count($this->oaiItemMetaRepository->findBy($criteria)));
        $records = $this->oaiItemMetaRepository->withPagination($pagination)->withModels()->findBy($criteria);

        $this->render('list', [
            'repoList' => $repoList,
            'activeRepo' => $activeRepoId,
            'itemList' => $records,
            'pagination' => $pagination
            //'limit' => $limit,
            //'page' => $page,
            //'totalPages' => $totalPages,
            //'allowedLimits' => $allowedLimits,
        ]);

    }


/*
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

    public function show(): void
    {
        $identifier = $_GET['id'] ?? null;
        $repoId = $_GET['repo'] ?? null;

        if (!$identifier || !$repoId) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Index');
        }

        $item = $this->oaiItemMetaRepository
            ->withModels()
            ->findOneBy([
                'repo' => $repoId,
                'identifier' => $identifier,
                'history' => 0
            ]);

        // @toDo: Ggf warnung in den VIEW verlegen?
        if (!$item) {
            FlashMessage::add('Record not found.', FlashMessage::TYPE_WARNING);
            Redirect::to('list', 'Index');
        }

        // render view with model object
        $this->render('show', [
            'item' => $item,
            'repo' => $repoId,
        ]);
    }



}
