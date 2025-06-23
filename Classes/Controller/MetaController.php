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
use Symfony\Component\VarDumper\VarDumper;

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

    public function __construct()
    {
        parent::__construct();
        $this->oaiMetaRepository = $this->getOaiMetaRepository();
        $this->oaiRepoRepository = $this->getOaiRepoRepository();
    }

    /**
     * @throws \ReflectionException
     */
    public function list(): void
    {
        //$pagination = PaginationFactory::fromRequestValues();

        $metaList = $this->oaiMetaRepository->withModels()->findAll();

        $this->render('list', [
            'metaList' => $metaList,
        ]);
    }


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
     * @throws \ReflectionException
     */
    public function new(): void
    {
        $repoList = $this->oaiRepoRepository->withModels()->findAll();
        $this->render('new', ['repoList' => $repoList]);
    }

    public function create(): void
    {

        /*
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
        */

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


    public function update(): void
    {
        //$repo = new \RKW\OaiConnector\Model\OaiRepo($_POST);

        $oaiMeta = GenericModelMapper::map($_POST, OaiMeta::class);

        $oaiMeta->setUpdated(date('Y-m-d H:i:s'));

        $this->oaiMetaRepository->update($oaiMeta, ['metadataPrefix', 'repo']);

        FlashMessage::add('Datensatz erfolgreich bearbeitet.', FlashMessage::TYPE_SUCCESS);

        Redirect::to('show', 'Meta', ['prefix' => $oaiMeta->getMetadataPrefix(), 'repo' => $oaiMeta->getRepo()]);
    }


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
