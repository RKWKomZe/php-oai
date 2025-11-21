<?php

namespace RKW\OaiConnector\Model;

/**
 * OaiMeta
 *
 * @var string
 */
class OaiMeta
{

    /**
     * repo
     *
     * @var string
     */
    protected string $repo = '1';


    /**
     * metadataPrefix
     *
     * @var string
     */
    protected string $metadataPrefix = '';


    /**
     * schema
     *
     * @var string
     */
    protected string $schema = '';


    /**
     * metadataNamespace
     *
     * @var string
     */
    protected string $metadataNamespace = '';


    /**
     * comment
     *
     * @var string
     */
    protected string $comment = '';


    /**
     * updated
     *
     * @var string
     */
    protected string $updated = '';


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
     * Gets schema
     *
     * @return string
     */
    public function getSchema(): string
    {
        return $this->schema;
    }


    /**
     * Sets schema
     *
     * @param string $schema
     * @return void
     */
    public function setSchema(string $schema): void
    {
        $this->schema = $schema;
    }


    /**
     * Gets metadata namespace
     *
     * @return string
     */
    public function getMetadataNamespace(): string
    {
        return $this->metadataNamespace;
    }


    /**
     * Sets metadata namespace
     *
     * @param string $metadataNamespace
     * @return void
     */
    public function setMetadataNamespace(string $metadataNamespace): void
    {
        $this->metadataNamespace = $metadataNamespace;
    }


    /**
     * Gets comment
     *
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Sets comment
     *
     * @param string|null $comment
     * @return void
     */
    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
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
