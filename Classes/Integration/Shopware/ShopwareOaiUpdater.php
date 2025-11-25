<?php

namespace RKW\OaiConnector\Integration\Shopware;

use RKW\OaiConnector\Repository\OaiSetRepository;
use RKW\OaiConnector\Utility\MarcXmlBuilder;
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
    ): array
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
    public function parseIdentifier(string $identifier): string
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

        // HINT: The $f product array is build in ShopwareOaiUpdater->transformProduct()

        return match (strtolower($metadataPrefix)) {
            'oai_dc' => $this->renderDublinCore($f),
            default => $this->renderMarcXml($f),
        };

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
    private function xmlEscape(string $value): string
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
            ->findOneBy([
                'repo' => $this->repoId
            ]);

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
    public function metadataPrefixArray(): array
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
    public function aboutMetadata(mixed $f): array
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
     * Render MARCXML
     * @throws \DOMException
     */
    private function renderMarcXml(array $f): string
    {
        $builder = new MarcXmlBuilder();

        return $builder->renderRecord($f);
    }


}
