<?php

namespace RKW\OaiConnector\Model;

/**
 * OaiSetDescription
 *
 * Represents the description of an OAI set.
 */
class OaiSetDescription
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
    protected string $setSpec;

    /**
     * setDescription
     *
     * @var string
     */
    protected string $setDescription;

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
     * Gets setDescription
     *
     * @return string
     */
    public function getSetDescription(): string
    {
        return $this->setDescription;
    }

    /**
     * Sets setDescription
     *
     * @param string $setDescription
     * @return void
     */
    public function setSetDescription(string $setDescription): void
    {
        $this->setDescription = $setDescription;
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
