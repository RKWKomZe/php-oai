<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Factory\PaginationFactory;
use RKW\OaiConnector\Mapper\GenericModelMapper;
use RKW\OaiConnector\Model\OaiMeta;
use RKW\OaiConnector\Repository\OaiRepoDescriptionRepository;
use RKW\OaiConnector\Repository\OaiMetaRepository;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Redirect;

/**
 * MetaController
 *
 * The MetaController handles CRUD operations for OaiMeta entities,
 * allowing for listing, viewing, creating, editing, updating, and deleting records.
 *
 * Dependency Injection for repositories is handled through protected methods.
 */
class MetaController extends AbstractController
{
    private ?OaiMetaRepository $oaiMetaRepository = null;

    private ?OaiRepoRepository $oaiRepoRepository = null;

    protected function getOaiMetaRepository(): OaiMetaRepository
    {
        return $this->oaiMetaRepository ??= new OaiMetaRepository();
    }

    protected function getOaiRepoRepository(): OaiRepoRepository
    {
        return $this->oaiRepoRepository ??= new OaiRepoRepository();
    }

    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->oaiMetaRepository = $this->getOaiMetaRepository();
        $this->oaiRepoRepository = $this->getOaiRepoRepository();
    }


    /**
     * Lists all metadata models and renders the 'list' view.
     */
    public function list(): void
    {
        $metaList = $this->oaiMetaRepository->withModels()->findAll();

        $this->render('list', [
            'metaList' => $metaList,
        ]);
    }


    /**
     * Displays a detailed view of a metadata record based on the provided parameters.
     *
     * The method retrieves metadata and repository information using the
     * provided 'prefix' and 'repo' parameters from the query string. If
     * required parameters are missing or the record cannot be found,
     * appropriate flash messages are added, and the user is redirected
     * to the metadata list view.
     *
     * The method also retrieves the repository list and renders the
     * 'show' view with related data.
     */
    public function show(): void
    {
        $metaPrefix = $_GET['prefix'] ?? null;
        $repoName = $_GET['repo'] ?? null;

        if (!$metaPrefix || !$repoName) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Meta');
            return;
        }

        $oaiMeta = $this->oaiMetaRepository
            ->withModels()
            ->findOneBy([
                'metadataPrefix' => $metaPrefix,
                'repo' => $repoName
            ]);

        // @toDo: Ggf warnung in den VIEW verlegen?
        if (!$oaiMeta) {
            FlashMessage::add('Record not found.', FlashMessage::TYPE_WARNING);
            Redirect::to('list', 'Meta');
            return;
        }

        $repoList = $this->oaiRepoRepository->withModels()->findAll();

        // render view with model object
        $this->render('show', [
            'oaiMeta' => $oaiMeta,
            'metadataPrefix' => $metaPrefix,
            'repoList' => $repoList
        ]);
    }


    /**
     * Displays the form for creating a new entity.
     */
    public function new(): void
    {
        $repoList = $this->oaiRepoRepository->withModels()->findAll();
        $this->render('new', ['repoList' => $repoList]);
    }


    /**
     * Creates a new OAI Meta entry.
     * Maps the data from the request, applies necessary updates, and attempts to save it to the repository.
     * Handles success and failure states with appropriate messaging and redirection.
     */
    public function create(): void
    {
        $oaiMeta = GenericModelMapper::map($_POST, OaiMeta::class);

        $oaiMeta->setUpdated(date('Y-m-d H:i:s'));

        $success = $this->oaiMetaRepository->insert($oaiMeta);

        if ($success) {
            FlashMessage::add('Meta created successfully.',  FlashMessage::TYPE_SUCCESS);
            Redirect::to('show', 'Meta', ['prefix'  => $oaiMeta->getMetadataPrefix(), 'repo' => $oaiMeta->getRepo()]);
        } else {
            FlashMessage::add('Meta could not be saved.',   FlashMessage::TYPE_DANGER);
            Redirect::to('new', 'Meta');
        }
    }


    /**
     * Handles the edit functionality for updating metadata records.
     *
     * Extracts the 'prefix' and 'repo' parameters from the GET request,
     * validates their presence, and fetches the corresponding metadata
     * record and repository list for rendering the edit view.
     *
     * Redirects to the list page with an error message if required parameters
     * are missing.
     */
    public function edit(): void
    {
        $metaPrefix = $_GET['prefix'] ?? null;
        $repoName = $_GET['repo'] ?? null;

        if (!$metaPrefix || !$repoName) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Meta');
            return;
        }

        $oaiMeta = $this->oaiMetaRepository->withModels()->findOneBy([
            'metadataPrefix' => $metaPrefix,
            'repo' => $repoName
        ]);

        $repoList = $this->oaiRepoRepository->withModels()->findAll();

        $this->render('edit', [
            'oaiMeta' => $oaiMeta,
            'repoList' => $repoList
        ]);
    }


    /**
     * Updates an OAI Metadata record by mapping data from the POST request,
     * setting the updated timestamp, and updating the repository.
     *
     * Adds a success flash message upon successful update and redirects to the
     * specified meta display.
     *
     * @return void
     */
    public function update(): void
    {
        //$repo = new \RKW\OaiConnector\Model\OaiRepo($_POST);

        $oaiMeta = GenericModelMapper::map($_POST, OaiMeta::class);

        $oaiMeta->setUpdated(date('Y-m-d H:i:s'));

        $this->oaiMetaRepository->update($oaiMeta, ['metadataPrefix', 'repo']);

        FlashMessage::add('Datensatz erfolgreich bearbeitet.', FlashMessage::TYPE_SUCCESS);

        Redirect::to('show', 'Meta', ['prefix' => $oaiMeta->getMetadataPrefix(), 'repo' => $oaiMeta->getRepo()]);
    }


    /**
     * Deletes a specific metadata record based on provided parameters.
     *
     * Retrieves the metadata record identified by 'prefix' and 'repo' from the request
     * and deletes it from the repository. Handles validation of parameters and sets
     * appropriate flash messages for success or failure. Redirects the user after operation.
     *
     * @return void
     */
    public function delete(): void
    {
        $metaPrefix = $_GET['prefix'] ?? null;
        $repoName = $_GET['repo'] ?? null;

        if (!$metaPrefix || !$repoName) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Meta');
            return;
        }

        $oaiMeta = $this->oaiMetaRepository->withModels()->findOneBy([
            'metadataPrefix' => $metaPrefix,
            'repo' => $repoName
        ]);

        $this->oaiMetaRepository->delete($oaiMeta, ['metadataPrefix', 'repo']);

        FlashMessage::add('Datensatz gelöscht.', FlashMessage::TYPE_SUCCESS);

        Redirect::to('list', 'Meta');
    }


}
