<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PaginatorService
{
    /**
     * @param  array  $items
     * @param  int  $perPage
     * @param  string  $pageName
     * @return LengthAwarePaginator
     */
    public function makePaginator(array $items, int $perPage = 8, string $pageName = 'page'): LengthAwarePaginator
    {
        $currentPage      = Paginator::resolveCurrentPage() ?: 1;
        $total            = count($items);
        $offset           = ($currentPage - 1) * $perPage;
        $currentPageItems = array_slice($items, $offset, $perPage);

        return new LengthAwarePaginator($currentPageItems, $total, $perPage, $currentPage, [
            'path'     => LengthAwarePaginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
}
