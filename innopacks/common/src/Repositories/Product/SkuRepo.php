<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Product;

use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Common\Repositories\BaseRepo;

class SkuRepo extends BaseRepo
{
    /**
     * @param  $code
     * @return Sku|null
     */
    public function getSkuByCode($code): ?Sku
    {
        return Sku::query()->where('code', $code)->first();
    }

    /**
     * @param  $code
     * @return mixed|null
     */
    public function getProductByCode($code): ?Product
    {
        return Sku::query()->where('code', $code)->first()->product ?? null;
    }

    /**
     * Create a query builder for SKUs.
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Sku::query()->with('product.translation');

        $keyword = $filters['keyword'] ?? '';
        if ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('code', 'like', "%{$keyword}%")
                    ->orWhereHas('product.translation', function ($query) use ($keyword) {
                        $query->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        return $builder;
    }

    /**
     * Search SKUs by keyword.
     *
     * @param  ?string  $keyword
     * @return \Illuminate\Support\Collection
     */
    public function searchByKeyword(?string $keyword)
    {
        return $this->builder(['keyword' => $keyword])->get();
    }
}
