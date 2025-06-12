<?php

/*
How to:

use RKW\OaiConnector\Utility\RedirectService;

// Nur Aktion (innerhalb des aktuellen Controllers)
RedirectService::to('index');

// Aktion + Controller
RedirectService::to('run', 'Import');

// Mit zusätzlichen Parametern
RedirectService::to('show', 'Product', ['id' => 123, 'foo' => 'bar']);

Creates: /index.php?action=show&controller=Product&id=123&foo=bar
*/

namespace RKW\OaiConnector\Utility;

class Redirect
{
    /**
     * Redirect to a given controller action with optional parameters.
     *
     * @param string      $action      The action method to call (e.g. 'index')
     * @param string|null $controller  The controller class name without 'Controller' suffix (e.g. 'Import')
     * @param array       $params      Optional additional GET parameters
     */
    public static function to(string $action, ?string $controller = null, array $params = []): never
    {
        if (headers_sent()) {
            throw new \RuntimeException("Cannot redirect to controller/action because headers were already sent.");
        }

        $query = ['action' => $action];
        if ($controller !== null) {
            $query['controller'] = $controller;
        }

        $query = array_merge($query, $params);
        $queryString = http_build_query($query);

        header("Location: /index.php?$queryString");
        exit;
    }
}
