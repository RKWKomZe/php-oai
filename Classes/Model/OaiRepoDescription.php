<?php

namespace RKW\OaiConnector\Model;

/**
 * OaiRepoDescription
 *
 * Handles the repository's description along with its metadata, such as rank, update timestamps, and optional comments.
 */
class OaiRepoDescription
{

    /**
     * repo
     *
     * @var string
     */
    protected string $repo;


    /**
     * description
     *
     * @var string
     */
    protected string $description = '';


    /**
     * rank
     *
     * @var int
     */
    protected int $rank = 0;


    /**
     * updated
     *
     * @var string|null
     */
    protected ?string $updated = null;


    /**
     * comment
     *
     * @var string|null
     */
    protected ?string $comment = null;


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
     * Gets description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }


    /**
     * Sets description
     *
     * @param string $description
     * @return void
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


    /**
     * Gets rank
     *
     * @return int
     */
    public function getRank(): int
    {
        return $this->rank;
    }


    /**
     * Sets rank
     *
     * @param int $rank
     * @return void
     */
    public function setRank(int $rank): void
    {
        $this->rank = $rank;
    }


    /**
     * Gets updated
     *
     * @return string|null
     */
    public function getUpdated(): ?string
    {
        return $this->updated;
    }


    /**
     * Sets updated
     *
     * @param string|null $updated
     * @return void
     */
    public function setUpdated(?string $updated): void
    {
        $this->updated = $updated;
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

}
