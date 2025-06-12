<?php
// === /Classes/Controller/AbstractController.php ===

namespace RKW\OaiConnector\Controller;

use PDO;
use RKW\OaiConnector\Utility\ConfigLoader;
use Symfony\Component\VarDumper\VarDumper;

abstract class AbstractController
{
    protected array $settings = [];
    private ?PDO $pdo = null;

    public function __construct()
    {
        $this->settings = ConfigLoader::load();
    }

    protected function getPdo(): PDO
    {
        if (!$this->pdo) {
            $this->pdo = new PDO(
                'mysql:host=localhost;dbname=your_db;charset=utf8mb4',
                'user',
                'password',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }
        return $this->pdo;
    }


    protected function render(?string $methodName = null, array $data = []): void
    {
        $config = ConfigLoader::load();

        $classParts = explode('\\', static::class);
        $controllerClass = end($classParts); // e.g., ImportController
        $controllerName = str_replace('Controller', '', $controllerClass); // e.g., Import

        if ($methodName === null) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $methodName = $trace[1]['function'] ?? 'index';
        }

        $templateFile = $config['app']['basePath'] . "/Resources/Private/Templates/{$controllerName}/{$methodName}.php";

        include $config['app']['basePath'] . '/Resources/Private/Layouts/DefaultLayout.php';
        renderLayout($templateFile, $data);
    }
}
