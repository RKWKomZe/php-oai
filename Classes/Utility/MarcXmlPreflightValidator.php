<?php

declare(strict_types=1);

namespace RKW\OaiConnector\Utility;

/**
 * Preflight validation for MARCXML export readiness.
 * Returns structured error/warning lists so callers can log and abort safely.
 */
final class MarcXmlPreflightValidator
{

    /**
     * @param array $record
     * @return array{
     *   errors:array<int,string>,
     *   warnings:array<int,string>,
     *   status:string,
     *   summary:array{must_total:int,must_failed:int,should_total:int,should_failed:int},
     *   checklist:array{
     *     must:array<int,array{id:string,ok:bool,message:string}>,
     *     should:array<int,array{id:string,ok:bool,message:string}>
     *   }
     * }
     */
    public function validate(array $record): array
    {
        $customFields = is_array($record['customFields'] ?? null) ? $record['customFields'] : [];
        $resourceType = strtolower(trim((string)($customFields['custom_product_oai_resource_type'] ?? '')));
        $publicationType = strtolower(trim((string)($customFields['custom_product_oai_publication_type'] ?? '')));

        $title = trim((string)($record['title'] ?? ''));
        $identifier = trim((string)($record['identifier'] ?? ''));
        $productNumber = trim((string)($record['productNumber'] ?? ''));
        $releaseDate = trim((string)($record['releaseDate'] ?? ''));
        $access = trim((string)($customFields['custom_product_oai_access'] ?? ''));
        $journalLinkingId = trim((string)($customFields['custom_product_oai_identifier_journal'] ?? ''));
        $issue = trim((string)($customFields['custom_product_oai_issue'] ?? ''));
        $license = trim((string)($customFields['custom_product_oai_license'] ?? ''));
        $keywords = (array)($record['categoryIds'] ?? []);

        $isIssue = $resourceType === 'ab' || $publicationType === 'issue';
        $isArticle = $publicationType === 'article' || $resourceType === 'aa';
        $isSerialChild = $isIssue || $isArticle;

        $mustChecks = [];
        $shouldChecks = [];

        $mustChecks[] = $this->check(
            'identifier-present',
            $identifier !== '',
            'Identifier (MARC/OAI) must be present.'
        );
        $mustChecks[] = $this->check(
            'title-present',
            $title !== '',
            'Title should be present for stable cataloging.'
        );
        $mustChecks[] = $this->check(
            'resource-type-supported',
            in_array($resourceType, ['am', 'ab', 'aa'], true),
            'custom_product_oai_resource_type must be am/ab/aa for leader + 856.'
        );
        $mustChecks[] = $this->check(
            'transfer-url-prerequisites',
            $productNumber !== '' && in_array($resourceType, ['am', 'ab'], true),
            'MARC 856 requires productNumber and supported resource type (am/ab).'
        );
        $mustChecks[] = $this->check(
            'access-093',
            $access === '' || in_array($access, ['a', 'b'], true),
            'MARC 093 access code must be "a" or "b" (empty falls back to "b").'
        );
        if ($isSerialChild) {
            $mustChecks[] = $this->check(
                'serial-linking-id',
                $journalLinkingId !== '',
                'Serial issues/articles require stable MARC 773$o linking identifier.'
            );
            $mustChecks[] = $this->check(
                'serial-enumeration',
                $issue !== '' || $releaseDate !== '',
                'Serial issues/articles require at least one enumeration component for MARC 773$g.'
            );
        }

        $shouldChecks[] = $this->check(
            'release-date-present',
            $releaseDate !== '',
            'releaseDate is recommended to avoid fallback year in MARC 008/773.'
        );
        $shouldChecks[] = $this->check(
            'serial-linking-id-format',
            $journalLinkingId === '' || (bool)preg_match('/^[A-Za-z0-9._-]+$/', str_replace(' ', '-', $journalLinkingId)),
            'Linking identifier should be stable and without spaces/special characters.'
        );
        $shouldChecks[] = $this->check(
            'license-506-present',
            $license !== '',
            'MARC 506 license statement is recommended.'
        );
        $shouldChecks[] = $this->check(
            'keywords-653-human-readable',
            $this->looksHumanReadableKeywordSet($keywords),
            'MARC 653 should contain human-readable keywords instead of internal IDs.'
        );
        $shouldChecks[] = $this->check(
            'transfer-url-pdf-like',
            $resourceType !== 'am' || $this->looksLikePdfTransferUrlSource($customFields, $record),
            'Transfer URL should ideally point to a directly harvestable PDF.'
        );

        $errors = [];
        foreach ($mustChecks as $check) {
            if (!$check['ok']) {
                $errors[] = $check['message'];
            }
        }
        $warnings = [];
        foreach ($shouldChecks as $check) {
            if (!$check['ok']) {
                $warnings[] = $check['message'];
            }
        }

        $mustFailed = count($errors);
        $shouldFailed = count($warnings);
        $status = $mustFailed > 0 ? 'red' : ($shouldFailed > 0 ? 'yellow' : 'green');

        return [
            'errors' => $errors,
            'warnings' => $warnings,
            'status' => $status,
            'summary' => [
                'must_total' => count($mustChecks),
                'must_failed' => $mustFailed,
                'should_total' => count($shouldChecks),
                'should_failed' => $shouldFailed,
            ],
            'checklist' => [
                'must' => $mustChecks,
                'should' => $shouldChecks,
            ],
        ];
    }


    /**
     * @param string $id
     * @param bool $ok
     * @param string $message
     * @return array{id:string,ok:bool,message:string}
     */
    private function check(string $id, bool $ok, string $message): array
    {
        return [
            'id' => $id,
            'ok' => $ok,
            'message' => $message,
        ];
    }


    /**
     * @param array $keywords
     * @return bool
     */
    private function looksHumanReadableKeywordSet(array $keywords): bool
    {
        if ($keywords === []) {
            return false;
        }
        foreach ($keywords as $keyword) {
            $value = trim((string)$keyword);
            if ($value === '') {
                continue;
            }
            if ((bool)preg_match('/[A-Za-zÄÖÜäöü]/u', $value) && strlen($value) > 2) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param array $customFields
     * @param array $record
     * @return bool
     */
    private function looksLikePdfTransferUrlSource(array $customFields, array $record): bool
    {
        $direct = trim((string)($customFields['custom_product_oai_transfer_url'] ?? ''));
        if ($direct !== '') {
            return (bool)preg_match('/\.pdf($|[?#])/i', $direct);
        }

        $url = trim((string)($record['url'] ?? ''));
        return $url !== '' && (bool)preg_match('/\.pdf($|[?#])/i', $url);
    }
}
