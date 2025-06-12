<?php

namespace RKW\OaiConnector\Integration\Shopware;

use RKW\OaiConnector\Utility\ConfigLoader;

class ShopwareOaiFetcherOld

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

    public function fetchAccessToken(): string
    {
        $url = "{$this->baseUrl}/api/oauth/token";
        $options = [
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
        ];
        $response = file_get_contents($url, false, stream_context_create($options));
        $data = json_decode($response, true);

        // @toDo: Logger

        return $data['access_token'];

            //?? throw new Exception("Kein Access-Token erhalten");
    }

    public function fetchProducts(string $accessToken): array
    {
        $url = "{$this->baseUrl}/api/product";
        $options = [
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
        ];
        $response = file_get_contents($url, false, stream_context_create($options));
        $data = json_decode($response, true);
        return $data['data'] ?? [];
    }

    public function transformProductToOai(array $product): array
    {
        $attributes = $product['attributes'] ?? [];
        $title = $attributes['translated']['name']
            ?? $attributes['name']
            ?? 'Kein Titel';

        return [
            'identifier' => $product['id'],
            'datestamp' => $attributes['createdAt'] ?? date('Y-m-d'),
            'title' => $title,
            'description' => $attributes['translated']['description'] ?? '',
            'url' => "https://rkw-shopware.ddev.site/detail/{$product['id']}"
        ];
    }

    public function fetchAndTransform(): array
    {
        $token = $this->fetchAccessToken();
        $products = $this->fetchProducts($token);

        $oaiRecords = [];
        foreach ($products as $product) {
            $oaiRecords[] = $this->transformProductToOai($product);
        }

        return $oaiRecords;
    }
}