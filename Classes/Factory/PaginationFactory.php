<?php

namespace RKW\OaiConnector\Factory;

use RKW\OaiConnector\Utility\Pagination;

class PaginationFactory
{
    /**
     * Creates a Pagination object based on page and limit values.
     *
     * @param int|null $page  The current page number from the request (1-based)
     * @param int|null $limit Number of items per page from the request
     * @return Pagination
     */
    public static function fromRequestValues(?int $page = null, ?int $limit = null): Pagination
    {
        // Use fallback from $_GET if values are not explicitly passed
        $resolvedPage = $page ?? (isset($_GET['page']) ? (int)$_GET['page'] : 1);
        $resolvedLimit = $limit ?? (isset($_GET['limit']) ? (int)$_GET['limit'] : Pagination::DEFAULT_LIMIT);

        $resolvedPage = max(1, $resolvedPage);
        $resolvedLimit = max(1, $resolvedLimit);

        // fallback
        if (!in_array($resolvedLimit, Pagination::ALLOWED_LIMITS, true)) {
            $resolvedLimit = Pagination::DEFAULT_LIMIT;
        }

        return new Pagination($resolvedPage, $resolvedLimit);
    }
}
