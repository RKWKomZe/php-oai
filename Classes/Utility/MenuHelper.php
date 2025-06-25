<?php

namespace RKW\OaiConnector\Utility;

class MenuHelper
{
    public static function renderMenuLink(
        ?string $controller,
        ?string $action,
        string $label,
        array $params = [],
        array $attributes = [],
        string $linkClass = 'nav-link' // can be changed to 'dropdown-item' etc.
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

        // Compose link class
        $classList = [$linkClass];
        if ($isActive) {
            $classList[] = 'active';
            if ($linkClass === 'nav-link') {
                $classList[] = 'text-orange';
            }
        }

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

        // Convert attributes array into HTML string
        $attrHtml = '';
        foreach ($attributes as $key => $value) {
            $attrHtml .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }

        return sprintf(
            '<li class="nav-item"><a class="%s" href="%s"%s>%s</a></li>',
            implode(' ', $classList),
            htmlspecialchars($href, ENT_QUOTES),
            $attrHtml,
            htmlspecialchars($label, ENT_QUOTES)
        );
    }
}
