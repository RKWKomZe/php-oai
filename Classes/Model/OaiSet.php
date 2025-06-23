<?php

namespace RKW\OaiConnector\Model;

class OaiSet
{
    protected string $repo;
    protected string $setSpec;
    protected string $setName;
    protected int $rank = 0;
    protected ?string $comment = null;
    protected ?string $updated = null; // ISO-Timestamp, von DB gesetzt

    public function getRepo(): string
    {
        return $this->repo;
    }

    public function setRepo(string $repo): void
    {
        $this->repo = $repo;
    }

    public function getSetSpec(): string
    {
        return $this->setSpec;
    }

    public function setSetSpec(string $setSpec): void
    {
        $this->setSpec = $setSpec;
    }

    public function getSetName(): string
    {
        return $this->setName;
    }

    public function setSetName(string $setName): void
    {
        $this->setName = $setName;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getUpdated(): ?string
    {
        return $this->updated;
    }

    public function setUpdated(?string $updated): void
    {
        $this->updated = $updated;
    }
}
