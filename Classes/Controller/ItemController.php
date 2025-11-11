<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Factory\PaginationFactory;
use RKW\OaiConnector\Repository\OaiItemMetaRepository;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Redirect;


/**
 * ItemController
 *
 * Controller class for handling operations related to items.
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


    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->oaiItemMetaRepository = $this->getOaiItemMetaRepository();
        $this->repoRepository = $this->getRepoRepository();
    }


    /**
     * Index action
     */
    public function index(): void
    {
        //$this->render();
    }


    /**
     * Retrieves and renders a list of repositories and their respective items with pagination.
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

        $records = $this->oaiItemMetaRepository
            ->withSort('datestamp', 'DESC')
            ->withPagination($pagination)
            ->withModels()
            ->findBy($criteria);

        $this->render('list', [
            'repoList' => $repoList,
            'activeRepo' => $activeRepoId,
            'itemList' => $records,
            'pagination' => $pagination
        ]);

    }


    /**
     * Displays a specific record based on provided identifier and repository ID.
     *
     * Retrieves the record using the identifier and repository ID sent via the GET request. If the parameters are missing
     * or the record is not found in the repository, redirects to the index page with an appropriate flash message.
     * Otherwise, renders a view showing the details of the record.
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

        // @toDo: Ggf Warnung in den VIEW verlegen?
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
