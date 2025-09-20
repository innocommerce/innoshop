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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InnoShop\Common\Models\Product\Option;
use InnoShop\Common\Repositories\BaseRepo;

/**
 * 产品选项关联仓库类
 */
class OptionRepo extends BaseRepo
{
    protected string $model = Option::class;

    /**
     * 获取产品选项列表
     */
    public function getProductOptionList(array $filters = []): LengthAwarePaginator
    {
        $query = Option::query()->with(['product', 'option']);

        $query = $this->buildProductOptionFilters($query, $filters);

        return $query->orderBy('id', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * 构建产品选项查询过滤器
     *
     * @param  Builder  $query
     * @param  array  $filters
     * @return Builder
     */
    private function buildProductOptionFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['option_id'])) {
            $query->where('option_id', $filters['option_id']);
        }

        if (isset($filters['required'])) {
            $query->where('required', $filters['required']);
        }

        return $query;
    }

    /**
     * 根据产品ID获取选项
     */
    public function getByProductId(int $productId): Collection
    {
        $query = Option::where('product_id', $productId)
            ->with(['option'])
            ->orderBy('sort_order');

        return $query->get();
    }

    /**
     * 根据选项ID获取产品
     */
    public function getProductsByOptionId(int $optionId): Collection
    {
        return Option::where('option_id', $optionId)
            ->with(['product'])
            ->get();
    }

    /**
     * 批量创建产品选项
     */
    public function createProductOptions(int $productId, array $options): bool
    {
        // 先删除现有选项
        Option::where('product_id', $productId)->delete();

        // 创建新选项
        foreach ($options as $option) {
            Option::create([
                'product_id' => $productId,
                'option_id'  => $option['option_id'],
                'required'   => $option['required'] ?? false,
                'sort_order' => $option['sort_order'] ?? 0,
            ]);
        }

        return true;
    }

    /**
     * 删除产品选项
     */
    public function deleteByProductId(int $productId): bool
    {
        Option::where('product_id', $productId)
            ->delete();

        return true;
    }

    /**
     * 检查产品是否有选项
     */
    public function hasOptions(int $productId): bool
    {
        return Option::where('product_id', $productId)
            ->exists();
    }
}
