<?php

namespace RKW\OaiConnector\Integration\Shopware;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use RKW\OaiConnector\Factory\LoggerFactory;
use RKW\OaiConnector\Utility\ConfigLoader;

/**
 * Class ShopwareOaiFetcher
 *
 * Handles communication and data transformation between the application and the Shopware API for OAI-related imports and product queries.
 */
class ShopwareOaiFetcher
{

    /**
     * @var string
     */
    private string $baseUrl;


    /**
     * @var string
     */
    private string $clientId;


    /**
     * @var string
     */
    private string $clientSecret;


    /**
     * @var ?LoggerInterface|LoggerFactory|null
     */
    private LoggerInterface|null|LoggerFactory $logger = null;


    /**
     * constructor
     */
    public function __construct()
    {

        $config = ConfigLoader::load();

        $this->baseUrl = $config['api']['shopware']['baseUrl'];
        $this->clientId = $config['api']['shopware']['clientId'];
        $this->clientSecret = $config['api']['shopware']['clientSecret'];

        $this->logger = LoggerFactory::get();

    }


    /**
     * Fetches and transforms a list of products based on the provided filter options.
     *
     * @param array $filterOptions Options to filter the products.
     * @param bool $returnRawDataArray Determines whether to return the raw product data array or transformed records.
     * @return array The processed list of products, either raw or transformed.
     * @throws GuzzleException
     */
    public function fetchAndTransform(
        array $filterOptions = [],
        bool $returnRawDataArray = false
    ): array
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
     * Fetches a single product by its ID from the Shopware API.
     *
     * This method retrieves product details using the Shopware API with admin rights,
     * specifying necessary associations and transformations. For local DDEV environments,
     * SSL verification is disabled. In case no result is returned from the API, an exception is thrown.
     *
     * @param string $productId The unique identifier of the product to fetch.
     * @return array An array containing the transformed product data.
     * @throws \RuntimeException|GuzzleException If the Shopware API returns an empty result for the provided product ID.
     * @throws \JsonException
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
            ],
            'json' => [
                'limit' => 1,
                'filter' => [['type' => 'equals', 'field' => 'id', 'value' => $productId]],
            ]
        ]);
        */

        $productList = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (empty($productList)) {
            throw new \RuntimeException("Shopware API returned empty result for product ID: {$productId}");
        }

        return $this->transformProduct($productList['data']);

    }


    /**
     * Transforms a product array into a standardized format.
     *
     * @param array $product The product data to transform.
     * @return array The transformed product data.
     */
    protected function transformProduct(array $product): array
    {

        $title = $product['translated']['name'] ?? $product['name'] ?? 'Kein Titel';
        $description = $product['translated']['description'] ?? '';
        $createdAt = $product['createdAt'] ?? date('Y-m-d');

        return [
            'identifier' => $product['id'],
            'datestamp' => $createdAt,
            'title' => $title,
            'description' => $description,
            'url' => $this->baseUrl . "/detail/{$product['id']}",

            'productNumber' => $product['productNumber'] ?? '',
            'releaseDate' => $product['releaseDate'] ?? '',
            'categoryIds' => $product['categoryIds'] ?? [],
            'customFields' => $product['customFields'] ?? [],

            // auf basis von Steffens PDF
            'digital_address' => '???',
        ];

    }


    /**
     * Fetches an access token from the API using client credentials.
     *
     * @return string The access token or an empty string if retrieval fails.
     * @throws \JsonException
     */
    private function fetchAccessToken(): string
    {

        $response = file_get_contents("{$this->baseUrl}/api/oauth/token", false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode([
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret
                ], JSON_THROW_ON_ERROR)
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]));

        $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $data['access_token'] ?? '';

    }


    /**
     * Fetches products from the API by applying filters and pagination options.
     *
     * @param string $accessToken The access token used for authentication with the API.
     * @param array $filterOptions An array of filter options for fetching products, such as date ranges.
     *
     * @return array|null The decoded JSON response containing the list of products.
     * @throws GuzzleException
     */
    protected function fetchProducts(string $accessToken, array $filterOptions): ?array
    {

        #$url = $this->baseUrl . '/api/product';
        $url = $this->baseUrl . '/api/search/product';

        $client = new Client([
            'base_uri' => $url,
            'verify' => false, // lokal ggf. nötig bei self-signed SSL
        ]);

        $filters = $this->setFilters($filterOptions);

        // @toDo: Maybe change to array $filterOptions
        $page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) && $_GET['limit'] > 0 && $_GET['limit'] <= 500
            ? (int) $_GET['limit']
            : 25;


        $this->logger->info('Start Shopware fetch', ['endpoint' => $url]);

        $response = null;


        /* @todo: Maybe extract theo following try/catch blocks to make the method more readable. */
        try {
            /* @todo: check base url, see above */
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
        } catch (GuzzleException $e) {
            // catches all connection/timeouts/etc.
            $this->logger->error('Shopware API request failed', [
                'error' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            // final safeguard (unexpected runtime errors)
            $this->logger->critical('Unexpected error in Shopware request', [
                'error' => $e->getMessage(),
            ]);
        }


        // if we got here, $response is guaranteed to be set
        try {

            $body = (string)$response->getBody();
            if ($body === '') {
                $this->logger->warning('Shopware API returned empty body');
                return null;
            }

            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        } catch (\JsonException $e) {

            $this->logger->error('Shopware API returned invalid JSON', [
                'error' => $e->getMessage(),
            ]);

            return null;

        }

    }


    /**
     * @param array $filterOptions
     * @return array|array[]
     */
    protected function setFilters(array $filterOptions): array
    {

        $filters = [];

        if (
            isset($filterOptions['fromDate'], $filterOptions['untilDate'])
        ) {
            $filters = [
                // show only main products (show no product variations without name etc)
                [
                    'type'  => 'equals',
                    'field' => 'parentId',
                    'value' => null
                ],
                // Filter: order by creation date
                [
                    'type'       => 'range',
                    'field'      => 'createdAt',
                    'parameters' => [
                        'gte' => $filterOptions['fromDate']->format('Y-m-d\TH:i:s.000\Z'),
                        'lte' => $filterOptions['untilDate']->format('Y-m-d\TH:i:s.999\Z'),
                    ],
                ]
            ];
        }

        return $filters;

    }

}
