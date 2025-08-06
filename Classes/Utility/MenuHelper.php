<?php

namespace RKW\OaiConnector\Utility;

/**
 * MenuHelper
 *
 * Provides helper methods for rendering navigation menu links.
 */
class MenuHelper
{
    /**
     * Renders a menu link element for a navigation menu.
     *
     * @param string|null $controller The name of the controller to be linked (optional).
     * @param string|null $action The name of the action to be linked (optional).
     * @param string $label The label text for the menu link.
     * @param array $params Additional query parameters to include in the link.
     * @param array $attributes HTML attributes to include in the `<a>` tag (e.g., `['target' => '_blank']`).
     * @param string $linkClass The CSS class to apply to the `<a>` tag. Defaults to 'nav-link'.
     *
     * @return string The generated HTML code for the menu link (wrapped in a `<li>` element).
     */
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

        if ($controller !== null && $controller === $currentController) {
            // If a specific action is set for the menu link, require it to match as well
            if (isset($action) && $action !== '' && isset($currentAction)) {
                $isActive = ($action === $currentAction);
            } else {
                // Fallback: Only controller match
                $isActive = true;
            }
        }

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
