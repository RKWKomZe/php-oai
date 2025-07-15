<?php

namespace RKW\OaiConnector\Utility;


class Pagination
{
    public const ALLOWED_LIMITS = [1, 25, 50, 100, 250, 500];
    public const DEFAULT_LIMIT = 25;

    protected int $currentPage;
    protected int $itemsPerPage;
    protected int $totalItems;

    public function __construct(int $currentPage = 1, int $itemsPerPage = self::DEFAULT_LIMIT, int $totalItems = 0)
    {
        $this->currentPage = max(1, $currentPage);
        $this->itemsPerPage = in_array($itemsPerPage, self::ALLOWED_LIMITS, true) ? $itemsPerPage : self::DEFAULT_LIMIT;
        $this->totalItems = max(0, $totalItems);
    }

    public function getLimit(): int
    {
        return $this->itemsPerPage;
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    public function setTotalItems(int $totalItems): void
    {
        $this->totalItems = max(0, $totalItems);
    }

    public function getTotalPages(): int
    {
        if ($this->itemsPerPage <= 0) {
            throw new \LogicException('Pagination error: itemsPerPage must be greater than zero.');
        }
        return max(1, (int)ceil($this->totalItems / $this->itemsPerPage));
    }

    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNext(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }


    public function renderPageLink(int $targetPage): string
    {
        // Aktuelle GET-Parameter übernehmen
        $params = $_GET;

        // Zielseite setzen
        $params['page'] = $targetPage;

        // URL zusammensetzen
        return '?' . http_build_query($params);
    }


    public function getAllowedLimits(): array
    {
        return self::ALLOWED_LIMITS;
    }
}
