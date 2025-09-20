<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Cart;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InnoShop\Common\Models\Cart\OptionValue;
use InnoShop\Common\Repositories\BaseRepo;

/**
 * 购物车选项值仓库类
 */
class OptionValueRepo extends BaseRepo
{
    protected string $model = OptionValue::class;

    /**
     * 获取购物车选项值列表
     */
    public function getCartOptionValueList(array $filters = []): LengthAwarePaginator
    {
        $query = OptionValue::query()->with(['cartItem', 'option', 'optionValue']);

        $query = $this->buildCartOptionValueFilters($query, $filters);

        return $query->orderBy('id', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * 构建购物车选项值查询过滤器
     */
    private function buildCartOptionValueFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['cart_item_id'])) {
            $query->where('cart_item_id', $filters['cart_item_id']);
        }

        if (isset($filters['option_id'])) {
            $query->where('option_id', $filters['option_id']);
        }

        if (isset($filters['option_value_id'])) {
            $query->where('option_value_id', $filters['option_value_id']);
        }

        return $query;
    }

    /**
     * 根据购物车商品ID获取选项值
     */
    public function getByCartItemId(int $cartItemId): Collection
    {
        return OptionValue::where('cart_item_id', $cartItemId)
            ->with(['option', 'optionValue'])
            ->orderBy('id')
            ->get();
    }

    /**
     * 批量创建购物车选项值
     */
    public function createCartOptionValues(int $cartItemId, array $optionValues): bool
    {
        // Delete existing option values first
        OptionValue::where('cart_item_id', $cartItemId)->delete();

        foreach ($optionValues as $optionValue) {
            OptionValue::create([
                'cart_item_id'      => $cartItemId,
                'option_id'         => $optionValue['option_id'],
                'option_value_id'   => $optionValue['option_value_id'],
                'option_name'       => $optionValue['option_name'] ?? '',
                'option_value_name' => $optionValue['option_value_name'] ?? '',
                'price_adjustment'  => $optionValue['price_adjustment'] ?? 0,
            ]);
        }

        return true;
    }

    /**
     * 更新购物车选项值
     */
    public function updateCartOptionValues(int $cartItemId, array $optionValues): bool
    {
        return $this->createCartOptionValues($cartItemId, $optionValues);
    }

    /**
     * 删除购物车选项值
     */
    public function deleteByCartItemId(int $cartItemId): bool
    {
        return OptionValue::where('cart_item_id', $cartItemId)->delete();
    }

    /**
     * 计算购物车选项值的总价格调整
     */
    public function calculatePriceAdjustment(int $cartItemId): float
    {
        return OptionValue::where('cart_item_id', $cartItemId)
            ->sum('price_adjustment');
    }

    /**
     * 获取购物车选项值的组合
     */
    public function getOptionCombination(int $cartItemId): array
    {
        return OptionValue::where('cart_item_id', $cartItemId)
            ->pluck('option_value_id')
            ->toArray();
    }

    /**
     * 格式化显示购物车选项值
     */
    public function formatForDisplay(int $cartItemId): array
    {
        $optionValues = OptionValue::where('cart_item_id', $cartItemId)
            ->with(['option', 'optionValue'])
            ->get();

        $combination = [];
        foreach ($optionValues as $optionValue) {
            $combination[] = [
                'option_id'         => $optionValue->option_id,
                'option_value_id'   => $optionValue->option_value_id,
                'option_name'       => $optionValue->option_name,
                'option_value_name' => $optionValue->option_value_name,
            ];
        }

        return $combination;
    }
}
