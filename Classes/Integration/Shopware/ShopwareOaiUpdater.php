<?php

namespace RKW\OaiConnector\Integration\Shopware;

use Symfony\Component\VarDumper\VarDumper;

require_once __DIR__ . '/../../../bootstrap.php';

class ShopwareOaiUpdater extends \Oai_Updater
{

    protected string $repoId;

    protected array $records = [];
    protected int $cursor = 0;

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
    }

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
     * Gibt das nächste Objekt per Referenz zurück. Muss true oder false liefern.
     */
    public function nextObject(& $r)
    {
        if ($this->cursor < count($this->records)) {
            $r = $this->records[$this->cursor++];
            return $r;
        }

        return false;
    }

    public function identifier($id): string
    {
        return 'oai:' . $this->repoId . ':' . $id;
    }

    public function parseIdentifier($identifier): string
    {
        return preg_replace('/^oai:' . preg_quote($this->repoId, '/') . ':/', '', $identifier);
    }

    /**
     * @param $f
     * @return string
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

    public function deleted($f): int
    {
        return isset($f['deleted']) ? (int)$f['deleted'] : 0;
    }

    public function metadata($f, $metadataPrefix): string
    {
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
    }

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

    public function id($f): mixed
    {
        return $f['identifier'];
    }

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

        return ['default'];
        // Das set muss vorher existieren. Hier legen wir es einfach mal via "Piratenmethode" an:

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

    public function metadataPrefixArray()
    {
        return ['oai_dc'];
    }

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

    protected function findProductById(string $id): ?array
    {
        foreach ($this->records as $record) {
            if ($record['identifier'] === $id) {
                return $record;
            }
        }
        return null;
    }
}
