<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Product;

class VariantRepo
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @param  $originData
     * @param  $newData
     * @return mixed
     */
    public function mergeVariant($originData, $newData): mixed
    {
        if (empty($originData)) {
            return $newData;
        }

        if (empty($newData)) {
            return $originData;
        }

        // 创建原始变体的索引
        $variantIndex = [];
        foreach ($originData as $index => &$variant) {
            $variantName                = $variant['name']['en'] ?? '';
            $variantIndex[$variantName] = $index;
        }

        $result = $originData;
        foreach ($newData as $newVariant) {
            $newVariantName = $newVariant['name']['en'] ?? '';

            // 使用索引直接定位变体
            if (isset($variantIndex[$newVariantName])) {
                $index = $variantIndex[$newVariantName];

                // 更新基本信息
                $result[$index]['name'] = $newVariant['name'];

                // 为 values 创建索引
                $valueIndex = [];
                foreach ($result[$index]['values'] as $vIndex => &$value) {
                    $valueName              = $value['name']['en'] ?? '';
                    $valueIndex[$valueName] = $vIndex;
                }

                // 更新或添加新的 values
                foreach ($newVariant['values'] as $newValue) {
                    $newValueName = $newValue['name']['en'] ?? '';
                    if (isset($valueIndex[$newValueName])) {
                        $result[$index]['values'][$valueIndex[$newValueName]] = array_replace_recursive(
                            $result[$index]['values'][$valueIndex[$newValueName]],
                            $newValue
                        );
                    } else {
                        $result[$index]['values'][] = $newValue;
                    }
                }
            } else {
                $result[]                      = $newVariant;
                $variantIndex[$newVariantName] = count($result) - 1;
            }
        }

        return $result;
    }
}
