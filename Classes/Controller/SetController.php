<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Model\OaiSet;
use RKW\OaiConnector\Factory\PaginationFactory;
use RKW\OaiConnector\Mapper\GenericModelMapper;
use RKW\OaiConnector\Model\OaiSetDescription;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Repository\OaiSetDescriptionRepository;
use RKW\OaiConnector\Repository\OaiSetRepository;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Redirect;

/**
 * SetController
 *
 * Controller managing operations on OAI Sets and their descriptions.
 */
class SetController extends AbstractController
{
    private ?OaiSetRepository $oaiSetRepository = null;

    private ?OaiSetDescriptionRepository $oaiSetDescriptionRepository = null;

    private ?OaiRepoRepository $oaiRepoRepository = null;

    protected function getOaiSetRepository(): OaiSetRepository
    {
        return $this->oaiSetRepository ??= new OaiSetRepository();
    }

    protected function getOaiSetDescriptionRepository(): OaiSetDescriptionRepository
    {
        return $this->oaiSetDescriptionRepository ??= new OaiSetDescriptionRepository();
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
        $this->oaiSetRepository = $this->getOaiSetRepository();
        $this->oaiSetDescriptionRepository = $this->getOaiSetDescriptionRepository();
        $this->oaiRepoRepository = $this->getOaiRepoRepository();
    }


    /**
     * Lists all sets and renders the list view.
     */
    public function list(): void
    {
        $setList = $this->oaiSetRepository->withModels()->findAll();

        $this->render('list', [
            'setList' => $setList,
        ]);
    }


    /**
     * Handles the display of a specific record and its associated data.
     * Validates required parameters, retrieves the record and related entities,
     * and renders the appropriate view. Redirects with messages if parameters
     * are missing or the record is not found.
     */
    public function show(): void
    {
        $setSpec = $_GET['spec'] ?? null;
        $repoName = $_GET['repo'] ?? null;

        if (!$setSpec || !$repoName) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Set');
            return;
        }

        $oaiSet = $this->oaiSetRepository
            ->withModels()
            ->findOneBy([
                'setSpec' => $setSpec,
                'repo' => $repoName
            ]);

        // @toDo: Ggf warnung in den VIEW verlegen?
        if (!$oaiSet) {
            FlashMessage::add('Record not found.', FlashMessage::TYPE_WARNING);
            Redirect::to('list', 'Set');
            return;
        }

        $oaiSetDescription = $this->oaiSetDescriptionRepository->withModels()->findOneBy(['repo' => $oaiSet->getRepo(), 'setSpec' => $oaiSet->getSetSpec()]);

        $repoList = $this->oaiRepoRepository->withModels()->findAll();

        // render view with model object
        $this->render('show', [
            'oaiSet' => $oaiSet,
            'setSpec' => $setSpec,
            'repoList' => $repoList,
            'oaiSetDescription' => $oaiSetDescription,
        ]);
    }


    /**
     * Prepares and renders the 'new' view with the list of repositories and their associated models.
     */
    public function new(): void
    {
        $repoList = $this->oaiRepoRepository->withModels()->findAll();
        $this->render('new', ['repoList' => $repoList]);
    }


    /**
     * Creates a new OAI set and its corresponding description.
     * Maps user input to the appropriate models, updates timestamps, and saves both the set and description.
     * Displays a success or failure message and redirects to the appropriate page based on the outcome.
     */
    public function create(): void
    {
        $oaiSet = GenericModelMapper::map($_POST, OaiSet::class);
        $oaiSetDescription = GenericModelMapper::map($_POST, OaiSetDescription::class);

        $oaiSet->setUpdated(date('Y-m-d H:i:s'));

        $success = $this->oaiSetRepository->insert($oaiSet);

        $this->oaiSetDescriptionRepository->upsert($oaiSetDescription);

        // @toDo: Meldung auch für Description?

        if ($success) {
            FlashMessage::add('Set created successfully.',  FlashMessage::TYPE_SUCCESS);
            Redirect::to('show', 'Set', ['spec'  => $oaiSet->getSetSpec(), 'repo' => $oaiSet->getRepo()]);
        } else {
            FlashMessage::add('Set could not be saved.',   FlashMessage::TYPE_DANGER);
            Redirect::to('new', 'Set');
        }
    }


    /**
     * Handles the editing of a specific record.
     *
     * This method retrieves and validates the necessary parameters (`spec` and `repo`) from the request.
     * If the required parameters are missing, it displays a flash message indicating the issue and redirects to the "list" view of sets.
     * Upon successful validation, it fetches the relevant OAI set, its description, and the repository list to render the edit view.
     */
    public function edit(): void
    {
        $setSpec = $_GET['spec'] ?? null;
        $repoName = $_GET['repo'] ?? null;

        if (!$setSpec || !$repoName) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Set');
            return;
        }

        $oaiSet = $this->oaiSetRepository->withModels()->findOneBy([
            'setSpec' => $setSpec,
            'repo' => $repoName
        ]);

        $oaiSetDescription = $this->oaiSetDescriptionRepository->withModels()->findOneBy(['repo' => $oaiSet->getRepo(), 'setSpec' => $oaiSet->getSetSpec()]);

        $repoList = $this->oaiRepoRepository->withModels()->findAll();

        $this->render('edit', [
            'oaiSet' => $oaiSet,
            'repoList' => $repoList,
            'oaiSetDescription' => $oaiSetDescription,
        ]);
    }


    /**
     * Updates the OAI set and its description based on the provided POST data.
     *
     * Maps the POST parameters to the corresponding OAI set and description models,
     * updates the set's last modified timestamp, and performs repository operations
     * to save the changes. A success message is then added, and a redirect is triggered.
     */
    public function update(): void
    {
        $oaiSet = GenericModelMapper::map($_POST, OaiSet::class);
        $oaiSetDescription = GenericModelMapper::map($_POST, OaiSetDescription::class);

        $oaiSet->setUpdated(date('Y-m-d H:i:s'));
        $this->oaiSetRepository->update($oaiSet, ['setSpec', 'repo']);

        $this->oaiSetDescriptionRepository->upsert($oaiSetDescription);

        // @toDo: Meldung auch für Description?

        FlashMessage::add('Datensatz erfolgreich bearbeitet.', FlashMessage::TYPE_SUCCESS);

        Redirect::to('show', 'Set', ['spec' => $oaiSet->getSetSpec(), 'repo' => $oaiSet->getRepo()]);
    }


    /**
     * Deletes a record based on the specified set specification and repository name.
     *
     * This method retrieves the set specification (`setSpec`) and repository name (`repoName`)
     * from GET parameters. If either parameter is absent, an error message is displayed,
     * and the user is redirected.
     *
     * If valid parameters are provided, the method deletes the associated set descriptions
     * and sets from their respective repositories. After successful deletion, a success
     * message is displayed, and the user is redirected to the set list page.
     */
    public function delete(): void
    {
        $setSpec = $_GET['spec'] ?? null;
        $repoName = $_GET['repo'] ?? null;

        if (!$setSpec || !$repoName) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Set');
            return;
        }

        // deleteDescription
        $oaiSetDescription = $this->oaiSetDescriptionRepository->withModels()->findOneBy([
            'setSpec' => $setSpec,
            'repo' => $repoName
        ]);
        if ($oaiSetDescription) {
            $this->oaiSetDescriptionRepository->delete($oaiSetDescription, ['setSpec', 'repo']);
        }

        $oaiSet = $this->oaiSetRepository->withModels()->findOneBy([
            'setSpec' => $setSpec,
            'repo' => $repoName
        ]);
        $this->oaiSetRepository->delete($oaiSet, ['setSpec', 'repo']);

        FlashMessage::add('Datensatz gelöscht.', FlashMessage::TYPE_SUCCESS);

        Redirect::to('list', 'Set');
    }


}
