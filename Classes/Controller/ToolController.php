<?php

namespace RKW\OaiConnector\Controller;


use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Repository\OaiSetRepository;

/**
 * ToolController
 *
 * Controller that provides functionality to handle OAI Set and Repo operations.
 */
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


    /**
     * constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->oaiSetRepository = $this->getOaiSetRepository();
        $this->oaiRepoRepository = $this->getOaiRepoRepository();
    }


    /**
     * Handles the "query" action by retrieving repository and set data,
     * organizing sets grouped by repositories, and rendering the result.
     */
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


    /**
     * Handles the "fullImport" action by rendering the designated view.
     */
    public function fullImport(): void
    {
        $this->render('fullImport', []);
    }
}
