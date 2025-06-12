<?php

use RKW\OaiConnector\Controller\ErrorController;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    // Sanitize controller/action input
    $controllerName = ucfirst(isset($_GET['controller']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['controller']) : 'Index');
    $actionName = isset($_GET['action']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_GET['action']) : 'index';

// Create fully qualified class name
    $controllerClass = "RKW\\OaiConnector\\Controller\\{$controllerName}Controller";

    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();

        if (!$controller instanceof \RKW\OaiConnector\Controller\AbstractController) {
            (new ErrorController())->forbidden('Ungültiger Controller-Typ: ' . $controllerClass);
            exit;
        }

        if (method_exists($controller, $actionName)) {
            $controller->$actionName();
            exit;
        } else {
            (new ErrorController())->notFound('Die Aktion "' . $actionName . '" existiert nicht im Controller "' . $controllerName . '".');
            exit;
        }
    } else {
        (new ErrorController())->notFound('Controller "' . $controllerClass . '" existiert nicht.');
        exit;
    }
} catch (\Throwable $e) {
    // Optional: Fehler loggen
    error_log('[EXCEPTION] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    // Fehlerseite ausgeben
    (new ErrorController())->internal('Ein interner Fehler ist aufgetreten: ' . $e->getMessage());
    exit;
}

