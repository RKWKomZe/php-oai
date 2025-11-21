<?php

namespace RKW\OaiConnector\Model;

/**
 * OaiRepo
 *
 * Represents an OAI repository with configurations and metadata properties.
 */
class OaiRepo
{

    /**
     * id
     *
     * @var string
     */
    protected string $id = '';


    /**
     * repositoryName
     *
     * @var string
     */
    protected string $repositoryName = '';


    /**
     * baseURL
     *
     * @var string
     */
    protected string $baseURL = '';


    /**
     * protocolVersion
     *
     * @var string
     */
    protected string $protocolVersion = '';


    /**
     * adminEmails
     *
     * @var string
     */
    protected string $adminEmails = '';


    /**
     * earliestDatestamp
     *
     * @var string
     */
    protected string $earliestDatestamp = '';


    /**
     * deletedRecord
     * Possible values: 'no', 'transient', 'persistent'
     *
     * @var string
     */
    protected string $deletedRecord = 'no';


    /**
     * granularity
     * Possible values: 'YYYY-MM-DD' or 'YYYY-MM-DDThh:mm:ssZ'
     *
     * @var string
     */
    protected string $granularity = 'YYYY-MM-DD';


    /**
     * maxListSize
     *
     * @var int|null
     */
    protected ?int $maxListSize = null;


    /**
     * tokenDuration
     *
     * @var int|null
     */
    protected ?int $tokenDuration = null;


    /**
     * updated
     *
     * @var string
     */
    protected string $updated = '';


    /**
     * comment
     *
     * @var string
     */
    protected string $comment = '';


    /**
     * Gets id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * Sets id
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }


    /**
     * Gets repository name
     *
     * @return string
     */
    public function getRepositoryName(): string
    {
        return $this->repositoryName;
    }


    /**
     * Sets repository name
     *
     * @param string $repositoryName
     * @return void
     */
    public function setRepositoryName(string $repositoryName): void
    {
        $this->repositoryName = $repositoryName;
    }


    /**
     * Gets base URL
     *
     * @return string
     */
    public function getBaseURL(): string
    {
        return $this->baseURL;
    }


    /**
     * Sets base URL
     *
     * @param string $baseURL
     * @return void
     */
    public function setBaseURL(string $baseURL): void
    {
        $this->baseURL = $baseURL;
    }


    /**
     * Gets protocol version
     *
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }


    /**
     * Sets protocol version
     *
     * @param string $protocolVersion
     * @return void
     */
    public function setProtocolVersion(string $protocolVersion): void
    {
        $this->protocolVersion = $protocolVersion;
    }


    /**
     * Gets admin emails
     *
     * @return string
     */
    public function getAdminEmails(): string
    {
        return $this->adminEmails;
    }


    /**
     * Sets admin emails
     *
     * @param string $adminEmails
     * @return void
     */
    public function setAdminEmails(string $adminEmails): void
    {
        $this->adminEmails = $adminEmails;
    }


    /**
     * Gets earliest datestamp
     *
     * @return string
     */
    public function getEarliestDatestamp(): string
    {
        return $this->earliestDatestamp;
    }


    /**
     * Sets earliest datestamp
     *
     * @param string $earliestDatestamp
     * @return void
     */
    public function setEarliestDatestamp(string $earliestDatestamp): void
    {
        $this->earliestDatestamp = $earliestDatestamp;
    }


    /**
     * Gets deleted record
     *
     * @return string
     */
    public function getDeletedRecord(): string
    {
        return $this->deletedRecord;
    }


    /**
     * Sets deleted record
     *
     * @param string $deletedRecord
     * @return void
     */
    public function setDeletedRecord(string $deletedRecord): void
    {
        $this->deletedRecord = $deletedRecord;
    }


    /**
     * Gets granularity
     *
     * @return string
     */
    public function getGranularity(): string
    {
        return $this->granularity;
    }


    /**
     * Sets granularity
     *
     * @param string $granularity
     * @return void
     */
    public function setGranularity(string $granularity): void
    {
        $this->granularity = $granularity;
    }


    /**
     * Gets max list size
     *
     * @return int|null
     */
    public function getMaxListSize(): ?int
    {
        return $this->maxListSize;
    }


    /**
     * Sets max list size
     *
     * @param int|null $maxListSize
     * @return void
     */
    public function setMaxListSize(?int $maxListSize): void
    {
        $this->maxListSize = $maxListSize;
    }


    /**
     * Gets token duration
     *
     * @return int|null
     */
    public function getTokenDuration(): ?int
    {
        return $this->tokenDuration;
    }


    /**
     * Sets token duration
     *
     * @param int|null $tokenDuration
     * @return void
     */
    public function setTokenDuration(?int $tokenDuration): void
    {
        $this->tokenDuration = $tokenDuration;
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
        // Ignore empty or null values on insert (SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'updated' cannot be null)
        if ($updated === null || $updated === '') {
            return;
        }

        $this->updated = $updated;
    }


    /**
     * Gets comment
     *
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }


    /**
     * Sets comment
     *
     * @param string $comment
     * @return void
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

}