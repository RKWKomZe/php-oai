<?php

declare(strict_types=1);

namespace RKW\OaiConnector\Tests\Utility;

use PHPUnit\Framework\TestCase;
use RKW\OaiConnector\Utility\MarcXmlPreflightValidator;

final class MarcXmlPreflightValidatorTest extends TestCase
{

    public function testValidIssueRecordIsGreen(): void
    {
        $validator = new MarcXmlPreflightValidator();
        $report = $validator->validate([
            'identifier' => '019abc',
            'title' => 'RKW Magazin Test',
            'productNumber' => 'SW-1000',
            'releaseDate' => '2026-02-20',
            'url' => 'https://example.org/file.pdf',
            'categoryIds' => ['Fachkräfte', 'Transformation'],
            'customFields' => [
                'custom_product_oai_resource_type' => 'ab',
                'custom_product_oai_publication_type' => 'issue',
                'custom_product_oai_access' => 'b',
                'custom_product_oai_identifier_journal' => 'RKW-Magazin',
                'custom_product_oai_issue' => '2',
                'custom_product_oai_license' => 'open-access',
            ],
        ]);

        self::assertSame('green', $report['status']);
        self::assertSame([], $report['errors']);
        self::assertSame([], $report['warnings']);
    }


    public function testMissingProductNumberIsRed(): void
    {
        $validator = new MarcXmlPreflightValidator();
        $report = $validator->validate([
            'identifier' => '019abc',
            'title' => 'Monografie',
            'releaseDate' => '2026-01-01',
            'customFields' => [
                'custom_product_oai_resource_type' => 'am',
                'custom_product_oai_access' => 'b',
            ],
        ]);

        self::assertSame('red', $report['status']);
        self::assertNotEmpty($report['errors']);
        self::assertStringContainsString('MARC 856', implode(' | ', $report['errors']));
    }


    public function testIssueWithoutLinkingIdentifierIsRed(): void
    {
        $validator = new MarcXmlPreflightValidator();
        $report = $validator->validate([
            'identifier' => '019abc',
            'title' => 'Heft ohne Linking-ID',
            'productNumber' => 'SW-1001',
            'releaseDate' => '2026-02-01',
            'customFields' => [
                'custom_product_oai_resource_type' => 'ab',
                'custom_product_oai_issue' => '5',
                'custom_product_oai_access' => 'b',
            ],
        ]);

        self::assertSame('red', $report['status']);
        self::assertStringContainsString('773$o', implode(' | ', $report['errors']));
    }


    public function testMissingRecommendedLicenseProducesYellow(): void
    {
        $validator = new MarcXmlPreflightValidator();
        $report = $validator->validate([
            'identifier' => '019abc',
            'title' => 'Monografie ohne Lizenz',
            'productNumber' => 'SW-1002',
            'releaseDate' => '2026-02-01',
            'url' => 'https://example.org/doc.pdf',
            'categoryIds' => ['Innovation'],
            'customFields' => [
                'custom_product_oai_resource_type' => 'am',
                'custom_product_oai_access' => 'b',
            ],
        ]);

        self::assertSame('yellow', $report['status']);
        self::assertSame([], $report['errors']);
        self::assertNotEmpty($report['warnings']);
        self::assertStringContainsString('MARC 506', implode(' | ', $report['warnings']));
    }
}

