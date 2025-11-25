<?php

declare(strict_types=1);

namespace RKW\OaiConnector\Utility;

use Symfony\Component\VarDumper\VarDumper;

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
        $this->addLeader();
        $this->addControl001($f);

        // Descriptive fields
        $this->addCreatorsFromProperties($f);
        $this->addTitle245($f);
        $this->addPublisherAndPlaceAndYear264($f);
        $this->addIsbnIssnAndProductNumber($f);
        $this->addAccess506($f);
        $this->addAbstract520($f);
        $this->addLanguage041($f);

        // Relations and URLs
        $this->addElectronicLocation856($f);
        $this->addJournal773($f);
        $this->addIssue362($f);
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
    protected function addLeader(): void
    {
        $leader = $this->doc->createElement('leader', '00000nam a2200000 a 4500');
        $this->record->appendChild($leader);
    }


    /**
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addControl001(array $f): void
    {
        $id = ($f['identifier'] ?? '') ?: ($f['url'] ?? '');
        if ($id === '') {
            return;
        }

        $cf001 = $this->doc->createElement('controlfield', $id);
        $cf001->setAttribute('tag', '001');
        $this->record->appendChild($cf001);
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

        $aText = rtrim($title, " /:;");
        if ($subtitle !== '') {
            $aText .= ' :';
        }

        $sfA = $this->doc->createElement('subfield', $aText);
        $sfA->setAttribute('code', 'a');
        $df245->appendChild($sfA);

        if ($subtitle !== '') {
            $sfB = $this->doc->createElement('subfield', $subtitle);
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
    // 100/700: Creators (authors) from Shopware properties
    // ---------------------------------------------------------------------

    /**
     * Add 100/700 fields based on Shopware property group "Autorenschaft".
     *
     * @param array $f
     * @return void
     * @throws \DOMException
     */
    protected function addCreatorsFromProperties(array $f): void
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

        $sfA = $this->doc->createElement('subfield', $authors[0]);
        $sfA->setAttribute('code', 'a');
        $df100->appendChild($sfA);

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

                $sfA = $this->doc->createElement('subfield', $authorName);
                $sfA->setAttribute('code', 'a');
                $df700->appendChild($sfA);

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
            $sf2 = $this->doc->createElement('subfield', 'shopware');
            $sf2->setAttribute('code', '2');
            $df024->appendChild($sf2);
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
    protected function addAccess506(array $f): void
    {
        $access = trim($f['customFields']['custom_product_oai_access'] ?? '');
        if ($access === '') {
            $access = 'b';
        }

        $df506 = $this->doc->createElement('datafield');
        $df506->setAttribute('tag', '506');
        $df506->setAttribute('ind1', ' ');
        $df506->setAttribute('ind2', ' ');

        $sfF = $this->doc->createElement('subfield', $access);
        $sfF->setAttribute('code', 'f');
        $df506->appendChild($sfF);

        $sf2 = $this->doc->createElement('subfield', 'local');
        $sf2->setAttribute('code', '2');
        $df506->appendChild($sf2);

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

        if ($description === '') {
            return;
        }

        $df520 = $this->doc->createElement('datafield');
        $df520->setAttribute('tag', '520');
        $df520->setAttribute('ind1', ' ');
        $df520->setAttribute('ind2', ' ');

        $sfA = $this->doc->createElement('subfield', $description);
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

        $sfU = $this->doc->createElement('subfield', $url);
        $sfU->setAttribute('code', 'u');
        $df856->appendChild($sfU);

        $sfY = $this->doc->createElement('subfield', 'Digital publication');
        $sfY->setAttribute('code', 'y');
        $df856->appendChild($sfY);

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

        $name          = trim($f['title'] ?? '');
        $productNumber = trim($f['productNumber'] ?? '');
        $customFields  = $f['customFields'] ?? [];
        $resourceType  = trim($customFields['custom_product_oai_resource_type'] ?? '');

        if ($name === '' || $productNumber === '' || $resourceType === '') {
            return '';
        }

        $slug = $this->slugify($name);
        if ($slug === '') {
            return '';
        }

        switch ($resourceType) {
            case 'am':
                $path = '/shop/download/' . $slug . '/' . rawurlencode($productNumber);
                break;
            case 'ab':
                $path = '/shop/show/' . $slug . '/' . rawurlencode($productNumber);
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
    protected function addJournal773(array $f): void
    {
        $customFields = $f['customFields'] ?? [];
        $resourceType = trim($customFields['custom_product_oai_resource_type'] ?? '');
        $journalTitle = trim($customFields['custom_product_oai_identifier_journal'] ?? '');

        if ($resourceType !== 'ab' || $journalTitle === '') {
            return;
        }

        $df773 = $this->doc->createElement('datafield');
        $df773->setAttribute('tag', '773');
        $df773->setAttribute('ind1', '0');
        $df773->setAttribute('ind2', ' ');

        $sfT = $this->doc->createElement('subfield', $journalTitle);
        $sfT->setAttribute('code', 't');
        $df773->appendChild($sfT);

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
    protected function addIssue362(array $f): void
    {
        $customFields = $f['customFields'] ?? [];
        $resourceType = trim($customFields['custom_product_oai_resource_type'] ?? '');
        $issue        = trim($customFields['custom_product_oai_issue'] ?? '');

        if ($resourceType !== 'ab' || $issue === '') {
            return;
        }

        $df362 = $this->doc->createElement('datafield');
        $df362->setAttribute('tag', '362');
        $df362->setAttribute('ind1', '0');
        $df362->setAttribute('ind2', ' ');

        $sfA = $this->doc->createElement('subfield', $issue);
        $sfA->setAttribute('code', 'a');
        $df362->appendChild($sfA);

        $this->record->appendChild($df362);
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
        $categoryIds = array_values(
            array_filter(
                (array)($f['categoryIds'] ?? []),
                static fn($v) => trim((string)$v) !== ''
            )
        );

        foreach ($categoryIds as $catId) {
            $df690 = $this->doc->createElement('datafield');
            $df690->setAttribute('tag', '690');
            $df690->setAttribute('ind1', ' ');
            $df690->setAttribute('ind2', ' ');
            $sfA = $this->doc->createElement('subfield', (string)$catId);
            $sfA->setAttribute('code', 'a');
            $df690->appendChild($sfA);
            $this->record->appendChild($df690);
        }
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
}