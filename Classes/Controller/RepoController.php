<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Factory\PaginationFactory;
use RKW\OaiConnector\Mapper\GenericModelMapper;
use RKW\OaiConnector\Model\OaiRepo;
use RKW\OaiConnector\Model\OaiRepoDescription;
use RKW\OaiConnector\Repository\OaiRepoDescriptionRepository;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Redirect;
use Symfony\Component\VarDumper\VarDumper;

/**
 * RepoController
 *
 * Controller for managing repositories and their descriptions.
 */
class RepoController extends AbstractController
{

    private ?OaiRepoRepository $oaiRepoRepository = null;


    private ?OaiRepoDescriptionRepository $oaiRepoDescriptionRepository = null;


    protected function getOaiRepoRepository(): OaiRepoRepository
    {
        return $this->oaiRepoRepository ??= new OaiRepoRepository();
    }


    protected function getOaiRepoDescriptionRepository(): OaiRepoDescriptionRepository
    {
        return $this->oaiRepoDescriptionRepository ??= new OaiRepoDescriptionRepository();
    }


    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->oaiRepoRepository = $this->getOaiRepoRepository();
        $this->oaiRepoDescriptionRepository = $this->getOaiRepoDescriptionRepository();
    }


    /**
     * Lists repositories with pagination and renders the list view.
     */
    public function list(): void
    {

        $pagination = PaginationFactory::fromRequestValues();
        $repoList = $this->oaiRepoRepository->withPagination($pagination)->withModels()->findAll();

        $this->render('list', [
            'repoList' => $repoList,
        ]);

    }


    /**
     * Displays a specific record based on the provided identifier.
     *
     * Retrieves the record from the repository using the identifier from the request.
     * If the identifier is missing or the record is not found, redirects to the list view with a corresponding message.
     * Fetches related description information for the record and renders the show view with the model objects.
     *
     * @return void
     */
    public function show(): void
    {
        $identifier = $_GET['id'] ?? null;

        if (!$identifier) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Repo');
        }

        $oaiRepo = $this->oaiRepoRepository
            ->withModels()
            ->findOneBy([
                'id' => $identifier
            ]);

        // @toDo: Ggf warnung in den VIEW verlegen?
        if (!$oaiRepo) {
            FlashMessage::add('Record not found.', FlashMessage::TYPE_WARNING);
            Redirect::to('list', 'Repo');
        }

        $oaiRepoDescription = $this->oaiRepoDescriptionRepository->withModels()->findOneBy(['repo' => $oaiRepo->getId()]);

        // render view with model object
        $this->render('show', [
            'oaiRepo' => $oaiRepo,
            'id' => $identifier,
            'oaiRepoDescription' => $oaiRepoDescription
        ]);
    }


    /**
     * Renders the 'new' view.
     * @throws \ReflectionException
     */
    public function new(): void
    {
        $this->render('new', [
            'oaiRepo' => new OaiRepo(),
            'oaiRepoDescription' => new OaiRepoDescription(),
        ]);
    }


    /**
     * Creates a new OAI repository and its description based on posted data.
     *
     * Maps the posted data to `OaiRepo` and `OaiRepoDescription` models, associates the description
     * with the repository, sets the updated timestamp on the repository, and saves the data.
     *
     * Provides feedback to the user regarding the success or failure of the operation
     * through flash messages and redirects accordingly.
     *
     * @throws Exception|\ReflectionException If an error occurs during data mapping, insertion, or redirection.
     */
    public function create(): void
    {

        $oaiRepo = GenericModelMapper::map($_POST, OaiRepo::class);

        $oaiRepoDescription = GenericModelMapper::map($_POST, OaiRepoDescription::class);
        $oaiRepoDescription->setRepo($oaiRepo->getId());

        $oaiRepo->setUpdated(date('Y-m-d H:i:s'));
        $oaiRepoDescription->setUpdated(date('Y-m-d H:i:s'));

        $success = $this->oaiRepoRepository->insert($oaiRepo);

        $this->oaiRepoDescriptionRepository->upsert($oaiRepoDescription);

        if (!$success) {
            FlashMessage::add('Repository could not be saved.',   FlashMessage::TYPE_DANGER);
            Redirect::to('new', 'Repo');
        }

        FlashMessage::add('Repository was successfully created. Next, an “OAI Meta” with linked schema prefix (e.g., “oai_dc”) should be created.',  FlashMessage::TYPE_SUCCESS);
        Redirect::to('show', 'Repo', [
            'id'  => $oaiRepo->getId()
        ]);

    }


    /**
     * Handles the edit operation.
     *
     * This method is responsible for retrieving the necessary data for editing
     * a specific repository record based on the provided identifier. It checks
     * for the existence of the identifier, fetches the associated repository and
     * its description models, and renders the edit view with the retrieved data.
     *
     * If the identifier is missing, a flash message is shown, and the user is redirected to the repository list.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function edit(): void
    {

        $identifier = $_GET['id'] ?? null;
        if (!$identifier) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Repo');
        }

        $oaiRepo = $this->oaiRepoRepository->withModels()->findById($identifier);

        $oaiRepoDescription = $this->oaiRepoDescriptionRepository->withModels()->findOneBy([
            'repo' => $oaiRepo->getId()
        ]);

        $this->render('edit', [
            'oaiRepo' => $oaiRepo,
            'oaiRepoDescription' => $oaiRepoDescription
        ]);

    }


    /**
     * Updates an OaiRepo entity and its associated OaiRepoDescription entity
     * based on the POST data. Handles data mapping, relation updating,
     * persistence, and redirects to the appropriate page with a success message.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function update(): void
    {

        $oaiRepo = GenericModelMapper::map($_POST, OaiRepo::class);
        $oaiRepoDescription = GenericModelMapper::map($_POST, OaiRepoDescription::class);
        $oaiRepoDescription->setRepo($oaiRepo->getId());

        $oaiRepo->setUpdated(date('Y-m-d H:i:s'));
        $oaiRepoDescription->setUpdated(date('Y-m-d H:i:s'));

        $this->oaiRepoRepository->update($oaiRepo);
        $this->oaiRepoDescriptionRepository->upsert($oaiRepoDescription);

        FlashMessage::add('Record successfully edited.', FlashMessage::TYPE_SUCCESS);
        Redirect::to('show', 'Repo', ['id' => $oaiRepo->getId()]);

    }


    /**
     * Deletes an OaiRepo entity and its associated description, if present.
     * Displays a success message and redirects to the list view upon completion.
     */
    public function delete(): void
    {

        $oaiRepo = GenericModelMapper::map($_GET, OaiRepo::class);

        // deleteDescription
        $oaiRepoDescription = $this->oaiRepoDescriptionRepository->withModels()->findOneBy([
            'repo' => $oaiRepo->getId()
        ]);
        if ($oaiRepoDescription) {
            $this->oaiRepoDescriptionRepository->delete($oaiRepoDescription, ['repo']);
        }

        $this->oaiRepoRepository->delete($oaiRepo);

        FlashMessage::add('Record deleted.', FlashMessage::TYPE_SUCCESS);
        Redirect::to('list', 'Repo', [
            'id' => $oaiRepo->getId()
        ]);

    }


    /**
     * @return void
     * @throws \JsonException
     */
    public function validateRepoId(): void
    {

        header('Content-Type: application/json');

        $repoId = trim($_GET['repoId'] ?? '');
        $exists = false;

        if ($repoId !== '') {
            $exists = (bool) $this->oaiRepoRepository->findOneBy([
                'id' => $repoId
            ]);
        }

        echo json_encode(['exists' => $exists], JSON_THROW_ON_ERROR);
        exit;
    }


}
