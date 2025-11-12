<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Repository\OaiItemMetaRepository;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Utility\DbConnection;

/**
 * IndexController
 *
 * Controller managing OAI repositories and related functionalities.
 */
class IndexController extends AbstractController
{
    /**
     * @var OaiItemMetaRepository|null
     */
    private ?OaiItemMetaRepository $oaiItemMetaRepository = null;

    /**
     * @var OaiRepoRepository|null
     */
    private ?OaiRepoRepository $repoRepository = null;

    /**
     * @return OaiItemMetaRepository
     */
    protected function getOaiItemMetaRepository(): OaiItemMetaRepository
    {
        return $this->oaiItemMetaRepository ??= new OaiItemMetaRepository($this->settings['oai']['defaultRepoId']);
    }

    /**
     * @return OaiRepoRepository
     */
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
     * Handles the index action.
     *
     * This action retrieves the latest 30 entries from the `oai_update_log` table
     * and renders them using the specified view.
     *
     * @return void
     */
    public function index(): void
    {

        $pdo = DbConnection::get();

        $stmt = $pdo->query('
        SELECT *
        FROM oai_update_log
        ORDER BY id DESC
        LIMIT 30
    ');

        $logs = $stmt->fetchAll();

        $this->render('index', [
            'logs' => $logs,
        ]);
    }

}
