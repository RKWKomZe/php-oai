<?php

namespace RKW\OaiConnector\Model;

class OaiSetDescription
{
    protected string $repo;
    protected string $setSpec;
    protected string $setDescription;
    protected int $rank = 0;
    protected ?string $updated = null;
    protected ?string $comment = null;

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

    public function getSetDescription(): string
    {
        return $this->setDescription;
    }

    public function setSetDescription(string $setDescription): void
    {
        $this->setDescription = $setDescription;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }

    public function getUpdated(): ?string
    {
        return $this->updated;
    }

    public function setUpdated(?string $updated): void
    {
        $this->updated = $updated;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }
}
