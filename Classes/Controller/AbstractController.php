<?php
// === /Classes/Controller/AbstractController.php ===

namespace RKW\OaiConnector\Controller;

use PDO;
use RKW\OaiConnector\Utility\ConfigLoader;

/**
 * AbstractController
 *
 * Base abstract controller class providing essential functionalities such as
 * configuration loading, database connection, and template rendering.
 */
abstract class AbstractController
{
    protected array $settings = [];
    private ?PDO $pdo = null;

    /**
     * constructor
     */
    public function __construct()
    {
        $this->settings = ConfigLoader::load();
    }

    /**
     * Retrieves the PDO instance, creating it if it does not already exist.
     *
     * @return PDO The PDO instance.
     */
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


    /**
     * Renders a template based on the current controller and method.
     *
     * The method determines the template file to be included by analyzing
     * the calling method and the corresponding controller name. If no
     * method name is provided, the method name is derived from the
     * debug backtrace, defaulting to 'index' if unavailable.
     *
     * The layout file and extracted template file are included via
     * rendering logic provided by external utility functions or classes.
     *
     * @param string|null $methodName The name of the method to locate the appropriate template. Defaults to null.
     * @param array $data An array of data to pass to the layout or template.
     *
     * @return void
     */
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

        include_once $config['app']['basePath'] . '/Resources/Private/Layouts/DefaultLayout.php';
        renderLayout($templateFile, $data);
    }
}
