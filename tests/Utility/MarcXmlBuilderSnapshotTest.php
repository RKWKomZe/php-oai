<?php

declare(strict_types=1);

namespace RKW\OaiConnector\Tests\Utility;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RKW\OaiConnector\Utility\MarcXmlBuilder;

final class MarcXmlBuilderSnapshotTest extends TestCase
{

    #[DataProvider('snapshotCases')]
    public function testMarcXmlMatchesSnapshot(array $record, string $fixtureName): void
    {
        $builder = new MarcXmlBuilder();
        $actualXml = $builder->renderRecord($record);

        $actual = $this->normalizeForSnapshot($actualXml);
        $fixturePath = __DIR__ . '/../Fixtures/marcxml/' . $fixtureName;
        $expected = trim((string)file_get_contents($fixturePath));

        self::assertSame($expected, $actual);
    }


    public static function snapshotCases(): array
    {
        return [
            'monograph' => [
                [
                    'identifier' => 'mono-1',
                    'title' => 'Einzelpublikation Test',
                    'description' => 'Beschreibung Monografie',
                    'releaseDate' => '2026-01-15',
                    'productNumber' => 'SW-MONO-1',
                    'categoryIds' => ['Innovation', 'Mittelstand'],
                    'manufacturer' => [
                        'translated' => [
                            'name' => 'RKW Kompetenzzentrum',
                            'customFields' => ['custom_manufacturer_oai_place' => 'Eschborn'],
                        ],
                    ],
                    'customFields' => [
                        'custom_product_oai_resource_type' => 'am',
                        'custom_product_oai_access' => 'b',
                        'custom_product_oai_language' => 'de',
                        'custom_product_oai_license' => 'open-access',
                    ],
                    'properties' => [],
                ],
                'monograph.xml',
            ],
            'issue' => [
                [
                    'identifier' => 'issue-1',
                    'title' => 'RKW Magazin Ausgabe',
                    'description' => 'Beschreibung Heft',
                    'releaseDate' => '2026-02-20',
                    'productNumber' => 'SW-ISSUE-1',
                    'categoryIds' => ['Fachkräfte', 'Transformation'],
                    'manufacturer' => [
                        'translated' => [
                            'name' => 'RKW Kompetenzzentrum',
                            'customFields' => ['custom_manufacturer_oai_place' => 'Eschborn'],
                        ],
                    ],
                    'customFields' => [
                        'custom_product_oai_resource_type' => 'ab',
                        'custom_product_oai_publication_type' => 'issue',
                        'custom_product_oai_identifier_journal' => 'RKW Magazin',
                        'custom_product_oai_issue' => '5',
                        'custom_product_oai_access' => 'b',
                        'custom_product_oai_language' => 'de',
                        'custom_product_oai_license' => 'open-access',
                    ],
                    'properties' => [],
                ],
                'issue.xml',
            ],
            'article' => [
                [
                    'identifier' => 'article-1',
                    'title' => 'Artikel in Ausgabe',
                    'description' => 'Artikelbeschreibung',
                    'releaseDate' => '2026-03-10',
                    'productNumber' => 'SW-ARTICLE-1',
                    'categoryIds' => ['Wissen'],
                    'manufacturer' => [
                        'translated' => [
                            'name' => 'RKW Kompetenzzentrum',
                            'customFields' => ['custom_manufacturer_oai_place' => 'Eschborn'],
                        ],
                    ],
                    'customFields' => [
                        'custom_product_oai_resource_type' => 'aa',
                        'custom_product_oai_publication_type' => 'article',
                        'custom_product_oai_identifier_journal' => 'RKW-Magazin',
                        'custom_product_oai_issue' => '3',
                        'custom_product_oai_access' => 'b',
                        'custom_product_oai_language' => 'de',
                        'custom_product_oai_license' => 'open-access',
                    ],
                    'properties' => [],
                ],
                'article.xml',
            ],
        ];
    }


    private function normalizeForSnapshot(string $xml): string
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('m', 'http://www.loc.gov/MARC21/slim');

        $nodes = $xpath->query('/m:record/m:controlfield[@tag="008"]');
        if ($nodes !== false && $nodes->length > 0) {
            $value = $nodes->item(0)->nodeValue ?? '';
            if (strlen($value) >= 6) {
                $nodes->item(0)->nodeValue = '000000' . substr($value, 6);
            }
        }

        return trim((string)$doc->C14N());
    }
}

