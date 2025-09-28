<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use InnoShop\Common\Models\Attribute;
use InnoShop\Common\Models\Attribute\Group as AttributeGroup;
use InnoShop\Common\Models\Attribute\Group\Translation as AttributeGroupTranslation;
use InnoShop\Common\Models\Attribute\Translation as AttributeTranslation;
use InnoShop\Common\Models\Attribute\Value as AttributeValue;
use InnoShop\Common\Models\Attribute\Value\Translation as AttributeValueTranslation;
use InnoShop\Common\Models\Product\Attribute as ProductAttribute;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AttributeGroup::query()->truncate();
        AttributeGroupTranslation::query()->truncate();
        Attribute::query()->truncate();
        AttributeTranslation::query()->truncate();
        AttributeValue::query()->truncate();
        AttributeValueTranslation::query()->truncate();
        ProductAttribute::query()->truncate();

        // Attribute Group
        $attributeGroupsNumber = 4;
        for ($i = 1; $i <= $attributeGroupsNumber; $i++) {
            AttributeGroup::query()->create([
                'position' => $i,
            ]);
        }

        //  Attribute Group Translation
        $items = $this->getGroupTranslations();
        AttributeGroupTranslation::query()->insert(
            collect($items)->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );

        // Attributes
        $items = $this->getAttributes();
        Attribute::query()->insert(
            collect($items)->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );

        // Attribute Translations
        $items = $this->getAttributeTranslations();
        AttributeTranslation::query()->insert(
            collect($items)->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );

        // Attribute Values
        $items = $this->getAttributeValues();
        AttributeValue::query()->insert(
            collect($items)->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );

        // Attribute Value Translations
        $items = $this->getAttributeValueTranslations();
        AttributeValueTranslation::query()->insert(
            collect($items)->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );

        // Product Attribute Relations
        $items = $this->productAttributes();
        ProductAttribute::query()->insert(
            collect($items)->map(function ($item) {
                $item['created_at'] = now();
                $item['updated_at'] = now();

                return $item;
            })->toArray()
        );
    }

    private function getGroupTranslations(): array
    {
        return [
            ['attribute_group_id' => 1, 'locale' => 'zh-cn', 'name' => '默认'],
            ['attribute_group_id' => 1, 'locale' => 'en', 'name' => 'Default'],
            ['attribute_group_id' => 2, 'locale' => 'zh-cn', 'name' => '衣服'],
            ['attribute_group_id' => 2, 'locale' => 'en', 'name' => 'Clothing'],
            ['attribute_group_id' => 3, 'locale' => 'zh-cn', 'name' => '运动'],
            ['attribute_group_id' => 3, 'locale' => 'en', 'name' => 'Sport'],
            ['attribute_group_id' => 4, 'locale' => 'zh-cn', 'name' => '配饰'],
            ['attribute_group_id' => 4, 'locale' => 'en', 'name' => 'Accessory'],
        ];
    }

    private function getAttributes(): array
    {
        return [
            ['attribute_group_id' => 2, 'category_id' => 1, 'position' => 0], // Features
            ['attribute_group_id' => 2, 'category_id' => 1, 'position' => 0], // Fabric
            ['attribute_group_id' => 2, 'category_id' => 1, 'position' => 0], // Style
        ];
    }

    private function getAttributeTranslations(): array
    {
        return [
            ['attribute_id' => 1, 'locale' => 'zh-cn', 'name' => '功能'],
            ['attribute_id' => 1, 'locale' => 'en', 'name' => 'Features'],
            ['attribute_id' => 2, 'locale' => 'zh-cn', 'name' => '面料'],
            ['attribute_id' => 2, 'locale' => 'en', 'name' => 'Fabric'],
            ['attribute_id' => 3, 'locale' => 'zh-cn', 'name' => '样式'],
            ['attribute_id' => 3, 'locale' => 'en', 'name' => 'Style'],
        ];
    }

    private function getAttributeValues(): array
    {
        return [
            ['attribute_id' => 2], // Cotton
            ['attribute_id' => 2], // Linen
            ['attribute_id' => 1], // Waterproof
            ['attribute_id' => 3], // Crew neck
            ['attribute_id' => 2], // Silk
            ['attribute_id' => 2], // Wool
            ['attribute_id' => 2], // Synthetic fiber
            ['attribute_id' => 3], // Collarless
            ['attribute_id' => 3], // Short-sleeve
            ['attribute_id' => 3], // T-shirt
            ['attribute_id' => 1], // Thermal
            ['attribute_id' => 1], // UV protection
        ];
    }

    private function getAttributeValueTranslations(): array
    {
        return [
            ['attribute_value_id' => 1, 'locale' => 'zh-cn', 'name' => '棉'],
            ['attribute_value_id' => 1, 'locale' => 'en', 'name' => 'Cotton'],
            ['attribute_value_id' => 2, 'locale' => 'zh-cn', 'name' => '麻'],
            ['attribute_value_id' => 2, 'locale' => 'en', 'name' => 'Linen'],
            ['attribute_value_id' => 3, 'locale' => 'zh-cn', 'name' => '防水'],
            ['attribute_value_id' => 3, 'locale' => 'en', 'name' => 'Waterproof'],
            ['attribute_value_id' => 4, 'locale' => 'zh-cn', 'name' => '圆领'],
            ['attribute_value_id' => 4, 'locale' => 'en', 'name' => 'Crew neck'],
            ['attribute_value_id' => 5, 'locale' => 'zh-cn', 'name' => '丝'],
            ['attribute_value_id' => 5, 'locale' => 'en', 'name' => 'Silk'],
            ['attribute_value_id' => 6, 'locale' => 'zh-cn', 'name' => '毛'],
            ['attribute_value_id' => 6, 'locale' => 'en', 'name' => 'Wool'],
            ['attribute_value_id' => 7, 'locale' => 'zh-cn', 'name' => '化纤'],
            ['attribute_value_id' => 7, 'locale' => 'en', 'name' => 'Synthetic fiber'],
            ['attribute_value_id' => 8, 'locale' => 'zh-cn', 'name' => '无领'],
            ['attribute_value_id' => 8, 'locale' => 'en', 'name' => 'Collarless'],
            ['attribute_value_id' => 9, 'locale' => 'zh-cn', 'name' => '短袖'],
            ['attribute_value_id' => 9, 'locale' => 'en', 'name' => 'Short-sleeve'],
            ['attribute_value_id' => 10, 'locale' => 'zh-cn', 'name' => 'T恤'],
            ['attribute_value_id' => 10, 'locale' => 'en', 'name' => 'T-shirt'],
            ['attribute_value_id' => 11, 'locale' => 'zh-cn', 'name' => '保暖'],
            ['attribute_value_id' => 11, 'locale' => 'en', 'name' => 'Thermal'],
            ['attribute_value_id' => 12, 'locale' => 'zh-cn', 'name' => '防晒'],
            ['attribute_value_id' => 12, 'locale' => 'en', 'name' => 'UV protection'],
        ];
    }

    private function productAttributes(): array
    {
        return [
            ['product_id' => 1, 'attribute_id' => 1, 'attribute_value_id' => 3], // Waterproof feature
            ['product_id' => 1, 'attribute_id' => 2, 'attribute_value_id' => 1], // Cotton fabric
            ['product_id' => 1, 'attribute_id' => 3, 'attribute_value_id' => 10], // T-shirt style
            ['product_id' => 2, 'attribute_id' => 1, 'attribute_value_id' => 3], // Waterproof feature
            ['product_id' => 2, 'attribute_id' => 2, 'attribute_value_id' => 1], // Cotton fabric
            ['product_id' => 2, 'attribute_id' => 3, 'attribute_value_id' => 4], // Crew neck style
            ['product_id' => 3, 'attribute_id' => 1, 'attribute_value_id' => 12], // UV protection feature
            ['product_id' => 3, 'attribute_id' => 2, 'attribute_value_id' => 5], // Silk fabric
            ['product_id' => 4, 'attribute_id' => 1, 'attribute_value_id' => 11], // Thermal feature
            ['product_id' => 4, 'attribute_id' => 2, 'attribute_value_id' => 7], // Synthetic fiber fabric
            ['product_id' => 4, 'attribute_id' => 3, 'attribute_value_id' => 10], // T-shirt style
            ['product_id' => 5, 'attribute_id' => 1, 'attribute_value_id' => 11], // Thermal feature
            ['product_id' => 5, 'attribute_id' => 2, 'attribute_value_id' => 5], // Silk fabric
            ['product_id' => 5, 'attribute_id' => 3, 'attribute_value_id' => 10], // T-shirt style
            ['product_id' => 6, 'attribute_id' => 3, 'attribute_value_id' => 8], // Collarless style
            ['product_id' => 6, 'attribute_id' => 2, 'attribute_value_id' => 1], // Cotton fabric
            ['product_id' => 7, 'attribute_id' => 1, 'attribute_value_id' => 11], // Thermal feature
            ['product_id' => 7, 'attribute_id' => 3, 'attribute_value_id' => 10], // T-shirt style
            ['product_id' => 7, 'attribute_id' => 2, 'attribute_value_id' => 5], // Silk fabric
            ['product_id' => 8, 'attribute_id' => 1, 'attribute_value_id' => 12], // UV protection feature
            ['product_id' => 8, 'attribute_id' => 3, 'attribute_value_id' => 9], // Short-sleeve style
            ['product_id' => 8, 'attribute_id' => 2, 'attribute_value_id' => 5], // Silk fabric
            ['product_id' => 9, 'attribute_id' => 1, 'attribute_value_id' => 3], // Waterproof feature
            ['product_id' => 9, 'attribute_id' => 2, 'attribute_value_id' => 1], // Cotton fabric
            ['product_id' => 9, 'attribute_id' => 3, 'attribute_value_id' => 8], // Collarless style
        ];
    }
}
