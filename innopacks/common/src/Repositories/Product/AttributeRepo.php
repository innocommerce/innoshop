<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Product;

use InnoShop\Common\Models\Product;
use InnoShop\Common\Repositories\BaseRepo;

class AttributeRepo extends BaseRepo
{
    /**
     * @return array
     */
    public function getItems(): array
    {
        $productAttributes = Product\Attribute::query()->with([
            'attribute.translation',
            'attributeValue.translation',
        ])
            ->select(['attribute_id', 'attribute_value_id'])
            ->distinct()
            ->get();

        $attributes = $values = [];
        foreach ($productAttributes as $item) {
            $attributeName      = $item->attribute->translation->name;
            $attributeValueName = $item->attributeValue->translation->name;

            $values[$item->attribute_id][] = [
                'value_id'   => $item->attribute_value_id,
                'value_name' => $attributeValueName,
            ];
            if (! isset($attributes[$item->attribute_id])) {
                $attributes[$item->attribute_id] = [
                    'attribute_id'   => $item->attribute_id,
                    'attribute_name' => $attributeName,
                ];
            }
            $attributes[$item->attribute_id]['values'] = $values[$item->attribute_id];
        }

        return array_values($attributes);
    }
}
