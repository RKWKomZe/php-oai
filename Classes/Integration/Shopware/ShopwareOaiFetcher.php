<?php
// === /Classes/Integration/Shopware/ShopwareOaiFetcher.php ===

namespace RKW\OaiConnector\Integration\Shopware;

use RKW\OaiConnector\Utility\ConfigLoader;

class ShopwareOaiFetcher
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $config = ConfigLoader::load();

        $this->baseUrl = $config['api']['shopware']['baseUrl'];
        $this->clientId = $config['api']['shopware']['clientId'];
        $this->clientSecret = $config['api']['shopware']['clientSecret'];
    }

    public function fetchAndTransform(): array
    {
        $accessToken = $this->fetchAccessToken();
        $products = $this->fetchProducts($accessToken);

        $records = [];
        foreach ($products as $product) {
            $attributes = $product['attributes'] ?? [];
            $title = $attributes['translated']['name'] ?? $attributes['name'] ?? 'Kein Titel';

            $records[] = [
                'identifier' => $product['id'],
                'datestamp' => $attributes['createdAt'] ?? date('Y-m-d'),
                'title' => $title,
                'description' => $attributes['translated']['description'] ?? '',
                'url' => $this->baseUrl . "/detail/{$product['id']}"
            ];
        }
        return $records;
    }

    private function fetchAccessToken(): string
    {
        $response = file_get_contents("{$this->baseUrl}/api/oauth/token", false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode([
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret
                ])
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]));

        $data = json_decode($response, true);
        return $data['access_token'] ?? '';
    }

    private function fetchProducts(string $accessToken): array
    {
        $url = $this->baseUrl . '/api/product';
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    "Authorization: Bearer $accessToken",
                    "Accept: application/json"
                ]
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);
        return $data['data'] ?? [];
    }
}
