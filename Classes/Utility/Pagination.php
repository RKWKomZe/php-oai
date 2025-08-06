<?php

namespace RKW\OaiConnector\Utility;

/**
 * Pagination
 *
 * Handles pagination logic for displaying limited data on multiple pages.
 */
class Pagination
{
    /**
     * ALLOWED_LIMITS
     * Defines the allowed number of items per page.
     *
     * @var int[]
     */
    public const ALLOWED_LIMITS = [25, 50, 100, 250, 500];

    /**
     * DEFAULT_LIMIT
     * Default number of items per page.
     *
     * @var int
     */
    public const DEFAULT_LIMIT = 25;

    /**
     * currentPage
     * The current page number in pagination.
     *
     * @var int
     */
    protected int $currentPage;

    /**
     * itemsPerPage
     * The number of items displayed per page.
     *
     * @var int
     */
    protected int $itemsPerPage;

    /**
     * totalItems
     * The total number of items available for pagination.
     *
     * @var int
     */
    protected int $totalItems;


    /**
     * constructor
     *
     * @param int $currentPage
     * @param int $itemsPerPage
     * @param int $totalItems
     */
    public function __construct(int $currentPage = 1, int $itemsPerPage = self::DEFAULT_LIMIT, int $totalItems = 0)
    {
        $this->currentPage = max(1, $currentPage);
        $this->itemsPerPage = in_array($itemsPerPage, self::ALLOWED_LIMITS, true) ? $itemsPerPage : self::DEFAULT_LIMIT;
        $this->totalItems = max(0, $totalItems);
    }


    /**
     * Gets the limit of items per page.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->itemsPerPage;
    }


    /**
     * Calculates the offset for the current page.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }


    /**
     * Get the current page number.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }


    /**
     * Gets the number of items displayed per page.
     *
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * Gets the total number of items available for pagination.
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * Sets the total number of items for pagination.
     * Ensures the value is not negative.
     *
     * @param int $totalItems
     * @return void
     */
    public function setTotalItems(int $totalItems): void
    {
        $this->totalItems = max(0, $totalItems);
    }

    /**
     * Calculates the total number of pages based on total items and items per page.
     *
     * @return int
     *
     * @throws \LogicException If itemsPerPage is zero or less.
     */
    public function getTotalPages(): int
    {
        if ($this->itemsPerPage <= 0) {
            throw new \LogicException('Pagination error: itemsPerPage must be greater than zero.');
        }
        return max(1, (int)ceil($this->totalItems / $this->itemsPerPage));
    }

    /**
     * Checks whether a previous page exists.
     *
     * @return bool True if there is a previous page, false otherwise.
     */
    public function hasPrevious(): bool
    {
        return $this->currentPage > 1;
    }

    /**
     * Checks whether a next page exists.
     *
     * @return bool True if there is a next page, false otherwise.
     */
    public function hasNext(): bool
    {
        return $this->currentPage < $this->getTotalPages();
    }

    /**
     * Builds a URL for the given target page while keeping current GET parameters.
     *
     * @param int $targetPage Target page number.
     * @return string URL for the requested page.
     */
    public function renderPageLink(int $targetPage): string
    {
        // Apply current GET parameters
        $params = $_GET;

        // Set landing page
        $params['page'] = $targetPage;

        // Compose URL
        return '?' . http_build_query($params);
    }

    /**
     * Gets the list of allowed pagination limits.
     *
     * @return int[] Array of allowed limits.
     */
    public function getAllowedLimits(): array
    {
        return self::ALLOWED_LIMITS;
    }

}
