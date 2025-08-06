<?php
namespace RKW\OaiConnector\Model;

/**
 * OaiItemMeta
 *
 * Represents metadata associated with an OAI item.
 * Provides properties and methods to manage OAI item metadata such as repository details, unique identifiers,
 * metadata prefixes, timestamps, and status indicators for deletion and publication.
 */
class OaiItemMeta
{

    /**
     * repo
     *
     * @var string
     */
    protected string $repo = '1';

    /**
     * history
     *
     * @var int
     */
    protected int $history = 0;

    /**
     * serial
     *
     * @var int
     */
    protected int $serial = 0;

    /**
     * identifier
     *
     * @var string
     */
    protected string $identifier = '';

    /**
     * metadataPrefix
     *
     * @var string
     */
    protected string $metadataPrefix = '';

    /**
     * datestamp
     *
     * @var string
     */
    protected string $datestamp = '';

    /**
     * deleted
     *
     * @var int
     */
    protected int $deleted = 0;

    /**
     * published
     *
     * @var string|null
     */
    protected ?string $published = null;

    /**
     * metadata
     *
     * @var string
     */
    protected string $metadata = '';

    /**
     * created
     *
     * @var string
     */
    protected string $created = '';

    /**
     * updated
     *
     * @var string
     */
    protected string $updated = '';

    /**
     * constructor
     */
    public function __construct(array $data = [])
    {
        $this->repo = $data['repo'] ?? '1';
        $this->history = (int)($data['history'] ?? 0);
        $this->serial = (int)($data['serial'] ?? 0);
        $this->identifier = $data['identifier'] ?? '';
        $this->metadataPrefix = $data['metadataPrefix'] ?? '';
        $this->datestamp = $data['datestamp'] ?? '';
        $this->published = (int)($data['deleted'] ?? '');
        $this->deleted = (int)($data['deleted'] ?? 0);
        $this->metadata = $data['metadata'] ?? '';
        $this->created = $data['created'] ?? '';
        $this->updated = $data['updated'] ?? '';
    }

    /**
     * Gets repo
     *
     * @return string
     */
    public function getRepo(): string
    {
        return $this->repo;
    }

    /**
     * Sets repo
     *
     * @param string $repo
     * @return void
     */
    public function setRepo(string $repo): void
    {
        $this->repo = $repo;
    }


    /**
     * Gets history
     *
     * @return int
     */
    public function getHistory(): int
    {
        return $this->history;
    }

    /**
     * Sets history
     *
     * @param int $history
     * @return void
     */
    public function setHistory(int $history): void
    {
        $this->history = $history;
    }


    /**
     * Gets serial
     *
     * @return int
     */
    public function getSerial(): int
    {
        return $this->serial;
    }

    /**
     * Sets serial
     *
     * @param int $serial
     * @return void
     */
    public function setSerial(int $serial): void
    {
        $this->serial = $serial;
    }


    /**
     * Gets identifier
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * Sets identifier
     *
     * @param string $identifier
     * @return void
     */
    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }


    /**
     * Gets metadata prefix
     *
     * @return string
     */
    public function getMetadataPrefix(): string
    {
        return $this->metadataPrefix;
    }

    /**
     * Sets metadata prefix
     *
     * @param string $metadataPrefix
     * @return void
     */
    public function setMetadataPrefix(string $metadataPrefix): void
    {
        $this->metadataPrefix = $metadataPrefix;
    }


    /**
     * Gets datestamp
     *
     * @return string
     */
    public function getDatestamp(): string
    {
        return $this->datestamp;
    }

    /**
     * Sets datestamp
     *
     * @param string $datestamp
     * @return void
     */
    public function setDatestamp(string $datestamp): void
    {
        $this->datestamp = $datestamp;
    }


    /**
     * Checks if deleted
     *
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted === 1;
    }

    /**
     * Sets deleted
     *
     * @param bool $deleted
     * @return void
     */
    public function setDeleted(bool $deleted): void
    {
        $this->deleted = $deleted ? 1 : 0;
    }


    /**
     * Gets published
     *
     * @return string|null
     */
    public function isPublished(): ?string
    {
        return $this->published;
    }

    /**
     * Sets published
     *
     * @param string|null $published
     * @return void
     */
    public function setPublished(?string $published): void
    {
        $this->published = $published;
    }


    /**
     * Gets metadata
     *
     * @return string
     */
    public function getMetadata(): string
    {
        return $this->metadata;
    }

    /**
     * Sets metadata
     *
     * @param string $metadata
     * @return void
     */
    public function setMetadata(string $metadata): void
    {
        $this->metadata = $metadata;
    }


    /**
     * Gets created
     *
     * @return string
     */
    public function getCreated(): string
    {
        return $this->created;
    }

    /**
     * Sets created
     *
     * @param string $created
     * @return void
     */
    public function setCreated(string $created): void
    {
        $this->created = $created;
    }


    /**
     * Gets updated
     *
     * @return string
     */
    public function getUpdated(): string
    {
        return $this->updated;
    }

    /**
     * Sets updated
     *
     * @param string $updated
     * @return void
     */
    public function setUpdated(string $updated): void
    {
        $this->updated = $updated;
    }

}
