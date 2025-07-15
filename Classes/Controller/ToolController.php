<?php

namespace RKW\OaiConnector\Controller;


use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Repository\OaiSetRepository;

class ToolController extends AbstractController
{
    private ?OaiSetRepository $oaiSetRepository = null;

    private ?OaiRepoRepository $oaiRepoRepository = null;

    protected function getOaiSetRepository(): OaiSetRepository
    {
        return $this->oaiSetRepository ??= new OaiSetRepository();
    }

    protected function getOaiRepoRepository(): OaiRepoRepository
    {
        return $this->oaiRepoRepository ??= new OaiRepoRepository();
    }

    public function __construct()
    {
        parent::__construct();
        $this->oaiSetRepository = $this->getOaiSetRepository();
        $this->oaiRepoRepository = $this->getOaiRepoRepository();
    }

    public function query(): void
    {
        $repoList = $this->oaiRepoRepository->withModels()->findAll();
        $repoSetList = $this->oaiSetRepository->withModels()->findAll();

        $setsByRepo = [];
        foreach ($repoSetList as $repoSet) {
            $setsByRepo[$repoSet->getRepo()][] = ['setSpec' => $repoSet->getSetSpec(), 'setName' => $repoSet->getSetName()];

        }

        $this->render('query', [
            'repoList' => $repoList,
            'setsByRepo' => $setsByRepo,
        ]);
    }


    public function fullImport(): void
    {
        $this->render('fullImport', []);
    }
}
