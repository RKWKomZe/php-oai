<?php
namespace RKW\OaiConnector\Model;

class OaiItemMeta
{

    protected string $repo = '1';
    protected int $history = 0;
    protected int $serial = 0;
    protected string $identifier = '';
    protected string $metadataPrefix = '';
    protected string $datestamp = '';
    protected int $deleted = 0;
    protected string $metadata = '';
    protected string $created = '';
    protected string $updated = '';

    public function __construct(array $data = [])
    {
        $this->repo = $data['repo'] ?? '1';
        $this->history = (int)($data['history'] ?? 0);
        $this->serial = (int)($data['serial'] ?? 0);
        $this->identifier = $data['identifier'] ?? '';
        $this->metadataPrefix = $data['metadataPrefix'] ?? '';
        $this->datestamp = $data['datestamp'] ?? '';
        $this->deleted = (int)($data['deleted'] ?? 0);
        $this->metadata = $data['metadata'] ?? '';
        $this->created = $data['created'] ?? '';
        $this->updated = $data['updated'] ?? '';
    }

    // --- Getter and Setter Methods ---

    public function getRepo(): string { return $this->repo; }
    public function setRepo(string $repo): void { $this->repo = $repo; }

    public function getHistory(): int { return $this->history; }
    public function setHistory(int $history): void { $this->history = $history; }

    public function getSerial(): int { return $this->serial; }
    public function setSerial(int $serial): void { $this->serial = $serial; }

    public function getIdentifier(): string { return $this->identifier; }
    public function setIdentifier(string $identifier): void { $this->identifier = $identifier; }

    public function getMetadataPrefix(): string { return $this->metadataPrefix; }
    public function setMetadataPrefix(string $metadataPrefix): void { $this->metadataPrefix = $metadataPrefix; }

    public function getDatestamp(): string { return $this->datestamp; }
    public function setDatestamp(string $datestamp): void { $this->datestamp = $datestamp; }

    public function isDeleted(): bool { return $this->deleted === 1; }
    public function setDeleted(bool $deleted): void { $this->deleted = $deleted ? 1 : 0; }

    public function getMetadata(): string { return $this->metadata; }
    public function setMetadata(string $metadata): void { $this->metadata = $metadata; }

    public function getCreated(): string { return $this->created; }
    public function setCreated(string $created): void { $this->created = $created; }

    public function getUpdated(): string { return $this->updated; }
    public function setUpdated(string $updated): void { $this->updated = $updated; }
}
