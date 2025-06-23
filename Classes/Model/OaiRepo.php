<?php

namespace RKW\OaiConnector\Model;

class OaiRepo
{
    protected string $id = '';
    protected string $repositoryName = '';
    protected string $baseURL = '';
    protected string $protocolVersion = '';
    protected string $adminEmails = '';
    protected string $earliestDatestamp = '';
    protected string $deletedRecord = 'no'; // 'no', 'transient', 'persistent'
    protected string $granularity = 'YYYY-MM-DD'; // or 'YYYY-MM-DDThh:mm:ssZ'
    protected ?int $maxListSize = null;
    protected ?int $tokenDuration = null;
    protected string $updated = '';
    protected string $comment = '';

    /*
    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? '';
        $this->repositoryName = $data['repositoryName'] ?? '';
        $this->baseURL = $data['baseURL'] ?? '';
        $this->protocolVersion = $data['protocolVersion'] ?? '';
        $this->adminEmails = $data['adminEmails'] ?? '';
        $this->earliestDatestamp = $data['earliestDatestamp'] ?? '';
        $this->deletedRecord = $data['deletedRecord'] ?? 'no';
        $this->granularity = $data['granularity'] ?? 'YYYY-MM-DD';
        $this->maxListSize = isset($data['maxListSize']) ? (int)$data['maxListSize'] : null;
        $this->tokenDuration = isset($data['tokenDuration']) ? (int)$data['tokenDuration'] : null;
        $this->updated = $data['updated'] ?? '';
        $this->comment = $data['comment'] ?? '';
    }
    */

    // --- Getter & Setter Methods ---

    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }

    public function getRepositoryName(): string { return $this->repositoryName; }
    public function setRepositoryName(string $repositoryName): void { $this->repositoryName = $repositoryName; }

    public function getBaseURL(): string { return $this->baseURL; }
    public function setBaseURL(string $baseURL): void { $this->baseURL = $baseURL; }

    public function getProtocolVersion(): string { return $this->protocolVersion; }
    public function setProtocolVersion(string $protocolVersion): void { $this->protocolVersion = $protocolVersion; }

    public function getAdminEmails(): string { return $this->adminEmails; }
    public function setAdminEmails(string $adminEmails): void { $this->adminEmails = $adminEmails; }

    public function getEarliestDatestamp(): string { return $this->earliestDatestamp; }
    public function setEarliestDatestamp(string $earliestDatestamp): void { $this->earliestDatestamp = $earliestDatestamp; }

    public function getDeletedRecord(): string { return $this->deletedRecord; }
    public function setDeletedRecord(string $deletedRecord): void { $this->deletedRecord = $deletedRecord; }

    public function getGranularity(): string { return $this->granularity; }
    public function setGranularity(string $granularity): void { $this->granularity = $granularity; }

    public function getMaxListSize(): ?int { return $this->maxListSize; }
    public function setMaxListSize(?int $maxListSize): void { $this->maxListSize = $maxListSize; }

    public function getTokenDuration(): ?int { return $this->tokenDuration; }
    public function setTokenDuration(?int $tokenDuration): void { $this->tokenDuration = $tokenDuration; }

    public function getUpdated(): string { return $this->updated; }
    public function setUpdated(string $updated): void { $this->updated = $updated; }

    public function getComment(): string { return $this->comment; }
    public function setComment(string $comment): void { $this->comment = $comment; }
}