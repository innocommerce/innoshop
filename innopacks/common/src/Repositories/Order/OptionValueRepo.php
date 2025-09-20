<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Order;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InnoShop\Common\Models\Order\OptionValue;
use InnoShop\Common\Repositories\BaseRepo;

/**
 * 订单选项值仓库类
 */
class OptionValueRepo extends BaseRepo
{
    protected string $model = OptionValue::class;

    /**
     * 获取订单选项值列表
     */
    public function getOrderOptionValueList(array $filters = []): LengthAwarePaginator
    {
        $query = OptionValue::query()->with(['orderItem', 'option', 'optionValue']);

        $query = $this->buildOrderOptionValueFilters($query, $filters);

        return $query->orderBy('id', 'desc')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * 构建订单选项值查询过滤器
     *
     * @param  Builder  $query
     * @param  array  $filters
     * @return Builder
     */
    private function buildOrderOptionValueFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['order_item_id'])) {
            $query->where('order_item_id', $filters['order_item_id']);
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
     * 根据订单项目ID获取选项值
     *
     * @param  int  $orderItemId
     * @return Collection
     */
    public function getByOrderItemId(int $orderItemId): Collection
    {
        return OptionValue::where('order_item_id', $orderItemId)
            ->with(['option', 'optionValue'])
            ->orderBy('id')
            ->get();
    }

    /**
     * 批量创建订单选项值
     *
     * @param  int  $orderItemId
     * @param  array  $optionValues
     * @return bool
     * @throws Exception
     */
    public function createOrderOptionValues(int $orderItemId, array $optionValues): bool
    {
        foreach ($optionValues as $optionValue) {
            OptionValue::create([
                'order_item_id'     => $orderItemId,
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
     * 从购物车选项值创建订单选项值
     *
     * @param  int  $orderItemId
     * @param  Collection  $cartOptionValues
     * @return bool
     * @throws Exception
     */
    public function createFromCartOptionValues(int $orderItemId, Collection $cartOptionValues): bool
    {
        try {
            $optionValues = [];
            foreach ($cartOptionValues as $cartOptionValue) {
                $optionValues[] = [
                    'option_id'         => $cartOptionValue->option_id,
                    'option_value_id'   => $cartOptionValue->option_value_id,
                    'option_name'       => $cartOptionValue->option_name,
                    'option_value_name' => $cartOptionValue->option_value_name,
                    'price_adjustment'  => $cartOptionValue->price_adjustment,
                ];
            }

            return $this->createOrderOptionValues($orderItemId, $optionValues);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 删除订单项目的所有选项值
     *
     * @param  int  $orderItemId
     * @return bool
     */
    public function deleteByOrderItem(int $orderItemId): bool
    {
        return OptionValue::where('order_item_id', $orderItemId)->delete();
    }

    /**
     * 计算订单项目选项值的总价格调整
     *
     * @param  int  $orderItemId
     * @return float
     */
    public function getTotalPriceAdjustment(int $orderItemId): float
    {
        return OptionValue::where('order_item_id', $orderItemId)
            ->sum('price_adjustment');
    }

    /**
     * 获取订单项目的选项值组合
     *
     * @param  int  $orderItemId
     * @return array
     */
    public function getOptionValueCombination(int $orderItemId): array
    {
        $optionValues = OptionValue::where('order_item_id', $orderItemId)
            ->select('option_id', 'option_value_id', 'option_name', 'option_value_name')
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

    /**
     * 获取订单选项值的格式化显示
     *
     * @param  int  $orderItemId
     * @return array
     */
    public function getFormattedOptionValues(int $orderItemId): array
    {
        $optionValues = $this->getOptionValuesByOrderItem($orderItemId);

        $formatted = [];
        foreach ($optionValues as $optionValue) {
            $formatted[] = [
                'name'             => $optionValue->option_name,
                'value'            => $optionValue->option_value_name,
                'price_adjustment' => $optionValue->price_adjustment,
                'formatted_price'  => currency_format($optionValue->price_adjustment),
            ];
        }

        return $formatted;
    }
}
