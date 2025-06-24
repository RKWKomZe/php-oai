<?php
// === /Classes/Integration/Shopware/ShopwareOaiFetcher.php ===

namespace RKW\OaiConnector\Integration\Shopware;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RKW\OaiConnector\Utility\ConfigLoader;
use Symfony\Component\VarDumper\VarDumper;

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


    /**
     * Used for OAI-Import
     *
     * @param array $filterOptions
     * @param bool $returnRawDataArray
     * @return array
     * @throws GuzzleException
     */
    public function fetchAndTransform(array $filterOptions = [], bool $returnRawDataArray = false): array
    {
        $accessToken = $this->fetchAccessToken();
        $productList = $this->fetchProducts($accessToken, $filterOptions);

        if ($returnRawDataArray) {
            return $productList;
        }

        $records = [];
        foreach ($productList['data'] as $product) {
            $records[] = $this->transformProduct($product);
        }
        return $records;
    }


    /**
     * Fetch a single product by ID from Shopware API.
     *
     * @param string $productId
     * @return array
     * @throws GuzzleException
     */
    public function fetchSingleById(string $productId): array
    {
        $accessToken = $this->fetchAccessToken();
        $url = $this->baseUrl . '/api/';

        $client = new Client([
            'base_uri' => $url,
            'verify' => false, // deaktiviert SSL-Prüfung für lokale DDEV-Umgebung
        ]);

        // IMPORTANT: For single product request the Shopware API-Integration needs ADMIN rights!
        $response = $client->get('product/' . $productId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ],
            'query' => [
                'associations[cover][associations][media]' => [],
                // weitere Abhängigkeiten bei Bedarf hinzufügen
            ],
        ]);

        // Alternative api query without api-ADMIN-access
        /*
        $response = $client->post('/api/search/product', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ],
            'query' => [
                'associations[cover][associations][media]' => [],
                // weitere Abhängigkeiten bei Bedarf hinzufügen
            ],
            'json' => [
                'limit' => 1,
                'filter' => [['type' => 'equals', 'field' => 'id', 'value' => $productId]],
            ]
        ]);
        */

        $productList = json_decode($response->getBody()->getContents(), true);

        if (empty($productList)) {
            throw new \RuntimeException("Shopware API returned empty result for product ID: {$productId}");
        }

        return $this->transformProduct($productList['data']);
    }


    /**
     * @param array $product
     * @return array
     */
    protected function transformProduct(array $product): array
    {
        $attributes = $product['attributes'] ?? [];

        return [
            'identifier' => $product['id'],
            'datestamp' => $attributes['createdAt'] ?? date('Y-m-d'),
            'title' => $attributes['translated']['name'] ?? $attributes['name'] ?? 'Kein Titel',
            'description' => $attributes['translated']['description'] ?? '',
            'url' => $this->baseUrl . "/detail/{$product['id']}"
        ];
    }


    /**
     * @return string
     */
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


    /**
     * @param string $accessToken
     * @param array $filterOptions
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function fetchProducts(string $accessToken, array $filterOptions): array
    {

        $url = $this->baseUrl . '/api/product';

        $client = new Client([
            'base_uri' => $url,
            'verify' => false, // lokal ggf. nötig bei self-signed SSL
        ]);

        $filters = [];

        if (
            isset($filterOptions['fromDate'])
            && isset($filterOptions['untilDate'])
        ) {
            $filters[] = [
                'type' => 'range',
                'field' => 'createdAt',
                'parameters' => [
                    'gte' => $filterOptions['fromDate']->format('Y-m-d\TH:i:s.000\Z'),
                    'lte' => $filterOptions['untilDate']->format('Y-m-d\TH:i:s.999\Z'),
                ],
            ];
        }

        // @toDo: Maybe change to array $filterOptions
        $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 && $_GET['limit'] <= 500
            ? (int) $_GET['limit']
            : 25;

        $response = $client->post('/api/search/product', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ],
            'json' => [
                'associations' => [
                    'cover' => ['associations' => ['media' => []]],
                ],
                'filter' => $filters,
                'limit' => $limit,
                'page' => $page,
                'sort' => [['field' => 'createdAt', 'order' => 'DESC']],
                'total-count-mode' => 1,
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}
