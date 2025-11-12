<?php

namespace RKW\OaiConnector\Integration\Shopware;

use RKW\OaiConnector\Repository\OaiSetRepository;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class ShopwareOaiUpdater
 *
 * An implementation providing methods to manage and interact with Open Archives Initiative (OAI) records.
 * Extends the Oai_Updater for integrating functionalities specific to Shopware repositories.
 */
class ShopwareOaiUpdater extends \Oai_Updater
{

    /**
     * @var string
     */
    protected string $repoId;

    /**
     * @var array
     */
    protected array $records = [];

    /**
     * @var int
     */
    protected int $cursor = 0;

    private ?OaiSetRepository $oaiSetRepository = null;

    protected function getOaiSetRepository(): OaiSetRepository
    {
        return $this->oaiSetRepository ??= new OaiSetRepository();
    }

    /**
     * Constructor to initialize a repository connection along with specific records to be managed.
     *
     * The constructor is responsible for setting up the repository by initializing its connection details,
     * base parameters, and the records that will be primarily handled.
     *
     * @param string $hostname The database server's hostname or IP address.
     * @param string $username The username required for authentication with the database.
     * @param string $password The password required for authentication with the database.
     * @param string $database The name of the database to connect to within the server.
     * @param string $repo Identifier of the repository to work with, used in the context of the application.
     * @param bool $save_history Boolean flag determining if history should be saved (e.g., modifications or actions).
     * @param array $records Dataset or entries associated with the repository for further processing.
     *
     * @return void
     */
    public function __construct(
        string $hostname,
        string $username,
        string $password,
        string $database,
        string $repo,
        bool $save_history,
        array $records
    ) {
        parent::__construct($hostname, $username, $password, $database, $repo, $save_history);
        $this->repoId = $repo;
        $this->records = $records;
        $this->oaiSetRepository = $this->getOaiSetRepository();
    }


    /**
     * Retrieves objects based on provided filters.
     *
     * Filters:
     * - `$identifierArray`: Optional array of identifiers to filter specific objects.
     * - `$from`: Optional parameter to set the starting range (e.g., date or index).
     * - `$to`: Optional parameter to set the ending range.
     * - `$noDeleted`: Boolean flag to exclude deleted records if set to true.
     * - `$set`: Optional set identifier to filter objects belonging to a specific logical collection.
     *
     * Functionality:
     * - Initializes the cursor to zero, potentially to support iteration or pagination.
     * - Returns the full set of records currently available in `$this->records`.
     *
     * Limitations:
     * - Actual filtering logic appears to be pending implementation. Current behavior may not honor provided filters.
     *
     * @param array $identifierArray Optional. Array of identifiers.
     * @param mixed|null $from Optional. Beginning of the range or filter.
     * @param mixed|null $to Optional. End of the range or filter.
     * @param bool $noDeleted Optional. Flag to exclude deleted records.
     * @param string|null $set Optional. Logical set identifier for filtering.
     * @return array Full list of records, without applying any filtering logic.
     */
    public function objects(
        $identifierArray = [],
        $from = null,
        $to = null,
        $noDeleted = false,
        $set = null
    )
    {
        $this->cursor = 0;
        return $this->records;
    }


    /**
     * Retrieves the next object from the records array while keeping track of its position using a cursor.
     *
     * @param mixed $r Reference variable to store the current object, if available.
     * @return mixed Returns the current record as an object or array if valid, or false if no more records exist.
     */
    public function nextObject(& $r)
    {
        if ($this->cursor < count($this->records)) {
            $r = $this->records[$this->cursor++];
            return $r;
        }

        return false;
    }


    /**
     * Constructs a unique identifier string based on the repository ID and a provided ID.
     *
     * @param mixed $id The specific identifier for an entity.
     *
     * @return string A unique identifier composed of the repository context and the provided ID.
     */
    public function identifier($id): string
    {
        return 'oai:' . $this->repoId . ':' . $id;
    }


    /**
     * Processes the given identifier to extract and return a simplified version.
     *
     * This method removes a specific prefix from the identifier based on the repository ID.
     * The prefix is defined in the format "oai:{repoId}:" and is stripped out using a regular expression.
     * The resulting string consists of the remaining part of the identifier after the defined prefix.
     *
     * @param string $identifier The original identifier, typically in the format "oai:{repoId}:{remainingIdentifier}".
     * @return string The identifier after removing the defined prefix.
     */
    public function parseIdentifier($identifier): string
    {
        return preg_replace('/^oai:' . preg_quote($this->repoId, '/') . ':/', '', $identifier);
    }


    /**
     * This method is used to retrieve a 'datestamp' value from a given input.
     *
     * Handling:
     * - When the input is an array and contains a 'datestamp' key, the value of this key is returned as a string.
     * - If the input does not meet the above conditions, an empty string is returned.
     *
     * Purpose:
     * Ensures consistent retrieval of a 'datestamp' value from provided data structures, or fallback behavior when it is not present.
     *
     * @param mixed $f Input which can be an array or another type.
     *
     * @return string The 'datestamp' value if present within an array, otherwise an empty string.
     */
    public function datestamp($f): string
    {
        // If it's an array, return its 'datestamp' field
        if (is_array($f) && isset($f['datestamp'])) {
            return $f['datestamp'];
        }

        // Optional: handle fallback or logging
        return '';
    }


    /**
     * This method determines if a record is marked as deleted and returns its corresponding status.
     *
     * Purpose:
     * The method checks the provided input array for a 'deleted' key.
     * If the key exists, it returns its value as an integer. If the key does not exist, it defaults to 0.
     *
     * Input:
     * The method expects a single parameter `$f`, which is an associative array.
     *
     * Output:
     * Returns an integer value:
     * - 1 if the 'deleted' key exists and its value can be cast to 1.
     * - 0 if the 'deleted' key does not exist or evaluates to the default value of 0.
     *
     * @param array $f Input array which may contain a 'deleted' key.
     *
     * @return int Returns the integer representation of the 'deleted' flag (1 or 0).
     */
    public function deleted($f): int
    {
        return isset($f['deleted']) ? (int)$f['deleted'] : 0;
    }


    /**
     * Generates metadata in XML format for a given dataset, conforming to the OAI-DC (Dublin Core) schema.
     *
     * Currently oai-dc: Maybe also marcxml needed
     *
     * @param array $f An associative array containing the metadata fields. Expected keys:
     *                 - 'title': The title of the resource.
     *                 - 'url': The identifier or URL of the resource.
     *                 - 'description': A short description of the resource.
     *
     * @param string $metadataPrefix A prefix indicating the metadata format being used. It serves as a representation of a specific metadata schema.
     *
     * @return string Returns an XML string containing formatted metadata for the provided dataset.
     */
    public function metadata($f, $metadataPrefix): string
    {

        // Hint: The $f product array is build in ShopwareOaiUpdater->transformProduct()

        /*
        var_dump($metadataPrefix); exit;

        $xml = <<<XML
<oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/"
           xmlns:dc="http://purl.org/dc/elements/1.1/"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/
           http://www.openarchives.org/OAI/2.0/oai_dc.xsd">
    <dc:title>{$this->xmlEscape($f['title'])}</dc:title>
    <dc:identifier>{$this->xmlEscape($f['url'])}</dc:identifier>
    <dc:description>{$this->xmlEscape($f['description'])}</dc:description>
</oai_dc:dc>
XML;


        return $xml;
        */

        switch (strtolower($metadataPrefix)) {
            case 'oai_dc':
                return $this->renderDublinCore($f);
            case 'marcxml':
            default:
                return $this->renderMarcXml($f);
        }

    }


    /**
     * Generates an 'about' metadata structure with information about the source, product,
     * and the time of import.
     *
     * @param array $f An associative array containing the key 'title', used to derive the product name.
     * @param string $metadataPrefix Placeholder parameter, purpose is left undefined in the scope of this method.
     *
     * @return array Returns an array containing a single string element with the structured metadata in XML-like format.
     */
    public function about($f, $metadataPrefix): array
    {
        $productName = htmlspecialchars($f['title'] ?? 'unbekannt');

        return [
            '<about>
            <source>Shopware</source>
            <product>' . $productName . '</product>
            <imported>' . date('c') . '</imported>
        </about>'
        ];
    }


    /**
     * Retrieves the identifier value from the provided array.
     *
     * @param array $f The input array containing the 'identifier' key.
     * @return mixed The value associated with the 'identifier' key in the provided array.
     */
    public function id($f): mixed
    {
        return $f['identifier'];
    }


    /**
     * Escapes special characters in a string to make it safe for use in XML content.
     *
     * @param string $value The input string to be escaped for XML usage.
     *
     * @return string The XML-escaped string.
     */
    private function xmlEscape($value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }


    /**
     * Purpose:
     * setSpec is used to identify and retrieve specific sets of records from a repository, rather than the entire dataset.
     * Format:
     * setSpec is a string value, typically consisting of alphanumeric characters and potentially colons (":") to indicate hierarchical relationships between sets.
     * Uniqueness:
     * Each setSpec within a repository must be unique.
     * Hierarchical Sets:
     * setSpec can be used to represent hierarchical set structures, where colons separate levels in the hierarchy (e.g., parent:child:grandchild).
     *
     * @return array
     */
    public function setSpecArray(): array
    {

        // @toDo: We need a real handling here...

        // @toTo: Use shopware categories???

        /*

            $sets = [];

            foreach ($this->records as $record) {
                if (!empty($record['category'])) {
                    $sets[] = strtolower(preg_replace('/\s+/', '_', $record['category']));
                }
            }

            return array_unique($sets) ?: ['default'];

         */

        // solution a little bit stupid, works only with one Set. Maybe the Set-Selection should work through shopware
        // categories or somewhat else
        $oaiRepoSet = $this->oaiSetRepository
            ->withModels()
            ->findOneBy(
                [
                    'repo' => $this->repoId
                ]
            );

        return [$oaiRepoSet->getSetSpec()];

        // Das set muss vorher existieren. Hier legen wir es einfach mal via "Piratenmethode" an:
       // return ['default'];


        /*
        $setSpec = 'shopware';
        $setName = 'Shopware-Daten';

        // ACHTUNG: Sicherstellen, dass _backend das richtige Objekt ist
        if (method_exists($this->_backend, 'setSpecExists') && !$this->_backend->setSpecExists($setSpec)) {
            $this->_backend->setSpecCreate($setSpec, $setName);
        }


        return ['shopware'];
        */


        /*
        $sets = [];

        foreach ($this->records as $record) {
            if (!empty($record['category'])) {
                $sets[] = strtolower($record['category']); // z. B. 'electronics', 'books'
            }
        }

        return array_unique($sets);
        */
    }


    /**
     * Purpose:
     * The function is utilized to retrieve an array of metadata prefixes, which can be used to specify the metadata format when interacting with repositories or protocols.
     * Default Value:
     * Returns a default metadata prefix that represents a specific format (e.g., 'oai_dc').
     * Usage Variability:
     * The returned prefixes could be dynamically adjusted in the future to support multiple metadata formats.
     *
     * @return array
     */
    public function metadataPrefixArray()
    {
        // Just a mindless default return. We can pass the value manually when calling ->run in ImportController.
        // for the "Deutsche Nationalbibliothek" we want to use marcxml
        return ['marcxml'];
    }


    /**
     * This method generates a structured metadata array related to a specific entity or product.
     * It analyzes the provided input, retrieves the corresponding product or entity information,
     * and formats metadata that describes attributes or notes about the given entity.
     *
     * @param mixed $f The input used to locate or identify a specific entity or product.
     *
     * @return array Returns an array containing structured metadata related to the specified entity or product.
     */
    public function aboutMetadata($f): array
    {
        $id = $this->id($f);
        $product = $this->findProductById($id);

        // Example: Add structured "note"-about metadata
        return [
            [
                'about' => 'note',
                'meta' => [
                    'type' => $product['type'] ?? 'default',
                    'level' => $product['visibility'] ?? 'normal',
                ],
            ],
        ];
    }


    /**
     * findProductById
     */
    protected function findProductById(string $id): ?array
    {
        foreach ($this->records as $record) {
            if ($record['identifier'] === $id) {
                return $record;
            }
        }
        return null;
    }



    /**
     * Render Dublin Core (oai_dc).
     * Namespace & schema per OAI-DC guidelines.
     *
     * @deprecated: DublinCore is not used yet!
     */
    private function renderDublinCore(array $f): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;

        $dc = $doc->createElementNS('http://www.openarchives.org/OAI/2.0/oai_dc/', 'oai_dc:dc');
        $dc->setAttributeNS(
            'http://www.w3.org/2001/XMLSchema-instance',
            'xsi:schemaLocation',
            'http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd'
        );
        // Add the DC namespace
        $dc->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
        $doc->appendChild($dc);

        // dc:title
        if ($f['title'] !== '') {
            $dc->appendChild($doc->createElement('dc:title', $f['title']));
        }

        // dc:identifier (prefer explicit identifier, fallback to URL)
        $identifier = $f['identifier'] ?: $f['url'];
        if ($identifier !== '') {
            $dc->appendChild($doc->createElement('dc:identifier', $identifier));
        }

        // dc:description
        if ($f['description'] !== '') {
            $dc->appendChild($doc->createElement('dc:description', $f['description']));
        }

        // dc:creator (repeatable)
        foreach ((array)$f['creators'] as $creator) {
            if ($creator !== '') {
                $dc->appendChild($doc->createElement('dc:creator', $creator));
            }
        }

        // dc:subject (repeatable)
        foreach ((array)$f['subjects'] as $subject) {
            if ($subject !== '') {
                $dc->appendChild($doc->createElement('dc:subject', $subject));
            }
        }

        // dc:date (ISO is fine)
        if (!empty($f['date'])) {
            $dc->appendChild($doc->createElement('dc:date', $f['date']));
        }

        // dc:language (BCP47 like "de" or ISO 639-2 like "deu")
        if (!empty($f['language'])) {
            $dc->appendChild($doc->createElement('dc:language', $f['language']));
        }

        // Return only the <oai_dc:dc> subtree (no XML declaration)
        return $doc->saveXML($dc);
    }



    /**
     * Render MARCXML (slim schema) incl. bundle relations and category handling.
     *
     * Marc21 856 = "Electronic Location and Access" (URL e.g)
     * ind1="4" -> Zugriffsmethode http/https. Andere Werte wären z. B. 0=E-Mail, 1=FTP, 2=Telnet, 3=Dial-up, 7=„sonstiges; genauer in $2“.
     *
     */
    private function renderMarcXml(array $f): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;

        // Helper closures (kept local; lift to methods if you prefer)
        $toOaiId = function (?string $uuid): string {
            $uuid = trim((string)$uuid);
            return $uuid !== '' ? 'oai:rkw:shopware:' . $uuid : '';
        };
        $parseIdList = function (?string $raw) : array {
            if (!$raw) return [];
            $parts = preg_split('/[\s,;]+/', trim($raw));
            $parts = array_filter(array_map('trim', (array)$parts), fn($v) => $v !== '');
            return array_values(array_unique($parts));
        };

        // <record xmlns="http://www.loc.gov/MARC21/slim">
        $record = $doc->createElementNS('http://www.loc.gov/MARC21/slim', 'record');
        $doc->appendChild($record);

        // <leader> — adapt to your material type if needed
        $leader = $doc->createElement('leader', '00000nam a2200000 a 4500');
        $record->appendChild($leader);

        // 001 Controlfield: stable control number (prefer explicit identifier, else URL)
        $id = ($f['identifier'] ?? '') ?: ($f['url'] ?? '');
        if ($id !== '') {
            $customFields001 = $doc->createElement('controlfield', $id);
            $customFields001->setAttribute('tag', '001');
            $record->appendChild($customFields001);
        }


        //##############################################################################
        // 245 Title statement from:
        // - main title: name -> 245 $a
        // - subtitle : translated.customFields.custom_meta_subheader -> 245 $b
        $title = trim($f['name'] ?? '');
        $subtitle = trim($f['translated']['customFields']['custom_meta_subheader'] ?? '');

        // Helper to compute non-filing characters (indicator 2) for common leading articles
        $computeNonFiling = static function (string $t): int {
            // Articles to strip for filing (case-insensitive). Include trailing space/apostrophe.
            $articles = [
                "der ", "die ", "das ", "ein ", "eine ", "einen ", "dem ", "den ", "des ",
                "the ", "a ", "an ",
                "le ", "la ", "les ", "l'",
                "el ", "los ", "las ",
                "il ", "lo ", "gli ", "i ",
                "de ", "het ", "den ", "det "
            ];
            $lt = mb_strtolower($t, 'UTF-8');
            foreach ($articles as $art) {
                if (mb_substr($lt, 0, mb_strlen($art, 'UTF-8'), 'UTF-8') === $art) {
                    // Count exact UTF-8 length of the matched prefix as non-filing chars
                    return min(mb_strlen($art, 'UTF-8'), 9); // MARC 245 ind2 is a single digit (0–9)
                }
            }
            return 0;
        };

        if ($title !== '') {
            // Decide ind1: set to "1" if you create a 1XX main entry elsewhere, otherwise "0"
            $hasMainEntry1XX = false; // set true if you add 100/110/111
            $ind1 = $hasMainEntry1XX ? '1' : '0';
            $ind2 = (string)$computeNonFiling($title);

            $df245 = $doc->createElement('datafield');
            $df245->setAttribute('tag', '245');
            $df245->setAttribute('ind1', $ind1);
            $df245->setAttribute('ind2', $ind2);

            // $a: ensure clean punctuation before adding $b
            $aText = rtrim($title, " /:;");
            if ($subtitle !== '') {
                $aText .= ' :'; // MARC punctuation: space-colon before $b
            }

            $sfA = $doc->createElement('subfield', $aText);
            $sfA->setAttribute('code', 'a');
            $df245->appendChild($sfA);

            if ($subtitle !== '') {
                $sfB = $doc->createElement('subfield', $subtitle);
                $sfB->setAttribute('code', 'b');
                $df245->appendChild($sfB);
            }

            $record->appendChild($df245);
        }

        //##############################################################################
        // 264 Publication year (from datestamp YYYY…)
        if (!empty($f['releaseDate'])) {
            $year = substr((string)$f['releaseDate'], 0, 4);
            if (ctype_digit($year)) {
                $df264 = $doc->createElement('datafield');
                $df264->setAttribute('tag', '264');
                $df264->setAttribute('ind1', ' ');
                $df264->setAttribute('ind2', '1'); // 1 = publication

                $sfC = $doc->createElement('subfield', $year);
                $sfC->setAttribute('code', 'c');
                $df264->appendChild($sfC);

                $record->appendChild($df264);
            }
        }


        // ################################
        // #### SHOPWARE CUSTOM FIELDS ####
        // ################################

        $customFields = $f['customFields'] ?? [];

        //##############################################################################
        // 20 / 22 - ISBN / ISSN or product number mapping
        $value = $customFields['custom_product_oai_issn_isbn'] ?? '';
        $productNumber = $f['productNumber'] ?? '';

        if (!empty($value)) {
            if (preg_match('/^\d{9}[\dXx]$/', $value) || preg_match('/^\d{13}$/', $value)) {
                // ISBN
                $df020 = $doc->createElement('datafield');
                $df020->setAttribute('tag', '020');
                $df020->setAttribute('ind1', ' ');
                $df020->setAttribute('ind2', ' ');
                $sfA = $doc->createElement('subfield', $value);
                $sfA->setAttribute('code', 'a');
                $df020->appendChild($sfA);
                $record->appendChild($df020);
            } elseif (preg_match('/^\d{4}-\d{3}[\dxX]$/', $value)) {
                // ISSN
                $df022 = $doc->createElement('datafield');
                $df022->setAttribute('tag', '022');
                $df022->setAttribute('ind1', ' ');
                $df022->setAttribute('ind2', ' ');
                $sfA = $doc->createElement('subfield', $value);
                $sfA->setAttribute('code', 'a');
                $df022->appendChild($sfA);
                $record->appendChild($df022);
            }
        }

        // 24 - Fallback: use productNumber as alternative ID
        if (!empty($productNumber)) {
            $df024 = $doc->createElement('datafield');
            $df024->setAttribute('tag', '024');
            $df024->setAttribute('ind1', '8'); // Other standard number
            $df024->setAttribute('ind2', ' ');
            $sfA = $doc->createElement('subfield', $productNumber);
            $sfA->setAttribute('code', 'a');
            $df024->appendChild($sfA);
            $sf2 = $doc->createElement('subfield', 'shopware');
            $sf2->setAttribute('code', '2');
            $df024->appendChild($sf2);
            $record->appendChild($df024);
        }

        //##############################################################################
        // 264 - Publication place ($a) and publisher ($b) with fallbacks
        $publisher = trim($f['manufacturer']['translated']['name'] ?? '');
        $place = trim($f['manufacturer']['translated']['customFields']['custom_manufacturer_oai_place'] ?? '');

        // Apply fallbacks if empty
        if ($publisher === '') {
            $publisher = 'RKW Kompetenzzentrum';
        }
        if ($place === '') {
            $place = 'Eschborn';
        }

        // Only create 264 if at least one of $a/$b will be non-empty
        if ($publisher !== '' || $place !== '') {
            $df264pb = $doc->createElement('datafield');
            $df264pb->setAttribute('tag', '264');
            $df264pb->setAttribute('ind1', ' ');
            $df264pb->setAttribute('ind2', '1'); // 1 = publication

            // $a = place of publication
            if ($place !== '') {
                $sfA = $doc->createElement('subfield', $place);
                $sfA->setAttribute('code', 'a');
                $df264pb->appendChild($sfA);
            }

            // $b = publisher
            if ($publisher !== '') {
                $sfB = $doc->createElement('subfield', $publisher);
                $sfB->setAttribute('code', 'b');
                $df264pb->appendChild($sfB);
            }

            $record->appendChild($df264pb);
        }


        //##############################################################################
        // 506 Access rights (from customFields.custom_product_oai_access)
        $access = trim($f['customFields']['custom_product_oai_access'] ?? '');
        if ($access === '') {
            $access = 'b'; // Fallback: restricted access
        }

        $df506 = $doc->createElement('datafield');
        $df506->setAttribute('tag', '506');
        $df506->setAttribute('ind1', ' ');
        $df506->setAttribute('ind2', ' ');

        // $f = access status code (e.g. a=open, b=restricted)
        $sfF = $doc->createElement('subfield', $access);
        $sfF->setAttribute('code', 'f');
        $df506->appendChild($sfF);

        // $2 = source of code list
        $sf2 = $doc->createElement('subfield', 'local');
        $sf2->setAttribute('code', '2');
        $df506->appendChild($sf2);

        $record->appendChild($df506);


        //##############################################################################
        // 520 Summary/Abstract (prefer custom cover text)
        $description = ($customFields['custom_meta_covertext'] ?? '') ?: ($f['description'] ?? '');
        if ($description !== '') {
            $df520 = $doc->createElement('datafield');
            $df520->setAttribute('tag', '520');
            $df520->setAttribute('ind1', ' ');
            $df520->setAttribute('ind2', ' ');

            $sfA = $doc->createElement('subfield', $description);
            $sfA->setAttribute('code', 'a');
            $df520->appendChild($sfA);

            $record->appendChild($df520);
        }


        //##############################################################################
        // 856 Electronic locations (product page URL + optional downloads)
        $urls = [];
        if (!empty($f['url'])) {
            $urls[] = (string)$f['url'];
        }
        // @toDo: Field "custom_download_" is only ID so far
        if (!empty($customFields['custom_download_'])) {
            $urls[] = (string)$customFields['custom_download_'];
        }
        // @toDo: Field "custom_bundle_file" is only ID so far
        if (!empty($customFields['custom_bundle_file'])) {
            $urls[] = (string)$customFields['custom_bundle_file'];
        }
        foreach (array_unique($urls) as $u) {
            if ($u === '') continue;
            $df856 = $doc->createElement('datafield');
            $df856->setAttribute('tag', '856');
            $df856->setAttribute('ind1', '4'); // 4 = HTTP
            $df856->setAttribute('ind2', '0');
            $sfU = $doc->createElement('subfield', $u);
            $sfU->setAttribute('code', 'u');
            $df856->appendChild($sfU);
            $record->appendChild($df856);
        }

        // === Bundle relations ===
        // Child -> Parent (773)
        $parentUuid = trim((string)($customFields['custom_bundles_parent'] ?? ''));
        if ($parentUuid !== '') {
            $df773 = $doc->createElement('datafield');
            $df773->setAttribute('tag', '773');
            $df773->setAttribute('ind1', ' ');
            $df773->setAttribute('ind2', ' ');
            $sfW = $doc->createElement('subfield', $toOaiId($parentUuid)); // related record ID
            $sfW->setAttribute('code', 'w'); // "w" = control number of related record
            $df773->appendChild($sfW);
            $record->appendChild($df773);
        }

        // Parent -> Children (774)
        $isBundleActive = !empty($customFields['custom_bundles_active']);
        $childrenRaw    = (string)($customFields['custom_bundles_products'] ?? '');
        $children       = $parseIdList($childrenRaw);
        if ($isBundleActive && !empty($children)) {
            foreach ($children as $kidUuid) {
                $df774 = $doc->createElement('datafield');
                $df774->setAttribute('tag', '774');
                $df774->setAttribute('ind1', ' ');
                $df774->setAttribute('ind2', ' ');
                $sfW = $doc->createElement('subfield', $toOaiId($kidUuid));
                $sfW->setAttribute('code', 'w');
                $df774->appendChild($sfW);
                $record->appendChild($df774);
            }
        }

        // === Category handling (multiple) ===
        // We expect $f['categoryIds'] as array of UUIDs (strings). Optional: $f['categoryNamesById'] as [uuid => name].
        $categoryIds        = array_values(array_filter((array)($f['categoryIds'] ?? []), fn($v) => trim((string)$v) !== ''));
        $categoryNamesById  = (array)($f['categoryNamesById'] ?? []); // optional map UUID -> human-readable name

        // 690: write raw internal IDs (local subject) – one field per ID
        foreach ($categoryIds as $catId) {
            $df690 = $doc->createElement('datafield');
            $df690->setAttribute('tag', '690'); // local subject added entry
            $df690->setAttribute('ind1', ' ');
            $df690->setAttribute('ind2', ' ');
            $sfA = $doc->createElement('subfield', (string)$catId);
            $sfA->setAttribute('code', 'a');
            $df690->appendChild($sfA);
            $record->appendChild($df690);
        }

        /*
        // If you also have a human-readable category name, map that to 650$a.
        if (!empty($f['categoryName'])) {
            $df650 = $doc->createElement('datafield');
            $df650->setAttribute('tag', '650'); // topical term
            $df650->setAttribute('ind1', ' ');
            $df650->setAttribute('ind2', '7'); // "7" = source specified in $2 (optional)
            $sfA = $doc->createElement('subfield', (string)$f['categoryName']);
            $sfA->setAttribute('code', 'a');
            $df650->appendChild($sfA);
            // Optional: specify your source vocabulary in $2, e.g. "shopware"
            $sf2 = $doc->createElement('subfield', 'shopware');
            $sf2->setAttribute('code', '2');
            $df650->appendChild($sf2);

            $record->appendChild($df650);
        }
        */


        /*
       // 100 / 700 Creators (very rough mapping; refine to your cataloging rules)
       // 100 is "Main Entry—Personal Name"; 700 additional entries. If multiple, you might pick the first as 100.
       $creators = array_values(array_filter((array)$f['creators'], fn($c) => trim((string)$c) !== ''));
       if (!empty($creators)) {
           // First creator as 100
           $df100 = $doc->createElement('datafield');
           $df100->setAttribute('tag', '100');
           $df100->setAttribute('ind1', '1');
           $df100->setAttribute('ind2', ' ');
           $sfA = $doc->createElement('subfield', $creators[0]);
           $sfA->setAttribute('code', 'a');
           $df100->appendChild($sfA);
           $record->appendChild($df100);

           // Remaining as 700
           for ($i = 1; $i < count($creators); $i++) {
               $df700 = $doc->createElement('datafield');
               $df700->setAttribute('tag', '700');
               $df700->setAttribute('ind1', '1');
               $df700->setAttribute('ind2', ' ');
               $sfA = $doc->createElement('subfield', $creators[$i]);
               $sfA->setAttribute('code', 'a');
               $df700->appendChild($sfA);
               $record->appendChild($df700);
           }
       }
       */

        /*
        // 650 Subjects (topical terms)
        foreach ((array)$f['subjects'] as $subject) {
            if ($subject !== '') {
                $df650 = $doc->createElement('datafield');
                $df650->setAttribute('tag', '650');
                $df650->setAttribute('ind1', ' ');
                $df650->setAttribute('ind2', '0'); // 0 = LCSH; adjust if you have another vocabulary
                $sfA = $doc->createElement('subfield', $subject);
                $sfA->setAttribute('code', 'a');
                $df650->appendChild($sfA);
                $record->appendChild($df650);
            }
        }
        */



        //##############################################################################
        // 041 Language (ISO 639-2, fallback 'ger')
        // Source: customFields.custom_product_oai_language
        $langRaw = $f['customFields']['custom_product_oai_language'] ?? '';

        // Normalize to array of lowercased tokens
        $tokens = [];
        if (is_array($langRaw)) {
            $tokens = $langRaw;
        } elseif (is_string($langRaw)) {
            // Split by comma/semicolon/whitespace
            $tokens = preg_split('/[,\;\s]+/u', $langRaw, -1, PREG_SPLIT_NO_EMPTY);
        }
        $tokens = array_map(static fn($v) => mb_strtolower(trim((string)$v), 'UTF-8'), $tokens);

        // Mapping 2-letter -> ISO 639-2/B codes (use bibliographic forms to align with 'ger')
        $map2to3 = [
            'de' => 'ger', 'en' => 'eng', 'fr' => 'fre', 'es' => 'spa', 'it' => 'ita',
            'nl' => 'dut', 'cs' => 'cze', 'sk' => 'slo', 'ro' => 'rum', 'alb' => 'alb', // edge cases if given
            'pt' => 'por', 'ru' => 'rus', 'pl' => 'pol', 'da' => 'dan', 'sv' => 'swe',
            'no' => 'nor', 'fi' => 'fin', 'hu' => 'hun', 'tr' => 'tur', 'el' => 'gre',
            'zh' => 'chi', 'ja' => 'jpn', 'ko' => 'kor', 'ar' => 'ara',
        ];

        // Acceptable 3-letter pattern
        $isThree = static fn(string $v) => (bool)preg_match('/^[a-z]{3}$/', $v);

        // Convert tokens to 3-letter 639-2/B
        $langs = [];
        foreach ($tokens as $t) {
            if ($t === '') { continue; }
            if (isset($map2to3[$t])) {
                $langs[] = $map2to3[$t];
            } elseif ($isThree($t)) {
                // Assume user already supplied a valid 3-letter code; keep as-is
                $langs[] = $t;
            }
        }

        // Apply fallback if nothing valid
        if (!$langs) {
            $langs = ['ger'];
        }

        // De-duplicate while preserving order
        $langs = array_values(array_unique($langs));

        // Emit 041 with one $a per language
        $df041 = $doc->createElement('datafield');
        $df041->setAttribute('tag', '041');
        $df041->setAttribute('ind1', ' ');
        $df041->setAttribute('ind2', ' ');
        foreach ($langs as $code) {
            $sfA = $doc->createElement('subfield', $code);
            $sfA->setAttribute('code', 'a');
            $df041->appendChild($sfA);
        }
        $record->appendChild($df041);



        // Return only the <record> subtree (no XML declaration)
        return $doc->saveXML($record);
    }



    /**
     * Very small helper to map 2-letter to 3-letter MARC language codes where possible.
     * Extend this mapping to your needs or feed proper ISO 639-2 codes directly.
     */
    private function toMarcLang(string $lang): string
    {
        $lang = strtolower(trim($lang));
        // Simple common mappings; extend as needed
        $map = [
            'de' => 'deu',
            'en' => 'eng',
            'fr' => 'fre', // or 'fra' depending on your cataloging choice
            'es' => 'spa',
            'it' => 'ita',
            'nl' => 'dut', // or 'nld'
            'sv' => 'swe',
        ];
        if (isset($map[$lang])) {
            return $map[$lang];
        }
        // If already 3 letters, pass through
        if (strlen($lang) === 3) {
            return $lang;
        }
        return '';
    }



}
