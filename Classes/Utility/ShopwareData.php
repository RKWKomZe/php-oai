<?php

namespace RKW\OaiConnector\Utility;

use Symfony\Component\VarDumper\VarDumper;

/**
 * ShopwareData
 *
 * some data handling for Shopware record arrays from API
 *
 */
class ShopwareData
{
    /**
     * Transforms a product array into a standardized format.
     *
     * @param array $product The product data to transform.
     * @return array The transformed product data.
     */
    public static function transformProduct(array $product): array
    {

        $title = $product['translated']['name'] ?? $product['name'] ?? 'Kein Titel';

        $description = $product['translated']['description'] ?? '';
        //       $createdAt = $product['createdAt'] ?? date('Y-m-d');
        $updatedAt = $product['updatedAt'] ?? date('Y-m-d');

        return [
            'identifier' => $product['id'],
            'datestamp' => $updatedAt,
            'title' => trim($title),
            'description' => $description,
            'productNumber' => $product['productNumber'] ?? '',
            'releaseDate' => $product['releaseDate'] ?? '',
            'categories' => $product['categories'] ?? [],
            'customFields' => $product['customFields'] ?? [],
            'properties' => $product['properties'] ?? [],

        ];

    }


    /**
     * Build all OAI identifiers for a given repo from a Shopware items array.
     *
     * @param array $items
     * @param string $repoIdentifier
     * @return array
     */
    public static function buildOaiIdentifiersFromItems(array $items, string $repoIdentifier): array
    {
        $rawIds = self::extractUniqueIdentifiers($items);

        $oaiIdentifiers = [];

        foreach ($rawIds as $id) {
            $oaiIdentifiers[] = self::buildOaiIdentifier($repoIdentifier, $id);
        }

        return $oaiIdentifiers;
    }



    /**
     * Build a lookup pool of source IDs (e.g. Shopware IDs) from OAI meta rows.
     *
     * Result is an associative array like:
     * [
     *     '0198a8cebc6b7ce4b859c88da66a0730' => "2025-11-26 09:37:37",
     *     '...' => "2025-09-14 10:07:01",
     * ]
     *
     * @param array $rows
     * @return array<string,bool>
     */
    public static function buildSourceIdPoolFromMetaRows(array $rows): array
    {
        $pool = [];

        foreach ($rows as $row) {

            if (
                !isset($row['identifier'], $row['updated']) ||
                !is_string($row['identifier']) ||
                !is_string($row['updated'])
            ) {
                continue;
            }

//            $sourceId = self::extractSourceIdFromOaiIdentifier($row['identifier']);
//            if ($sourceId === null || $sourceId === '') {
//                continue;
//            }

            $pool[$row['identifier']] = $row['updated'];
        }

        return $pool;
    }



    /**
     * Compare DB datestamp (Y-m-d H:i:s, no TZ) with Shopware updatedAt (ISO 8601).
     *
     * Returns:
     *  -1 => Shopware is older than OAI datestamp
     *   0 => equal (to the second)
     *   1 => Shopware is newer than OAI datestamp
     */
    public static function compareOaiWithShopwareDate(
        string $oaiDatestamp,
        string $shopwareUpdatedAt
    ): int {
        // Treat OAI datestamp as Europe/Berlin (or whatever your server timezone is)
        $oaiDate = \DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            $oaiDatestamp,
            new \DateTimeZone('Europe/Berlin')
        );

        if (!$oaiDate) {
            throw new \RuntimeException('Invalid OAI datestamp: ' . $oaiDatestamp);
        }

        // Shopware updatedAt is ISO 8601 with timezone, PHP understands this directly
        $shopwareDate = new \DateTimeImmutable($shopwareUpdatedAt);

        // Normalize both to UTC for a fair comparison
        $oaiUtc       = $oaiDate->setTimezone(new \DateTimeZone('UTC'));
        $shopwareUtc  = $shopwareDate->setTimezone(new \DateTimeZone('UTC'));

        if ($shopwareUtc < $oaiUtc) {
            return -1;
        }
        if ($shopwareUtc > $oaiUtc) {
            return 1;
        }
        return 0;
    }



    /**
     * Extract all _uniqueIdentifier values from a Shopware API items array.
     *
     * @param array $items
     * @return array
     */
    protected static function extractUniqueIdentifiers(array $items): array
    {
        $identifiers = [];

        foreach ($items as $item) {
            // Ensure the key exists and is a non-empty string
            if (isset($item['_uniqueIdentifier']) && is_string($item['_uniqueIdentifier'])) {
                $identifiers[] = $item['_uniqueIdentifier'];
            }
        }

        return $identifiers;
    }



    /**
     * Build a single OAI identifier from repo identifier and source ID.
     *
     * Example: oai:maxtest:0198a8cebc6b7ce4b859c88da66a0730
     *
     * @param string $repoIdentifier
     * @param string $sourceId
     * @return string
     */
    protected static function buildOaiIdentifier(string $repoIdentifier, string $sourceId): string
    {
        return sprintf('oai:%s:%s', $repoIdentifier, $sourceId);
    }



    /**
     * Parse an OAI identifier like "oai:repo:sourceId" into its components.
     *
     * @param string $oaiIdentifier
     * @return array{prefix: string, repo: string, sourceId: string}|null
     */
    protected static function parseOaiIdentifier(string $oaiIdentifier): ?array
    {
        // Expected format: oai:<repoIdentifier>:<sourceId>
        $parts = explode(':', $oaiIdentifier, 3);

        if (count($parts) !== 3) {
            return null;
        }

        [$prefix, $repo, $sourceId] = $parts;

        if ($prefix !== 'oai' || $repo === '' || $sourceId === '') {
            return null;
        }

        return [
            'prefix'  => $prefix,
            'repo'    => $repo,
            'sourceId'=> $sourceId,
        ];
    }



    /**
     * Extract the source ID from an OAI identifier like "oai:repo:sourceId".
     *
     * @param string $oaiIdentifier
     * @return string|null
     */
    protected static function extractSourceIdFromOaiIdentifier(string $oaiIdentifier): ?string
    {
        $parsed = self::parseOaiIdentifier($oaiIdentifier);

        return $parsed['sourceId'] ?? null;
    }
}
