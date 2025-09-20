<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Support\Collection;
use InnoShop\Common\Models\Option;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Option as ProductOption;

/**
 * 产品选项服务类
 */
class ProductOptionService
{
    private Product $product;

    /**
     * 构造函数
     *
     * @param  Product  $product
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * 获取服务实例
     *
     * @param  Product  $product
     * @return static
     */
    public static function getInstance(Product $product): static
    {
        return new static($product);
    }

    /**
     * 获取产品的所有选项组
     *
     * @return Collection
     */
    public function getOptionGroups(): Collection
    {
        return ProductOption::where('product_id', $this->product->id)
            ->with(['option' => function ($query) {
                $query->active()->ordered()->with(['optionValues' => function ($subQuery) {
                    $subQuery->active()->ordered();
                }]);
            }])
            ->orderBy('position')
            ->get()
            ->map(function ($productOption) {
                $option = $productOption->option;
                if ($option) {
                    // 使用 Option 模型的 required 属性，而不是 ProductOption 的 required 属性
                    $option->setAttribute('is_required', $option->required);
                    $option->setAttribute('product_option_id', $productOption->id);
                }

                return $option;
            })
            ->filter();
    }

    /**
     * 验证选项选择是否有效
     *
     * @param  array  $selectedOptions  格式: [option_group_id => option_id, ...]
     * @return array 返回验证结果和错误信息
     */
    public function validateOptions(array $selectedOptions): array
    {
        $optionGroups = $this->getOptionGroups();
        $errors       = [];

        // 检查必选项是否都已选择
        foreach ($optionGroups as $group) {
            if ($group->isRequired() && ! isset($selectedOptions[$group->id])) {
                $errors[] = "选项组 '{$group->current_name}' 是必选的";

                continue;
            }

            // 如果选择了该组的选项，验证选项是否有效
            if (isset($selectedOptions[$group->id])) {
                $selectedOptionIds = is_array($selectedOptions[$group->id])
                    ? $selectedOptions[$group->id]
                    : [$selectedOptions[$group->id]];

                // 单选类型只能选择一个选项
                if ($group->isSingleType() && count($selectedOptionIds) > 1) {
                    $errors[] = "选项组 '{$group->current_name}' 只能选择一个选项";

                    continue;
                }

                // 验证选项是否存在且有效
                $validOptionIds = $group->optionValues->pluck('id')->toArray();
                foreach ($selectedOptionIds as $optionId) {
                    if (! in_array($optionId, $validOptionIds)) {
                        $errors[] = "选项组 '{$group->current_name}' 中的选项值 ID {$optionId} 无效";

                        continue;
                    }

                    // 检查选项值库存
                    $productOptionValue = \InnoShop\Common\Models\Product\OptionValue::where('product_id', $this->product->id)
                        ->where('option_id', $group->id)
                        ->where('option_value_id', $optionId)
                        ->first();

                    if ($productOptionValue && $productOptionValue->quantity <= 0) {
                        $optionValueName = $group->optionValues->firstWhere('id', $optionId)->current_name ?? '';
                        $errors[]        = "选项组 '{$group->current_name}' 中的选项值 '{$optionValueName}' 库存不足";
                    }
                }
            }
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * 计算选项的总加价
     *
     * @param  array  $selectedOptions  格式: [option_group_id => option_id, ...]
     * @param  float  $basePrice  基础价格
     * @return float
     */
    public function calculateOptionsPrice(array $selectedOptions, float $basePrice = 0): float
    {
        if (empty($selectedOptions)) {
            return 0;
        }

        $totalPrice = 0;
        $optionIds  = [];

        // 收集所有选中的选项ID
        foreach ($selectedOptions as $groupId => $optionId) {
            if (is_array($optionId)) {
                $optionIds = array_merge($optionIds, $optionId);
            } else {
                $optionIds[] = $optionId;
            }
        }

        // 获取所有选中的选项
        $options = Option::whereIn('id', $optionIds)->active()->get();

        // 计算总价格
        foreach ($options as $option) {
            $totalPrice += $option->calculatePrice($basePrice);
        }

        return $totalPrice;
    }

    /**
     * 获取选项的详细信息
     *
     * @param  array  $selectedOptions  格式: [option_group_id => option_id, ...]
     * @return array
     */
    public function getOptionsDetails(array $selectedOptions): array
    {
        if (empty($selectedOptions)) {
            return [];
        }

        $details      = [];
        $optionGroups = $this->getOptionGroups()->keyBy('id');

        foreach ($selectedOptions as $groupId => $optionId) {
            if (! isset($optionGroups[$groupId])) {
                continue;
            }

            $group             = $optionGroups[$groupId];
            $selectedOptionIds = is_array($optionId) ? $optionId : [$optionId];

            $groupDetails = [
                'group_id'   => $groupId,
                'group_name' => $group->name,
                'group_type' => $group->type,
                'options'    => [],
            ];

            foreach ($selectedOptionIds as $singleOptionId) {
                $option = $group->options->firstWhere('id', $singleOptionId);
                if ($option) {
                    $groupDetails['options'][] = [
                        'option_id'    => $option->id,
                        'option_name'  => $option->name,
                        'price_type'   => $option->price_type,
                        'price'        => $option->price,
                        'price_format' => $option->price_format,
                    ];
                }
            }

            if (! empty($groupDetails['options'])) {
                $details[] = $groupDetails;
            }
        }

        return $details;
    }

    /**
     * 格式化选项显示文本
     *
     * @param  array  $selectedOptions  格式: [option_group_id => option_id, ...]
     * @return string
     */
    public function formatOptionsText(array $selectedOptions): string
    {
        $details = $this->getOptionsDetails($selectedOptions);
        $texts   = [];

        foreach ($details as $groupDetail) {
            $optionNames = array_column($groupDetail['options'], 'option_name');
            $texts[]     = $groupDetail['group_name'].': '.implode(', ', $optionNames);
        }

        return implode('; ', $texts);
    }

    /**
     * 将选项信息转换为订单项选项格式
     *
     * @param  array  $selectedOptions  格式: [option_group_id => option_id, ...]
     * @param  float  $basePrice  基础价格
     * @return array
     */
    public function convertToOrderItemOptions(array $selectedOptions, float $basePrice = 0): array
    {
        $details          = $this->getOptionsDetails($selectedOptions);
        $orderItemOptions = [];

        foreach ($details as $groupDetail) {
            foreach ($groupDetail['options'] as $optionDetail) {
                $option      = Option::find($optionDetail['option_id']);
                $optionPrice = $option ? $option->calculatePrice($basePrice) : 0;

                $orderItemOptions[] = [
                    'option_group_id'   => $groupDetail['group_id'],
                    'option_id'         => $optionDetail['option_id'],
                    'option_group_name' => $groupDetail['group_name'],
                    'option_name'       => $optionDetail['option_name'],
                    'option_price'      => $optionPrice,
                ];
            }
        }

        return $orderItemOptions;
    }
}
