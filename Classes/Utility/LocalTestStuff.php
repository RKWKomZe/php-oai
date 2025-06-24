<?php
namespace RKW\OaiConnector\Utility;

/**
 * This class only holds stuff you need for specific local things (like fixing ddev container-urls)
 */
class LocalTestStuff
{
    /**
     * Replaces the internal Shopware media domain with the public base URL.
     *
     * @param string $url The original URL from the Shopware API
     * @return string Corrected media URL
     */
    public static function fixShopwareMediaUrl(string $url): string
    {
        // @toDo: Error log if this is used in production mode?

        $config = ConfigLoader::load();
        $realShopwareUrl = $config['testing']['localShopwareUrl'];


        // Parse the incoming URL
        $parsedUrl = parse_url($url);

        // Extrahiere nur den Pfad + Query
        $path = $parsedUrl['path'] ?? '';
        $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';

        // Gib die neue vollständige URL zurück
        return rtrim($realShopwareUrl, '/') . $path . $query;
    }
}