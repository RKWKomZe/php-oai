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

    public function __construct()
    {
        parent::__construct();
        $this->oaiRepoRepository = $this->getOaiRepoRepository();
        $this->oaiRepoDescriptionRepository = $this->getOaiRepoDescriptionRepository();
    }

    /**
     * @throws \ReflectionException
     */
    public function list(): void
    {
        $pagination = PaginationFactory::fromRequestValues();

        $repoList = $this->oaiRepoRepository->withPagination($pagination)->withModels()->findAll();

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

    public function new(): void
    {
        $this->render('new');
    }

    public function create(): void
    {
        $oaiRepo = GenericModelMapper::map($_POST, OaiRepo::class);
        $oaiRepoDescription = GenericModelMapper::map($_POST, OaiRepoDescription::class);
        $oaiRepoDescription->setRepo($oaiRepo->getId());

        $oaiRepo->setUpdated(date('Y-m-d H:i:s'));

        $success = $this->oaiRepoRepository->insert($oaiRepo);

        $this->oaiRepoDescriptionRepository->upsert($oaiRepoDescription);

        // @toDo: Meldung auch für Description?

        if ($success) {
            FlashMessage::add('Repository wurde erfolgreich erstellt. Dazu sollte im anschluss eine "OAI Meta" mit verknüpften Schema-Prefix (z.B. "oai_dc= angelegt werden.',  FlashMessage::TYPE_SUCCESS);
            Redirect::to('show', 'Repo', ['id'  => $oaiRepo->getId()]);
        } else {
            FlashMessage::add('Repository could not be saved.',   FlashMessage::TYPE_DANGER);
            Redirect::to('new', 'Repo');
        }
    }



    public function edit(): void
    {
        $identifier = $_GET['id'] ?? null;

        if (!$identifier) {
            FlashMessage::add('Missing parameters for record view.', FlashMessage::TYPE_DANGER);
            Redirect::to('list', 'Repo');
        }

        $oaiRepo = $this->oaiRepoRepository->withModels()->findById($identifier);

        $oaiRepoDescription = $this->oaiRepoDescriptionRepository->withModels()->findOneBy(['repo' => $oaiRepo->getId()]);

        $this->render('edit', [
            'oaiRepo' => $oaiRepo,
            'oaiRepoDescription' => $oaiRepoDescription
        ]);
    }


    public function update(): void
    {
        $oaiRepo = GenericModelMapper::map($_POST, OaiRepo::class);
        $oaiRepoDescription = GenericModelMapper::map($_POST, OaiRepoDescription::class);
        $oaiRepoDescription->setRepo($oaiRepo->getId());

        $oaiRepo->setUpdated(date('Y-m-d H:i:s'));
        $this->oaiRepoRepository->update($oaiRepo);

        $this->oaiRepoDescriptionRepository->upsert($oaiRepoDescription);

        FlashMessage::add('Datensatz erfolgreich bearbeitet.', FlashMessage::TYPE_SUCCESS);

        Redirect::to('show', 'Repo', ['id' => $oaiRepo->getId()]);
    }


    public function delete(): void
    {
        $oaiRepo = GenericModelMapper::map($_GET, OaiRepo::class);

        // deleteDescription
        $oaiRepoDescription = $this->oaiRepoDescriptionRepository->withModels()->findOneBy(['repo' => $oaiRepo->getId()]);
        if ($oaiRepoDescription) {
            $this->oaiRepoDescriptionRepository->delete($oaiRepoDescription, ['repo']);
        }

        $this->oaiRepoRepository->delete($oaiRepo);

        FlashMessage::add('Datensatz gelöscht.', FlashMessage::TYPE_SUCCESS);

        Redirect::to('list', 'Repo', ['id' => $oaiRepo->getId()]);
    }


}
