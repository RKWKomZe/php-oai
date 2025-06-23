<?php

namespace RKW\OaiConnector\Utility;

class MenuHelper
{
    public static function renderMenuLink(
        ?string $controller,
        ?string $action,
        string $label,
        array $params = []
    ): string {
        $currentController = $_GET['controller'] ?? null;
        $currentAction = $_GET['action'] ?? null;

        // Normalize for case-insensitive comparison
        $controller = $controller !== null ? strtolower($controller) : null;
        $action = $action !== null ? strtolower($action) : null;
        $currentController = $currentController !== null ? strtolower($currentController) : null;
        $currentAction = $currentAction !== null ? strtolower($currentAction) : null;

        // Determine if this is the active link (Controller match is sufficient)
        $isActive = $controller !== null && $controller === $currentController;

        $class = 'nav-link' . ($isActive ? ' active text-orange' : '');

        // Build href
        $query = [];

        if ($controller !== null) {
            $query['controller'] = $controller;
        }

        if ($action !== null) {
            $query['action'] = $action;
        }

        $query = array_merge($query, $params);
        $href = '/index.php';
        if (!empty($query)) {
            $href .= '?' . http_build_query($query);
        }

        return sprintf(
            '<li class="nav-item"><a class="%s" href="%s">%s</a></li>',
            $class,
            htmlspecialchars($href, ENT_QUOTES),
            htmlspecialchars($label, ENT_QUOTES)
        );
    }
}
