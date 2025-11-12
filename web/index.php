<?php


use RKW\OaiConnector\Controller\ErrorController;
use RKW\OaiConnector\Factory\LoggerFactory;

require_once __DIR__ . '/../vendor/autoload.php';

(new \RKW\OaiConnector\Security\Gatekeeper())->handle();


try {

    // ### LOGGING ###

    // Define path once, e.g. /var/log relative to project root
    $logDir = __DIR__ . '/../logs';

    // Initialize global Monolog instance
    LoggerFactory::init($logDir, 'oai-app');


    // ### ROUTING ###

    // Sanitize controller/action input
    $controllerName = ucfirst(isset($_GET['controller']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['controller']) : 'Index');
    $actionName = isset($_GET['action']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['action']) : 'index';

    // Create fully qualified class name
    $controllerClass = "RKW\\OaiConnector\\Controller\\{$controllerName}Controller";

    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();

        if (!$controller instanceof \RKW\OaiConnector\Controller\AbstractController) {
            (new ErrorController())->forbidden('Invalid controller type: ' . $controllerClass);
            exit;
        }
        if (method_exists($controller, $actionName)) {
            $controller->$actionName();
            exit;
        } else {
            (new ErrorController())->notFound('The action "' . $actionName . '" does not exist in the controller "' . $controllerName . '".');
            exit;
        }
    } else {
        (new ErrorController())->notFound('Controller "' . $controllerClass . '" does not exist.');
        exit;
    }
} catch (\Throwable $e) {
    /*
    // @toDo: Problem mit Template-Dopplung und bzw. crash, wenn Template bereits includiert (include_once wäre nur Symptombehandlung)
    // Optional: Fehler loggen
    error_log('[EXCEPTION] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    // Fehlerseite ausgeben
    (new ErrorController())->internal('Ein interner Fehler ist aufgetreten: ' . $e->getMessage() . '<pre>' . $e->getTraceAsString() . '</pre>');
    exit;
    */
    // Log the error
    error_log('[EXCEPTION] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    // Direct emergency error output – no controller, no layout
    http_response_code(500);
    ?>

    <h1>Ein interner Fehler ist aufgetreten</h1>
    <p><?= htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') ?></p>
    <h2>Fehlerquelle</h2>
    <p><strong>Datei:</strong> <?= $e->getFile() ?></p>
    <p><strong>Zeile:</strong> <?= $e->getLine() ?></p>
    <h2>Stacktrace</h2>
    <pre><?= htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8') ?></pre>

    <?php
    exit;
}

