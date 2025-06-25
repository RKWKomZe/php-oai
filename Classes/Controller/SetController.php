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
use Symfony\Component\VarDumper\VarDumper;

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

    public function __construct()
    {
        parent::__construct();
        $this->oaiSetRepository = $this->getOaiSetRepository();
        $this->oaiSetDescriptionRepository = $this->getOaiSetDescriptionRepository();
        $this->oaiRepoRepository = $this->getOaiRepoRepository();
    }

    /**
     * @throws \ReflectionException
     */
    public function list(): void
    {
        $setList = $this->oaiSetRepository->withModels()->findAll();

        $this->render('list', [
            'setList' => $setList,
        ]);
    }


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
     * @throws \ReflectionException
     */
    public function new(): void
    {
        $repoList = $this->oaiRepoRepository->withModels()->findAll();
        $this->render('new', ['repoList' => $repoList]);
    }

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
