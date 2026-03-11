<?php

declare(strict_types=1);

namespace RKW\OaiConnector\Tests\Controller;

use PHPUnit\Framework\TestCase;
use RKW\OaiConnector\Controller\ImportController;
use RKW\OaiConnector\Integration\Shopware\ShopwareOaiFetcher;

final class ImportControllerReadinessReportTest extends TestCase
{

    public function testBuildDnbReadinessReportReturnsStructuredPayload(): void
    {
        $fetcher = new FakeShopwareOaiFetcher([
            'identifier' => 'prod-1',
            'title' => 'Testheft',
            'productNumber' => 'SW-123',
            'releaseDate' => '2026-02-20',
            'url' => 'https://example.org/doc.pdf',
            'categoryIds' => ['Fachkräfte'],
            'customFields' => [
                'custom_product_oai_resource_type' => 'ab',
                'custom_product_oai_publication_type' => 'issue',
                'custom_product_oai_identifier_journal' => 'RKW-Magazin',
                'custom_product_oai_issue' => '2',
                'custom_product_oai_access' => 'b',
                'custom_product_oai_license' => 'open-access',
            ],
        ]);

        $controller = new TestableImportController($fetcher);
        $payload = $controller->buildReport('prod-1');

        self::assertTrue($payload['success']);
        self::assertSame('prod-1', $payload['id']);
        self::assertSame('green', $payload['report']['status']);
        self::assertArrayHasKey('summary', $payload['report']);
        self::assertArrayHasKey('checklist', $payload['report']);
    }


    public function testBuildDnbReadinessReportThrowsWhenRecordMissing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product not found or could not be transformed');

        $controller = new TestableImportController(new FakeShopwareOaiFetcher(null));
        $controller->buildReport('missing-id');
    }
}


final class TestableImportController extends ImportController
{
    private ShopwareOaiFetcher $fetcher;

    public function __construct(ShopwareOaiFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    public function buildReport(string $identifier): array
    {
        return $this->buildDnbReadinessReportForProductId($identifier);
    }

    protected function getShopwareFetcher(): ShopwareOaiFetcher
    {
        return $this->fetcher;
    }
}


final class FakeShopwareOaiFetcher extends ShopwareOaiFetcher
{
    private ?array $record;

    public function __construct(?array $record)
    {
        $this->record = $record;
    }

    public function fetchSingleById(string $productId): ?array
    {
        return $this->record;
    }
}

