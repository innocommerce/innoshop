<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Product;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InnoShop\Common\Models\Product\OptionValue;
use InnoShop\Common\Repositories\BaseRepo;

/**
 * 产品选项值配置仓库类
 */
class OptionValueRepo extends BaseRepo
{
    protected string $model = OptionValue::class;

    /**
     * 获取产品选项值配置列表
     *
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function getProductOptionValueList(array $filters = []): LengthAwarePaginator
    {
        $query = OptionValue::query()->with(['product', 'option', 'optionValue']);

        $query = $this->buildProductOptionValueFilters($query, $filters);

        return $query->orderBy('id', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * 构建产品选项值查询过滤器
     *
     * @param  Builder  $query
     * @param  array  $filters
     * @return Builder
     */
    private function buildProductOptionValueFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['option_id'])) {
            $query->where('option_id', $filters['option_id']);
        }

        if (isset($filters['option_value_id'])) {
            $query->where('option_value_id', $filters['option_value_id']);
        }

        if (isset($filters['subtract_stock'])) {
            $query->where('subtract_stock', $filters['subtract_stock']);
        }

        return $query;
    }

    /**
     * 根据产品ID获取选项值配置
     *
     * @param  int  $productId
     * @return Collection
     */
    public function getOptionValuesByProduct(int $productId): Collection
    {
        return OptionValue::where('product_id', $productId)
            ->with(['option', 'optionValue'])
            ->get();
    }

    /**
     * 根据产品ID和选项ID获取选项值配置
     *
     * @param  int  $productId
     * @param  int  $optionId
     * @return Collection
     */
    public function getOptionValuesByProductAndOption(int $productId, int $optionId): Collection
    {
        return OptionValue::where('product_id', $productId)
            ->where('option_id', $optionId)
            ->with(['optionValue'])
            ->get();
    }

    /**
     * 批量创建产品选项值配置
     *
     * @param  int  $productId
     * @param  array|string  $optionValues  支持数组或JSON字符串
     * @return bool
     * @throws Exception
     */
    public function createProductOptionValues(int $productId, $optionValues): bool
    {
        // If input is string, parse to array first
        if (is_string($optionValues)) {
            $optionValues = json_decode($optionValues, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON format for product options');
            }
        }

        // Ensure it's array type
        if (! is_array($optionValues)) {
            throw new Exception('Product options must be an array or valid JSON string');
        }

        // Delete existing configurations first
        OptionValue::where('product_id', $productId)->delete();

        // Also delete product option records
        \InnoShop\Common\Models\Product\Option::where('product_id', $productId)->delete();

        // Create new configurations - adapt to frontend data structure
        foreach ($optionValues as $optionConfig) {
            $optionId = $optionConfig['option_id'] ?? null;
            $values   = $optionConfig['values'] ?? [];

            if (! $optionId || ! is_array($values)) {
                continue;
            }

            // Get option required status from options table
            $option   = \InnoShop\Common\Models\Option::find($optionId);
            $required = $option ? $option->required : false;

            // Create product option record
            \InnoShop\Common\Models\Product\Option::create([
                'product_id' => $productId,
                'option_id'  => $optionId,
                'required'   => $required, // Use required status from options table
                'sort_order' => 0,         // Default sort order
            ]);

            // Handle each option value configuration
            foreach ($values as $valueConfig) {
                $optionValueId   = $valueConfig['option_value_id'] ?? null;
                $priceAdjustment = $valueConfig['price_adjustment'] ?? 0;
                $stockQuantity   = $valueConfig['stock_quantity'] ?? 0;

                if (! $optionValueId) {
                    continue;
                }

                OptionValue::create([
                    'product_id'       => $productId,
                    'option_id'        => $optionId,
                    'option_value_id'  => $optionValueId,
                    'price_adjustment' => $priceAdjustment,
                    'quantity'         => $stockQuantity,
                    'subtract_stock'   => $stockQuantity > 0, // Enable stock deduction if stock quantity exists
                ]);
            }
        }

        return true;
    }

    /**
     * 更新库存数量
     *
     * @param  int  $id
     * @param  int  $quantity
     * @return bool
     */
    public function updateQuantity(int $id, int $quantity): bool
    {
        return OptionValue::where('id', $id)
            ->update(['quantity' => $quantity]);
    }

    /**
     * 减少库存
     *
     * @param  int  $productId
     * @param  int  $optionId
     * @param  int  $optionValueId
     * @param  int  $quantity
     * @return bool
     */
    public function decreaseStock(int $productId, int $optionId, int $optionValueId, int $quantity): bool
    {
        $productOptionValue = OptionValue::where('product_id', $productId)
            ->where('option_id', $optionId)
            ->where('option_value_id', $optionValueId)
            ->first();

        if (! $productOptionValue) {
            return false;
        }

        return $productOptionValue->decreaseStock($quantity);
    }

    /**
     * 检查库存是否充足
     *
     * @param  int  $productId
     * @param  int  $optionId
     * @param  int  $optionValueId
     * @param  int  $quantity
     * @return bool
     */
    public function hasEnoughStock(int $productId, int $optionId, int $optionValueId, int $quantity): bool
    {
        $productOptionValue = OptionValue::where('product_id', $productId)
            ->where('option_id', $optionId)
            ->where('option_value_id', $optionValueId)
            ->first();

        if (! $productOptionValue || ! $productOptionValue->subtract_stock) {
            return true;
        }

        return $productOptionValue->quantity >= $quantity;
    }

    /**
     * 获取价格调整
     *
     * @param  int  $productId
     * @param  int  $optionId
     * @param  int  $optionValueId
     * @return float
     */
    public function getPriceAdjustment(int $productId, int $optionId, int $optionValueId): float
    {
        $productOptionValue = OptionValue::where('product_id', $productId)
            ->where('option_id', $optionId)
            ->where('option_value_id', $optionValueId)
            ->first();

        return $productOptionValue ? $productOptionValue->price_adjustment : 0;
    }

    /**
     * 删除产品选项值
     */
    public function deleteByProductId(int $productId): bool
    {
        return OptionValue::where('product_id', $productId)->delete();
    }

    /**
     * 增加库存
     */
    public function increaseStock(int $productId, array $optionIds, int $quantity): bool
    {
        $optionValue = OptionValue::where('product_id', $productId)
            ->whereIn('option_id', $optionIds)
            ->first();

        if (! $optionValue) {
            return false;
        }

        return $optionValue->increaseStock($quantity);
    }

    /**
     * 检查库存是否充足
     */
    public function checkStock(int $productId, array $optionIds, int $quantity): bool
    {
        $optionValue = OptionValue::where('product_id', $productId)
            ->whereIn('option_id', $optionIds)
            ->first();

        if (! $optionValue || ! $optionValue->subtract_stock) {
            return true;
        }

        return $optionValue->quantity >= $quantity;
    }
}
