<?php

namespace RKW\OaiConnector\Model;

class OaiMeta
{
    protected string $repo = '1';
    protected string $metadataPrefix = '';
    protected string $schema = '';
    protected string $metadataNamespace = '';
    protected ?string $comment = null;
    protected string $updated = '';

    public function getRepo(): string
    {
        return $this->repo;
    }

    public function setRepo(string $repo): void
    {
        $this->repo = $repo;
    }

    public function getMetadataPrefix(): string
    {
        return $this->metadataPrefix;
    }

    public function setMetadataPrefix(string $metadataPrefix): void
    {
        $this->metadataPrefix = $metadataPrefix;
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function setSchema(string $schema): void
    {
        $this->schema = $schema;
    }

    public function getMetadataNamespace(): string
    {
        return $this->metadataNamespace;
    }

    public function setMetadataNamespace(string $metadataNamespace): void
    {
        $this->metadataNamespace = $metadataNamespace;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getUpdated(): string
    {
        return $this->updated;
    }

    public function setUpdated(string $updated): void
    {
        $this->updated = $updated;
    }
}
