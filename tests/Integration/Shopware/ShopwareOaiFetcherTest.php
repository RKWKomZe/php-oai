<?php

declare(strict_types=1);

namespace RKW\OaiConnector\Tests\Integration\Shopware;

use PHPUnit\Framework\TestCase;
use RKW\OaiConnector\Integration\Shopware\ShopwareOaiFetcher;

final class ShopwareOaiFetcherTest extends TestCase
{

    public function testTransformProductExtractsPdfDownload(): void
    {
        $fetcher = $this->createFetcherWithoutConstructor();

        $record = $this->invokeTransformProduct($fetcher, [
            'id' => 'product-1',
            'createdAt' => '2026-01-01T00:00:00.000+00:00',
            'translated' => [
                'name' => 'Testprodukt',
                'description' => 'Beschreibung',
            ],
            'productNumber' => 'SW1000',
            'downloads' => [
                [
                    'id' => 'download-1',
                    'mediaId' => 'media-1',
                    'media' => [
                        'id' => 'media-1',
                        'mimeType' => 'application/pdf',
                        'fileExtension' => 'pdf',
                        'fileSize' => 8266112,
                        'fileName' => 'test-pdf',
                        'url' => '',
                    ],
                ],
            ],
        ]);

        self::assertSame('media-1', $record['pdfDownload']['mediaId']);
        self::assertSame('application/pdf', $record['pdfDownload']['mimeType']);
        self::assertSame('pdf', $record['pdfDownload']['fileExtension']);
        self::assertSame(8266112, $record['pdfDownload']['fileSize']);
        self::assertSame('test-pdf', $record['pdfDownload']['fileName']);
    }


    private function createFetcherWithoutConstructor(): ShopwareOaiFetcher
    {
        $reflection = new \ReflectionClass(ShopwareOaiFetcher::class);
        $fetcher = $reflection->newInstanceWithoutConstructor();

        $baseUrl = $reflection->getProperty('baseUrl');
        $baseUrl->setValue($fetcher, 'https://shop.example.test');

        return $fetcher;
    }


    /**
     * @param ShopwareOaiFetcher $fetcher
     * @param array $product
     * @return array
     */
    private function invokeTransformProduct(ShopwareOaiFetcher $fetcher, array $product): array
    {
        $reflection = new \ReflectionClass($fetcher);
        $method = $reflection->getMethod('transformProduct');

        return $method->invoke($fetcher, $product);
    }
}
