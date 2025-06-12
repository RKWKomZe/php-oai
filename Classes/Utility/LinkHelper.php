<?php
namespace RKW\OaiConnector\Utility;

class LinkHelper
{
    /**
     * Render an HTML <a> tag with optional query inheritance.
     *
     * @param string $controller
     * @param string $action
     * @param array $query Query parameters like ['id' => 123]
     * @param string $label Link text or HTML
     * @param array $attributes HTML attributes like ['class' => 'btn']
     * @param bool $inheritQuery If true, includes matching $_GET values not overridden
     * @return string
     */
    public static function renderLink(
        string $controller,
        string $action,
        array $query = [],
        string $label = '',
        array $attributes = [],
        bool $inheritQuery = false
    ): string {
        // Start with base parameters
        $params = array_merge([
            'controller' => $controller,
            'action' => $action,
        ], $query);

        // Merge in $_GET if inheritQuery is true
        if ($inheritQuery) {
            foreach ($_GET as $key => $value) {
                if (!array_key_exists($key, $params)) {
                    $params[$key] = $value;
                }
            }
        }

        $queryString = http_build_query($params);
        $url = '/index.php?' . $queryString;

        // Build attributes
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }

        return '<a href="' . htmlspecialchars($url) . '"' . $attrString . '>' . $label . '</a>';
    }
}
