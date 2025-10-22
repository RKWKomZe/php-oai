<?php

namespace RKW\OaiConnector\Model;

/**
 * OaiSet
 *
 * Represents an OAI Set entity with properties for repository, set specification, name, rank, comment, and update timestamp.
 */
class OaiSet
{
    /**
     * repo
     *
     * @var string
     */
    protected string $repo;

    /**
     * setSpec
     *
     * @var string
     */
    protected string $setSpec = '';

    /**
     * setName
     *
     * @var string
     */
    protected string $setName = '';

    /**
     * rank
     *
     * @var int
     */
    protected int $rank = 0;

    /**
     * comment
     *
     * @var string|null
     */
    protected ?string $comment = null;

    /**
     * updated
     * ISO-Timestamp, set by DB
     *
     * @var string|null
     */
    protected ?string $updated = null;


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
     * Gets setSpec
     *
     * @return string
     */
    public function getSetSpec(): string
    {
        return $this->setSpec;
    }

    /**
     * Sets setSpec
     *
     * @param string $setSpec
     * @return void
     */
    public function setSetSpec(string $setSpec): void
    {
        $this->setSpec = $setSpec;
    }


    /**
     * Gets setName
     *
     * @return string
     */
    public function getSetName(): string
    {
        return $this->setName;
    }

    /**
     * Sets setName
     *
     * @param string $setName
     * @return void
     */
    public function setSetName(string $setName): void
    {
        $this->setName = $setName;
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

}
