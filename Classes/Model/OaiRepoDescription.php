<?php

namespace RKW\OaiConnector\Model;

class OaiRepoDescription
{
    protected string $repo;
    protected string $description;
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
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
