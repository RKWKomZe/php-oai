<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Factory\PaginationFactory;
use RKW\OaiConnector\Repository\RepoDescriptionRepository;
use RKW\OaiConnector\Repository\RepoRepository;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Redirect;

class RepoController extends AbstractController
{
    private ?RepoRepository $repoRepository = null;
    private ?RepoDescriptionRepository $repoDescriptionRepository = null;

    protected function getRepoRepository(): RepoRepository
    {
        return $this->repoRepository ??= new RepoRepository();
    }

    protected function getRepoDescriptionRepository(): RepoDescriptionRepository
    {
        return $this->repoDescriptionRepository ??= new RepoDescriptionRepository();
    }

    public function __construct()
    {
        $this->repoRepository = $this->getRepoRepository();
    }

    /**
     * @throws \ReflectionException
     */
    public function list(): void
    {
        $pagination = PaginationFactory::fromRequestValues();

        $repoList = $this->repoRepository->withPagination($pagination)->withModels()->findAll();

        $this->render('list', [
            'repoList' => $repoList,
        ]);
    }


    public function show(): void
    {
        $identifier = $_GET['id'] ?? null;

        if (!$identifier) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Repo');
            return;
        }

        $oaiRepo = $this->repoRepository
            ->withModels()
            ->findOneBy([
                'id' => $identifier
            ]);

        // @toDo: Ggf warnung in den VIEW verlegen?
        if (!$oaiRepo) {
            FlashMessage::add('Record not found.', FlashMessage::TYPE_WARNING);
            Redirect::to('list', 'Index');
            return;
        }

        // render view with model object
        $this->render('show', [
            'oaiRepo' => $oaiRepo,
            'id' => $identifier,
        ]);
    }

    public function new(): void
    {
        $this->render('new');
    }

    public function create(): void
    {
        $data = $_POST;

        // Basic validation for required fields
        $requiredFields = ['id', 'repositoryName', 'baseURL', 'protocolVersion', 'deletedRecord', 'granularity'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                FlashMessage::add("Missing required field: {$field}",  FlashMessage::TYPE_DANGER);
                $this->redirect('Repo', 'new');
                return;
            }
        }

        // Map form data to Repo model using GenericModelMapper
        $mapper = new \RKW\OaiConnector\Mapper\GenericModelMapper();
        /** @var \RKW\OaiConnector\Model\Repo $repo */
        $repo = $mapper->mapArrayToModel($data, \RKW\OaiConnector\Model\Repo::class);

        // Set/update timestamp (not passed from form)
        $repo->setUpdated(date('Y-m-d H:i:s'));

        // Save to database
        $repoRepository = new \RKW\OaiConnector\Repository\RepoRepository();
        $success = $repoRepository->insert($repo);

        if ($success) {
            FlashMessage::add('Repository created successfully.',  FlashMessage::TYPE_SUCCESS);
            $this->redirect('Repo', 'list');
        } else {
            FlashMessage::add('Repository could not be saved.',   FlashMessage::TYPE_DANGER);
            $this->redirect('Repo', 'new');
        }
    }



    public function edit(): void
    {
        $id = $_GET['id'] ?? '';
        $oaiRepo = $this->repoRepository->findById($id);

        if (!$oaiRepo) {
            echo "Repository nicht gefunden.";
            return;
        }

        $this->render('edit', [
            'oaiRepo' => $oaiRepo
        ]);
    }


    public function update(): void
    {
        $repo = new \RKW\OaiConnector\Model\Repo($_POST);

        $this->update($repo);
    }


    public function delete(): void
    {
        $id = $_GET['id'] ?? '';

        $this->deleteModel($repo);
    }


}
