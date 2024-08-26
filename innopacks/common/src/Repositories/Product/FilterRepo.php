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
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Repositories\BrandRepo;
use InnoShop\Common\Resources\BrandSimple;

class FilterRepo
{
    private ?Builder $builder;

    /**
     * @param  Builder  $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param  Builder  $builder
     * @return static
     */
    public static function getInstance(Builder $builder): static
    {
        return new static($builder);
    }

    /**
     * @return mixed
     */
    public function getCurrentFilters(): mixed
    {
        $filters = [
            'stock'      => $this->getStockStatusTotal(),
            'brands'     => $this->getBrandTotal(),
            'attributes' => $this->getAttributeTotal(),
            'variants'   => $this->getVariantTotal(),
        ];

        return fire_hook_filter('common.repo.product.filters', $filters);
    }

    /**
     * @return array
     */
    private function getStockStatusTotal(): array
    {
        return [
            ['code' => 'in_stock', 'name' => front_trans('common.in_stock')],
            ['code' => 'out_of_stock', 'name' => front_trans('common.out_of_stock')],
        ];
    }

    /**
     * @return array
     */
    private function getBrandTotal(): array
    {
        $brands = BrandSimple::collection(BrandRepo::getInstance()->all())->jsonSerialize();

        $builder = clone $this->builder;
        $totals  = $builder->forPage(1)
            ->select('brand_id', DB::raw('count(*) as total'))
            ->groupBy('brand_id')
            ->pluck('total', 'brand_id');

        $result = [];
        foreach ($brands as $brand) {
            $brand['name'] = html_entity_decode($brand['name']);
            $total         = $totals[$brand['id']] ?? 0;
            if ($total) {
                $brand['total'] = $total;
                $result[]       = $brand;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getAttributeTotal(): array
    {
        $attributes = AttributeRepo::getInstance()->getItems();
        $builder    = clone $this->builder;
        $totals     = $builder->forPage(1)
            ->join('product_attributes as pa', 'products.id', '=', 'pa.product_id')
            ->select('pa.attribute_id', DB::raw('count(*) as total'))
            ->where('pa.attribute_value_id', '<>', 0)
            ->groupBy('pa.attribute_id')
            ->pluck('total', 'attribute_id');

        $result = [];
        foreach ($attributes as $item) {
            if (! isset($totals[$item['attribute_id']])) {
                continue;
            }
            $item['total'] = $totals[$item['attribute_id']];
            $result[]      = $item;
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getVariantTotal(): array
    {
        return [];
    }
}
