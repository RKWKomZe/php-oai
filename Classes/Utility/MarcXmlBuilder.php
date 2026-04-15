<?php

declare(strict_types=1);

namespace RKW\OaiConnector\Utility;


class MarcXmlBuilder
{

    /**
     * @var \DOMDocument
     */
    private \DOMDocument $doc;


    /**
     * @var \DOMElement
     */
    private \DOMElement $record;


    /**
     * @var string
     */
    private string $downloadBaseUrl;

    /**
     * the shopware property ID which identifies the authors storage
     */
    private const SHOPWARE_AUTHOR_GROUP_ID = '0198a8a44b63714f8d1bacedca03392b';


    /**
     * constructor
     */
    public function __construct()
    {

        $config = ConfigLoader::load();

        $this->downloadBaseUrl = $config['typo3']['downloadBaseUrl'];
    }


    /**
     * Render MARCXML record from Shopware product data.
     *
     * @param array $f
     * @return string
     * @throws \DOMException
     */
    public function renderRecord(array $f): string
    {
        $this->initDocument();

        // Core control/leader
        $this->addLeader($f);
        $this->addControl007();
        $this->addControl008($f);

        // Descriptive fields
        if (!$this->isMagazineIssueType($f)) {
            $this->addCreators100And700($f);
        }
        $this->addTitle245($f);
        if (!$this->isMagazineIssueType($f)) {
            $this->addPublisherAndPlaceAndYear264($f);
        }
        $this->addIsbnIssnAndProductNumber($f);
        $this->addPhysicalDescription300($f);
        $this->addAccess093($f);
        $this->addLicense506($f);
        $this->addAbstract520($f);
        $this->addLanguage041($f);

        // Relations and URLs
        $this->addElectronicLocation856($f);
        $this->addIssueEnumeration773($f);
        $this->addIssueLink773($f);
        $this->addBundleRelations($f);
        $this->addCategories690($f);

        return $this->doc->saveXML($this->record);
    }



    /**
     * @return void
     * @throws \DOMException
     */
    protected function initDocument(): void
    {
        $this->doc = new \DOMDocument('1.0', 'UTF-8');
        $this->doc->formatOutput = false;

        $this->record = $this->doc->createElementNS(
            'http://www.loc.gov/MARC21/slim',
            'record'
        );
        $this->doc->appendChild($this->record);
    }


    /**
     * @return void
     * @throws \DOMException
     */
    protected function addLeader(array $f): void
    {
        $publicationCode = $this->resolveLeaderTypeCode($f);
        $leader = $this->doc->createElement('leader', '00000' . $publicationCode . ' a2200000uc 4500');
        $this->record->appendChild($leader);
    }


    /**
     * MARC control field 007 is mandatory for DNB import.
     *
     * @throws \DOMException
     */
    protected function addControl007(): void
    {
        $cf007 = $this->doc->createElement('controlfield', 'cr|||||');
        $cf007->setAttribute('tag', '007');
        $this->record->appendChild($cf007);
    }


    /**
     * MARC control field 008 has fixed positions (40 chars).
     *
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addControl008(array $f): void
    {
        $enteredOnFile = (new \DateTimeImmutable())->format('ymd');
        $year = $this->extractPublicationYear($f);
        $language = $this->resolvePrimaryLanguageCode($f);

        // 40 chars: date entered + publication status + year + fixed defaults + language
        $value = sprintf(
            '%ss%s    gw u||p|o ||| 0||||1%s  ',
            $enteredOnFile,
            $year,
            $language
        );

        $value = str_pad(substr($value, 0, 40), 40, ' ');

        $cf008 = $this->doc->createElement('controlfield', $value);
        $cf008->setAttribute('tag', '008');
        $this->record->appendChild($cf008);
    }


    // ---------------------------------------------------------------------
    // 245 Title
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addTitle245(array $f): void
    {
        $title    = trim($f['title'] ?? '');
        $subtitle = trim($f['customFields']['custom_meta_subheader'] ?? '');

        if ($title === '') {
            return;
        }

        $hasMainEntry1XX = false;
        $ind1 = $hasMainEntry1XX ? '1' : '0';
        $ind2 = (string)$this->computeNonFilingChars($title);

        $df245 = $this->doc->createElement('datafield');
        $df245->setAttribute('tag', '245');
        $df245->setAttribute('ind1', $ind1);
        $df245->setAttribute('ind2', $ind2);

        // Clean up trailing ISBD-style punctuation from main title
        // (slash, colon, semicolon, spaces)
        $aText = rtrim($title, " /:;");

        $sfA = $this->doc->createElement('subfield', $aText);
        $sfA->setAttribute('code', 'a');
        $df245->appendChild($sfA);

        if ($subtitle !== '') {
            // Avoid leading/trailing punctuation artifacts in subtitle
            $bText = trim($subtitle, " /:;");

            $sfB = $this->doc->createElement('subfield', $bText);
            $sfB->setAttribute('code', 'b');
            $df245->appendChild($sfB);
        }

        $this->record->appendChild($df245);
    }


    /**
     * @param string $t
     * @return int
     */
    private function computeNonFilingChars(string $t): int
    {
        $articles = [
            "der ", "die ", "das ", "ein ", "eine ", "einen ", "dem ", "den ", "des ",
            "the ", "a ", "an ",
            "le ", "la ", "les ", "l'",
            "el ", "los ", "las ",
            "il ", "lo ", "gli ", "i ",
            "de ", "het ", "den ", "det ",
        ];

        $lt = mb_strtolower($t, 'UTF-8');
        foreach ($articles as $art) {
            if (mb_substr($lt, 0, mb_strlen($art, 'UTF-8'), 'UTF-8') === $art) {
                return min(mb_strlen($art, 'UTF-8'), 9);
            }
        }

        return 0;
    }


    // ---------------------------------------------------------------------
    // 300 Physical description / extent
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addPhysicalDescription300(array $f): void
    {
        $extent = $this->resolvePhysicalDescriptionExtent($f);
        if ($extent === '') {
            return;
        }

        $df300 = $this->doc->createElement('datafield');
        $df300->setAttribute('tag', '300');
        $df300->setAttribute('ind1', ' ');
        $df300->setAttribute('ind2', ' ');

        $sfA = $this->doc->createElement('subfield', $extent);
        $sfA->setAttribute('code', 'a');
        $df300->appendChild($sfA);

        $this->record->appendChild($df300);
    }


    /**
     * @param array $f
     * @return string
     */
    private function resolvePhysicalDescriptionExtent(array $f): string
    {
        $customFields = $f['customFields'] ?? [];

        $pages = trim((string)($customFields['custom_product_oai_pages'] ?? ''));
        if ($pages !== '') {
            return preg_match('/^\d+$/', $pages) ? $pages . ' Seiten' : $this->sanitizeText($pages);
        }

        $fileSize = (int)($f['pdfDownload']['fileSize'] ?? 0);
        if ($fileSize <= 0) {
            return '';
        }

        return '1 Online-Ressource (' . $this->formatFileSize($fileSize) . ')';
    }


    /**
     * @param int $bytes
     * @return string
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1000000) {
            $value = round($bytes / 1000000, 1);
            return str_replace('.', ',', (string)$value) . ' MB';
        }

        if ($bytes >= 1000) {
            $value = round($bytes / 1000, 1);
            return str_replace('.', ',', (string)$value) . ' KB';
        }

        return $bytes . ' Bytes';
    }


    // ---------------------------------------------------------------------
    // 100/700: Creators (authors) from Shopware properties
    // ---------------------------------------------------------------------

    /**
     * Add 100/700 fields based on Shopware property group "Autorenschaft".
     *
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addCreators100And700(array $f): void
    {
        $properties = $f['properties'] ?? null;

        if (!is_array($properties) || $properties === []) {
            return;
        }

        $authors = [];

        foreach ($properties as $property) {
            if (!is_array($property)) {
                continue;
            }

            // Determine group id either from "groupId" or nested "group.id"
            $groupId = $property['groupId'] ?? ($property['group']['id'] ?? null);
            if ($groupId !== self::SHOPWARE_AUTHOR_GROUP_ID) {
                continue;
            }

            // Prefer translated name, fallback to raw name
            $name = $property['translated']['name'] ?? $property['name'] ?? '';
            $name = trim((string)$name);

            if ($name !== '' && !in_array($name, $authors, true)) {
                $authors[] = $name;
            }
        }

        if ($authors === []) {
            return;
        }

        // First author goes into 100 (Main Entry - Personal Name)
        $df100 = $this->doc->createElement('datafield');
        $df100->setAttribute('tag', '100');
        // Indicator 1 = 1: surname entry, matches "Heitzer-Priem, Ulrike"
        $df100->setAttribute('ind1', '1');
        $df100->setAttribute('ind2', ' ');

        // 100 $a – personal name
        $sfA = $this->doc->createElement('subfield', $authors[0]);
        $sfA->setAttribute('code', 'a');
        $df100->appendChild($sfA);

        // 100 $4 – relator code ("aut" = author)
        $sf4 = $this->doc->createElement('subfield', 'aut');
        $sf4->setAttribute('code', '4');
        $df100->appendChild($sf4);

        $this->record->appendChild($df100);

        // Additional authors (if any) as 700 fields
        if (count($authors) > 1) {
            foreach (array_slice($authors, 1) as $authorName) {
                $authorName = trim((string)$authorName);
                if ($authorName === '') {
                    continue;
                }

                $df700 = $this->doc->createElement('datafield');
                $df700->setAttribute('tag', '700');
                $df700->setAttribute('ind1', '1');
                $df700->setAttribute('ind2', ' ');

                // 700 $a – personal name
                $sfA = $this->doc->createElement('subfield', $authorName);
                $sfA->setAttribute('code', 'a');
                $df700->appendChild($sfA);

                // 700 $4 – relator code ("aut" = author)
                $sf4 = $this->doc->createElement('subfield', 'aut');
                $sf4->setAttribute('code', '4');
                $df700->appendChild($sf4);

                $this->record->appendChild($df700);
            }
        }
    }



    // ---------------------------------------------------------------------
    // 020/022/024: ISBN/ISSN/productNumber
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addIsbnIssnAndProductNumber(array $f): void
    {
        $customFields  = $f['customFields'] ?? [];
        $value         = $customFields['custom_product_oai_issn_isbn'] ?? '';
        $productNumber = $f['productNumber'] ?? '';

        if (!empty($value)) {
            if (preg_match('/^\d{9}[\dXx]$/', $value) || preg_match('/^\d{13}$/', $value)) {
                $df020 = $this->doc->createElement('datafield');
                $df020->setAttribute('tag', '020');
                $df020->setAttribute('ind1', ' ');
                $df020->setAttribute('ind2', ' ');
                $sfA = $this->doc->createElement('subfield', $value);
                $sfA->setAttribute('code', 'a');
                $df020->appendChild($sfA);
                $this->record->appendChild($df020);
            } elseif (preg_match('/^\d{4}-\d{3}[\dxX]$/', $value)) {
                $df022 = $this->doc->createElement('datafield');
                $df022->setAttribute('tag', '022');
                $df022->setAttribute('ind1', ' ');
                $df022->setAttribute('ind2', ' ');
                $sfA = $this->doc->createElement('subfield', $value);
                $sfA->setAttribute('code', 'a');
                $df022->appendChild($sfA);
                $this->record->appendChild($df022);
            }
        }

        if (!empty($productNumber)) {
            $df024 = $this->doc->createElement('datafield');
            $df024->setAttribute('tag', '024');
            $df024->setAttribute('ind1', '8');
            $df024->setAttribute('ind2', ' ');
            $sfA = $this->doc->createElement('subfield', $productNumber);
            $sfA->setAttribute('code', 'a');
            $df024->appendChild($sfA);
            $this->record->appendChild($df024);
        }
    }



    // ---------------------------------------------------------------------
    // Historical publication identifier combination
    // 264: place + publisher + year
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addPublisherAndPlaceAndYear264(array $f): void
    {
        $publisher = trim($f['manufacturer']['translated']['name'] ?? '');
        $place     = trim($f['manufacturer']['translated']['customFields']['custom_manufacturer_oai_place'] ?? '');
        $yearRaw   = trim($f['releaseDate'] ?? '');
        $year      = substr($yearRaw, 0, 4);

        if ($publisher === '') {
            $publisher = 'RKW Kompetenzzentrum';
        }
        if ($place === '') {
            $place = 'Eschborn';
        }

        // If all empty → no field
        if ($publisher === '' && $place === '' && !ctype_digit($year)) {
            return;
        }

        $df264 = $this->doc->createElement('datafield');
        $df264->setAttribute('tag', '264');
        $df264->setAttribute('ind1', ' ');
        $df264->setAttribute('ind2', '1'); // publication

        if ($place !== '') {
            $sfA = $this->doc->createElement('subfield', $place);
            $sfA->setAttribute('code', 'a');
            $df264->appendChild($sfA);
        }
        if ($publisher !== '') {
            $sfB = $this->doc->createElement('subfield', $publisher);
            $sfB->setAttribute('code', 'b');
            $df264->appendChild($sfB);
        }
        if (ctype_digit($year)) {
            $sfC = $this->doc->createElement('subfield', $year);
            $sfC->setAttribute('code', 'c');
            $df264->appendChild($sfC);
        }

        $this->record->appendChild($df264);
    }


    // ---------------------------------------------------------------------
    // 506 Access
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addAccess093(array $f): void
    {
        $access = trim($f['customFields']['custom_product_oai_access'] ?? '');
        if (!in_array($access, ['a', 'b'], true)) {
            $access = 'b'; // default unrestricted archive access
        }

        $df093 = $this->doc->createElement('datafield');
        $df093->setAttribute('tag', '093');
        $df093->setAttribute('ind1', ' ');
        $df093->setAttribute('ind2', ' ');

        $sfB = $this->doc->createElement('subfield', $access);
        $sfB->setAttribute('code', 'b');
        $df093->appendChild($sfB);

        $this->record->appendChild($df093);
    }


    /**
     * Optional license hint for the original object.
     *
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addLicense506(array $f): void
    {
        $license = trim((string)($f['customFields']['custom_product_oai_license'] ?? 'open-access'));
        if ($license === '') {
            return;
        }

        $df506 = $this->doc->createElement('datafield');
        $df506->setAttribute('tag', '506');
        $df506->setAttribute('ind1', '0');
        $df506->setAttribute('ind2', ' ');

        $sfA = $this->doc->createElement('subfield', $license);
        $sfA->setAttribute('code', 'a');
        $df506->appendChild($sfA);

        $this->record->appendChild($df506);
    }


    // ---------------------------------------------------------------------
    // 520 Abstract
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addAbstract520(array $f): void
    {
        $customFields = $f['customFields'] ?? [];
        $description  = ($customFields['custom_meta_covertext'] ?? '') ?: ($f['description'] ?? '');

        $description = $this->sanitizeText($description);
        if ($description === '') {
            return;
        }

        // --- Clean description ---
        // Remove HTML tags and trim the result
        // (MARCXML requires plain text; HTML would break validation)
        $cleanDescription = trim(strip_tags($description));

        // Escape for XML safety
        $cleanDescription = htmlspecialchars($cleanDescription);

        $df520 = $this->doc->createElement('datafield');
        $df520->setAttribute('tag', '520');
        $df520->setAttribute('ind1', ' ');
        $df520->setAttribute('ind2', ' ');

        $sfA = $this->doc->createElement('subfield', $cleanDescription);
        $sfA->setAttribute('code', 'a');
        $df520->appendChild($sfA);

        $this->record->appendChild($df520);
    }


    // ---------------------------------------------------------------------
    // 856 Electronic location (uses your resource_type logic)
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addElectronicLocation856(array $f): void
    {
        $url = $this->buildElectronicLocationUrl($f);

        if ($url === '') {
            return;
        }

        $df856 = $this->doc->createElement('datafield');
        $df856->setAttribute('tag', '856');
        $df856->setAttribute('ind1', '4');
        $df856->setAttribute('ind2', '0');

        // 856 $u – URL
        $sfU = $this->doc->createElement('subfield', $url);
        $sfU->setAttribute('code', 'u');
        $df856->appendChild($sfU);

        $fileExtension = strtolower(trim((string)($f['pdfDownload']['fileExtension'] ?? '')));
        $mimeType = strtolower(trim((string)($f['pdfDownload']['mimeType'] ?? '')));
        if ($fileExtension === 'pdf' || $mimeType === 'application/pdf') {
            $sfQ = $this->doc->createElement('subfield', 'pdf');
            $sfQ->setAttribute('code', 'q');
            $df856->appendChild($sfQ);
        }

        $fileSize = (int)($f['pdfDownload']['fileSize'] ?? 0);
        if ($fileSize > 0) {
            $sfS = $this->doc->createElement('subfield', $fileSize . ' bytes');
            $sfS->setAttribute('code', 's');
            $df856->appendChild($sfS);
        }

        $sfX = $this->doc->createElement('subfield', 'Transfer-URL');
        $sfX->setAttribute('code', 'x');
        $df856->appendChild($sfX);

        $this->record->appendChild($df856);
    }


    /**
     * @param array $f
     * @return string
     */
    private function buildElectronicLocationUrl(array $f): string
    {
        $base = $this->downloadBaseUrl ?? '';
        $base = rtrim((string)$base, '/');

        if ($base === '') {
            return '';
        }


        // @toDo: URL erstell Logik zu ShopwareData-Utility-Klass auslagern?


        $customFields  = $f['customFields'] ?? [];
        $direct = trim((string)($customFields['custom_product_oai_transfer_url'] ?? ''));
        if ($direct !== '') {
            return $direct;
        }

        $identifier = trim((string)($f['identifier'] ?? ''));
        $resourceType  = trim($customFields['custom_product_oai_resource_type'] ?? '');

        if ($resourceType === '' || $identifier === '') {
            return '';
        }

        switch ($resourceType) {
            case 'am':
            case 'ab':
                $path = '/oai/shop/download/' . rawurlencode($identifier);
                break;
            default:
                return '';
        }

        return $base . $path;
    }


    /**
     * @param string $value
     * @return string
     */
    private function slugify(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = preg_replace('/[^A-Za-z0-9]+/', '-', $value);
        $value = trim((string)$value, '-');
        $value = strtolower($value);

        return $value;
    }



    // ---------------------------------------------------------------------
    // 773 Journal ("Gesamtzeitschrift") for magazines
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addIssueLink773(array $f): void
    {
        if (!$this->isMagazineIssueType($f)) {
            return;
        }

        $customFields = $f['customFields'] ?? [];
        $linkingId = trim((string)($customFields['custom_product_oai_identifier_journal'] ?? ''));
        if ($linkingId === '') {
            return;
        }

        $linkingId = preg_replace('/[^A-Za-z0-9._-]+/', '-', $linkingId);
        $linkingId = trim((string)$linkingId, '-');
        if ($linkingId === '') {
            return;
        }

        $df773 = $this->doc->createElement('datafield');
        $df773->setAttribute('tag', '773');
        $df773->setAttribute('ind1', '1');
        $df773->setAttribute('ind2', '8');

        $sfO = $this->doc->createElement('subfield', $linkingId);
        $sfO->setAttribute('code', 'o');
        $df773->appendChild($sfO);

        $this->record->appendChild($df773);
    }


    // ---------------------------------------------------------------------
    // 362 Issue for magazines
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addIssueEnumeration773(array $f): void
    {
        if (!$this->isMagazineIssueType($f)) {
            return;
        }

        $customFields = $f['customFields'] ?? [];
        $issue        = trim((string)($customFields['custom_product_oai_issue'] ?? ''));
        $releaseDate  = trim((string)($f['releaseDate'] ?? ''));
        $year = $this->extractPublicationYear($f);

        $gValues = [];
        if ($issue !== '') {
            $gValues[] = 'number:' . $issue;
        }
        if ($year !== '0000') {
            $gValues[] = 'year:' . $year;
        }
        if ($releaseDate !== '') {
            try {
                $date = new \DateTimeImmutable($releaseDate);
                $gValues[] = 'month:' . $date->format('m');
                $gValues[] = 'day:' . $date->format('d');
            } catch (\Throwable $e) {
                // keep output stable even with invalid source values
            }
        }

        if ($gValues === []) {
            return;
        }

        $df773 = $this->doc->createElement('datafield');
        $df773->setAttribute('tag', '773');
        $df773->setAttribute('ind1', '1');
        $df773->setAttribute('ind2', ' ');

        foreach ($gValues as $gValue) {
            $sfG = $this->doc->createElement('subfield', $gValue);
            $sfG->setAttribute('code', 'g');
            $df773->appendChild($sfG);
        }

        $sf7 = $this->doc->createElement('subfield', 'nnas');
        $sf7->setAttribute('code', '7');
        $df773->appendChild($sf7);

        $this->record->appendChild($df773);
    }


    // ---------------------------------------------------------------------
    // 773/774 bundle relations
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addBundleRelations(array $f): void
    {
        $customFields = $f['customFields'] ?? [];

        $parentUuid = trim((string)($customFields['custom_bundles_parent'] ?? ''));
        if ($parentUuid !== '') {
            $df773 = $this->doc->createElement('datafield');
            $df773->setAttribute('tag', '773');
            $df773->setAttribute('ind1', ' ');
            $df773->setAttribute('ind2', ' ');
            $sfW = $this->doc->createElement('subfield', $this->toOaiId($parentUuid));
            $sfW->setAttribute('code', 'w');
            $df773->appendChild($sfW);
            $this->record->appendChild($df773);
        }

        $isBundleActive = !empty($customFields['custom_bundles_active']);
        $childrenRaw    = (string)($customFields['custom_bundles_products'] ?? '');
        $children       = $this->parseIdList($childrenRaw);

        if ($isBundleActive && !empty($children)) {
            foreach ($children as $kidUuid) {
                $df774 = $this->doc->createElement('datafield');
                $df774->setAttribute('tag', '774');
                $df774->setAttribute('ind1', ' ');
                $df774->setAttribute('ind2', ' ');
                $sfW = $this->doc->createElement('subfield', $this->toOaiId($kidUuid));
                $sfW->setAttribute('code', 'w');
                $df774->appendChild($sfW);
                $this->record->appendChild($df774);
            }
        }
    }


    /**
     * @param string|null $uuid
     * @return string
     */
    private function toOaiId(?string $uuid): string
    {
        $uuid = trim((string)$uuid);
        return $uuid !== '' ? 'oai:rkw:shopware:' . $uuid : '';
    }


    /**
     * @param string|null $raw
     * @return array
     */
    private function parseIdList(?string $raw): array
    {
        if (!$raw) {
            return [];
        }

        $parts = preg_split('/[\s,;]+/', trim($raw));
        $parts = array_filter(array_map('trim', (array)$parts), static fn($v) => $v !== '');

        return array_values(array_unique($parts));
    }


    // ---------------------------------------------------------------------
    // 690 categories
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addCategories690(array $f): void
    {
        $rawKeywords = $f['categoryNames'] ?? null;
        if (!is_array($rawKeywords) || $rawKeywords === []) {
            $rawKeywords = (array)($f['categoryIds'] ?? []);
        }

        $keywords = [];
        foreach ((array)$rawKeywords as $value) {
            $value = $this->sanitizeText((string)$value);
            if ($value !== '') {
                $keywords[] = $value;
            }
        }
        $keywords = array_values(array_unique($keywords));

        if ($keywords === []) {
            return;
        }

        $df653 = $this->doc->createElement('datafield');
        $df653->setAttribute('tag', '653');
        $df653->setAttribute('ind1', ' ');
        $df653->setAttribute('ind2', ' ');

        foreach ($keywords as $keyword) {
            $sfA = $this->doc->createElement('subfield', $keyword);
            $sfA->setAttribute('code', 'a');
            $df653->appendChild($sfA);
        }

        $this->record->appendChild($df653);
    }


    // ---------------------------------------------------------------------
    // 041 language
    // ---------------------------------------------------------------------

    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addLanguage041(array $f): void
    {
        $langRaw = $f['customFields']['custom_product_oai_language'] ?? '';

        $tokens = [];
        if (is_array($langRaw)) {
            $tokens = $langRaw;
        } elseif (is_string($langRaw)) {
            $tokens = preg_split('/[,\;\s]+/u', $langRaw, -1, PREG_SPLIT_NO_EMPTY);
        }
        $tokens = array_map(
            static fn($v) => mb_strtolower(trim((string)$v), 'UTF-8'),
            $tokens
        );

        $map2to3 = [
            'de' => 'ger', 'en' => 'eng', 'fr' => 'fre', 'es' => 'spa', 'it' => 'ita',
            'nl' => 'dut', 'cs' => 'cze', 'sk' => 'slo', 'ro' => 'rum', 'alb' => 'alb',
            'pt' => 'por', 'ru' => 'rus', 'pl' => 'pol', 'da' => 'dan', 'sv' => 'swe',
            'no' => 'nor', 'fi' => 'fin', 'hu' => 'hun', 'tr' => 'tur', 'el' => 'gre',
            'zh' => 'chi', 'ja' => 'jpn', 'ko' => 'kor', 'ar' => 'ara',
        ];

        $isThree = static fn(string $v) => (bool)preg_match('/^[a-z]{3}$/', $v);

        $langs = [];
        foreach ($tokens as $t) {
            if ($t === '') {
                continue;
            }
            if (isset($map2to3[$t])) {
                $langs[] = $map2to3[$t];
            } elseif ($isThree($t)) {
                $langs[] = $t;
            }
        }

        if (!$langs) {
            $langs = ['ger'];
        }

        $langs = array_values(array_unique($langs));

        $df041 = $this->doc->createElement('datafield');
        $df041->setAttribute('tag', '041');
        $df041->setAttribute('ind1', ' ');
        $df041->setAttribute('ind2', ' ');

        foreach ($langs as $code) {
            $sfA = $this->doc->createElement('subfield', $code);
            $sfA->setAttribute('code', 'a');
            $df041->appendChild($sfA);
        }

        $this->record->appendChild($df041);
    }


    /**
     * @param array $f
     * @return bool
     */
    private function isMagazineIssueType(array $f): bool
    {
        $customFields = $f['customFields'] ?? [];
        $resourceType = strtolower(trim((string)($customFields['custom_product_oai_resource_type'] ?? '')));
        $publicationType = strtolower(trim((string)($customFields['custom_product_oai_publication_type'] ?? '')));

        return $resourceType === 'ab' || $publicationType === 'issue';
    }


    /**
     * @param array $f
     * @return string
     */
    private function resolveLeaderTypeCode(array $f): string
    {
        $customFields = $f['customFields'] ?? [];
        $publicationType = strtolower(trim((string)($customFields['custom_product_oai_publication_type'] ?? '')));
        $resourceType = strtolower(trim((string)($customFields['custom_product_oai_resource_type'] ?? '')));

        if ($publicationType === 'article' || $resourceType === 'aa') {
            return 'naa';
        }
        if ($publicationType === 'issue' || $resourceType === 'ab') {
            return 'nab';
        }

        return 'nam';
    }


    /**
     * @param array $f
     * @return string
     */
    private function extractPublicationYear(array $f): string
    {
        $yearRaw = trim((string)($f['releaseDate'] ?? ''));
        $year = preg_match('/^\d{4}/', $yearRaw) ? substr($yearRaw, 0, 4) : '';
        if ($year === '' || $year === '0000') {
            $year = (new \DateTimeImmutable())->format('Y');
        }

        return $year;
    }


    /**
     * @param array $f
     * @return string
     */
    private function resolvePrimaryLanguageCode(array $f): string
    {
        $langRaw = $f['customFields']['custom_product_oai_language'] ?? '';

        $token = '';
        if (is_array($langRaw) && $langRaw !== []) {
            $token = (string)$langRaw[0];
        } elseif (is_string($langRaw) && $langRaw !== '') {
            $parts = preg_split('/[,\;\s]+/u', $langRaw, -1, PREG_SPLIT_NO_EMPTY);
            $token = (string)($parts[0] ?? '');
        }

        $token = mb_strtolower(trim($token), 'UTF-8');
        $map2to3 = [
            'de' => 'ger', 'en' => 'eng', 'fr' => 'fre', 'es' => 'spa', 'it' => 'ita',
            'nl' => 'dut', 'cs' => 'cze', 'sk' => 'slo', 'ro' => 'rum', 'alb' => 'alb',
            'pt' => 'por', 'ru' => 'rus', 'pl' => 'pol', 'da' => 'dan', 'sv' => 'swe',
            'no' => 'nor', 'fi' => 'fin', 'hu' => 'hun', 'tr' => 'tur', 'el' => 'gre',
            'zh' => 'chi', 'ja' => 'jpn', 'ko' => 'kor', 'ar' => 'ara',
        ];

        if (isset($map2to3[$token])) {
            return $map2to3[$token];
        }
        if ((bool)preg_match('/^[a-z]{3}$/', $token)) {
            return $token;
        }

        return 'ger';
    }


    /**
     * Normalize free-text fields for MARCXML output.
     *
     * @param string $value
     * @return string
     */
    private function sanitizeText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value);
        $value = preg_replace('/\s+/u', ' ', (string)$value);

        return trim((string)$value);
    }
}
