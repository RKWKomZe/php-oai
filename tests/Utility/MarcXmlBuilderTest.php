<?php

declare(strict_types=1);

namespace RKW\OaiConnector\Tests\Utility;

use PHPUnit\Framework\TestCase;
use RKW\OaiConnector\Utility\MarcXmlBuilder;

final class MarcXmlBuilderTest extends TestCase
{

    public function testIssueRecordContainsMandatoryDnbFields(): void
    {
        $builder = new MarcXmlBuilder();
        $xml = $builder->renderRecord([
            'identifier' => 'test-id',
            'title' => 'RKW Magazin Ausgabe',
            'description' => 'Beschreibung',
            'releaseDate' => '2026-02-20',
            'productNumber' => 'SW10000',
            'categoryIds' => ['Fachkräfte', 'Transformation'],
            'manufacturer' => [
                'translated' => [
                    'name' => 'RKW Kompetenzzentrum',
                    'customFields' => [
                        'custom_manufacturer_oai_place' => 'Eschborn',
                    ],
                ],
            ],
            'customFields' => [
                'custom_product_oai_resource_type' => 'ab',
                'custom_product_oai_publication_type' => 'issue',
                'custom_product_oai_identifier_journal' => 'RKW Magazin',
                'custom_product_oai_issue' => '5',
                'custom_product_oai_access' => 'b',
                'custom_product_oai_language' => 'de',
            ],
            'properties' => [],
        ]);

        $doc = new \DOMDocument();
        self::assertTrue($doc->loadXML($xml));
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('m', 'http://www.loc.gov/MARC21/slim');

        $leader = $xpath->evaluate('string(/m:record/m:leader)');
        self::assertStringContainsString('nab', $leader);
        self::assertSame(0, (int)$xpath->evaluate('count(/m:record/m:datafield[@tag="264"])'));

        self::assertSame('cr|||||', $xpath->evaluate('string(/m:record/m:controlfield[@tag="007"])'));
        self::assertSame(40, strlen($xpath->evaluate('string(/m:record/m:controlfield[@tag="008"])')));

        self::assertSame('Transfer-URL', $xpath->evaluate('string(/m:record/m:datafield[@tag="856"]/m:subfield[@code="x"])'));
        self::assertSame('b', $xpath->evaluate('string(/m:record/m:datafield[@tag="093"]/m:subfield[@code="b"])'));

        self::assertSame('RKW-Magazin', $xpath->evaluate('string(/m:record/m:datafield[@tag="773"][@ind2="8"]/m:subfield[@code="o"])'));
        self::assertGreaterThan(0, (int)$xpath->evaluate('count(/m:record/m:datafield[@tag="653"]/m:subfield[@code="a"])'));
    }


    public function testMonographLeaderIsNamAndHasNoIssueLinkField(): void
    {
        $builder = new MarcXmlBuilder();
        $xml = $builder->renderRecord([
            'identifier' => 'mono-id',
            'title' => 'Einzelpublikation',
            'releaseDate' => '2026-01-01',
            'description' => 'Text',
            'productNumber' => 'SW20000',
            'categoryIds' => ['Wissen'],
            'manufacturer' => [
                'translated' => [
                    'name' => 'RKW Kompetenzzentrum',
                    'customFields' => [
                        'custom_manufacturer_oai_place' => 'Eschborn',
                    ],
                ],
            ],
            'customFields' => [
                'custom_product_oai_resource_type' => 'am',
                'custom_product_oai_access' => 'b',
                'custom_product_oai_language' => 'de',
                'custom_product_oai_license' => 'open-access',
            ],
            'properties' => [],
        ]);

        $doc = new \DOMDocument();
        self::assertTrue($doc->loadXML($xml));
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('m', 'http://www.loc.gov/MARC21/slim');

        $leader = $xpath->evaluate('string(/m:record/m:leader)');
        self::assertStringContainsString('nam', $leader);
        self::assertSame('2026', $xpath->evaluate('string(/m:record/m:datafield[@tag="264"]/m:subfield[@code="c"])'));
        self::assertSame('open-access', $xpath->evaluate('string(/m:record/m:datafield[@tag="506"]/m:subfield[@code="a"])'));
        self::assertSame(0, (int)$xpath->evaluate('count(/m:record/m:datafield[@tag="773"][@ind2="8"])'));
    }


    public function testPdfFileSizeIsRenderedAsMarc300(): void
    {
        $builder = new MarcXmlBuilder();
        $xml = $builder->renderRecord([
            'identifier' => 'mono-id',
            'title' => 'Einzelpublikation',
            'releaseDate' => '2026-01-01',
            'description' => 'Text',
            'productNumber' => 'SW20000',
            'categoryIds' => ['Wissen'],
            'manufacturer' => [
                'translated' => [
                    'name' => 'RKW Kompetenzzentrum',
                    'customFields' => [
                        'custom_manufacturer_oai_place' => 'Eschborn',
                    ],
                ],
            ],
            'customFields' => [
                'custom_product_oai_resource_type' => 'am',
                'custom_product_oai_access' => 'b',
                'custom_product_oai_language' => 'de',
            ],
            'pdfDownload' => [
                'mimeType' => 'application/pdf',
                'fileExtension' => 'pdf',
                'fileSize' => 8266112,
            ],
            'properties' => [],
        ]);

        $doc = new \DOMDocument();
        self::assertTrue($doc->loadXML($xml));
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('m', 'http://www.loc.gov/MARC21/slim');

        self::assertSame(
            '1 Online-Ressource (8,3 MB)',
            $xpath->evaluate('string(/m:record/m:datafield[@tag="300"]/m:subfield[@code="a"])')
        );
        self::assertSame(
            'pdf',
            $xpath->evaluate('string(/m:record/m:datafield[@tag="856"]/m:subfield[@code="q"])')
        );
        self::assertSame(
            '8266112 bytes',
            $xpath->evaluate('string(/m:record/m:datafield[@tag="856"]/m:subfield[@code="s"])')
        );
        self::assertSame(0, (int)$xpath->evaluate('count(/m:record/m:datafield[@tag="347"])'));
    }


    public function testLicense506DefaultsToOpenAccess(): void
    {
        $builder = new MarcXmlBuilder();
        $xml = $builder->renderRecord([
            'identifier' => 'default-license-id',
            'title' => 'Publikation ohne Lizenzfeld',
            'releaseDate' => '2026-01-01',
            'description' => 'Text',
            'productNumber' => 'SW30000',
            'customFields' => [
                'custom_product_oai_resource_type' => 'am',
                'custom_product_oai_access' => 'b',
                'custom_product_oai_language' => 'de',
            ],
            'properties' => [],
        ]);

        $doc = new \DOMDocument();
        self::assertTrue($doc->loadXML($xml));
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('m', 'http://www.loc.gov/MARC21/slim');

        self::assertSame(
            'open-access',
            $xpath->evaluate('string(/m:record/m:datafield[@tag="506"]/m:subfield[@code="a"])')
        );
    }
}
