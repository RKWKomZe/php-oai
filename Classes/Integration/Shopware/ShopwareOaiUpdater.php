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

    public function objects($identifierArray = [], $from = null, $to = null, $noDeleted = false, $set = null)
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
        return [];
    }

    public function id($f): mixed
    {
        return $f['identifier'];
    }

    private function xmlEscape($value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    public function setSpecArray(): array
    {
        return [];

        // Falls man doch "sets" nutzen möchte
        //return isset($f['sets']) ? (array)$f['sets'] : [];
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
