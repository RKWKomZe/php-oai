<?php

namespace RKW\OaiConnector\Controller;

use RKW\OaiConnector\Factory\PaginationFactory;
use RKW\OaiConnector\Repository\OaiItemMetaRepository;
use RKW\OaiConnector\Repository\OaiRepoRepository;
use RKW\OaiConnector\Utility\ConfigLoader;
use RKW\OaiConnector\Utility\DbConnection;
use RKW\OaiConnector\Utility\FlashMessage;
use RKW\OaiConnector\Utility\Pagination;
use RKW\OaiConnector\Utility\Redirect;

/**
 * IndexController
 *
 * Controller managing OAI repositories and related functionalities.
 */
class IndexController extends AbstractController
{
    private ?OaiItemMetaRepository $oaiItemMetaRepository = null;

    private ?OaiRepoRepository $repoRepository = null;

    protected function getOaiItemMetaRepository(): OaiItemMetaRepository
    {
        return $this->oaiItemMetaRepository ??= new OaiItemMetaRepository($this->settings['oai']['defaultRepoId']);
    }

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
