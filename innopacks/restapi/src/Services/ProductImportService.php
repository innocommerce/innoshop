<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Services;

use Exception;
use InnoShop\Common\Repositories\Attribute\ValueRepo;
use InnoShop\Common\Repositories\AttributeRepo;
use InnoShop\Common\Repositories\CategoryRepo;
use InnoShop\Common\Repositories\ProductRepo;
use Throwable;

class ProductImportService
{
    /**
     * @return self
     */
    public static function getInstance(): ProductImportService
    {
        return new self;
    }

    /**
     * @param  $productData
     * @param  $product
     * @return mixed
     * @throws Throwable
     */
    public function import($productData, $product = null): mixed
    {
        $productData = $this->handleData($productData);
        if (empty($product)) {
            return ProductRepo::getInstance()->create($productData);
        }

        return ProductRepo::getInstance()->update($product, $productData);
    }

    /**
     * @param  $product
     * @param  $productData
     * @return mixed
     * @throws Throwable
     */
    public function patch($product, $productData): mixed
    {
        $productData = $this->handleData($productData);

        return ProductRepo::getInstance()->patch($product, $productData);
    }

    /**
     * @param  $productData
     * @return mixed
     * @throws Throwable
     */
    private function handleData($productData): mixed
    {
        if (isset($productData['attributes'])) {
            $productData['attributes'] = $this->handleAttributes($productData['attributes']);
        }

        if (isset($productData['categories'])) {
            $productData['categories'] = $this->handleCategories($productData['categories']);
        }

        return $productData;
    }

    /**
     * 输入 "attributes": [{"attribute": "功能","attribute_value": "防水"},{"attribute": "功能","attribute_value": "保暖"}],
     * 输出 "attributes": [{"attribute_id": "1", "attribute_value_id": "3"},{"attribute_id": "1", "attribute_value_id": "11"}],
     * @param  $attributes
     * @return array
     * @throws Exception|Throwable
     */
    private function handleAttributes($attributes): array
    {
        $result = [];

        if (empty($attributes)) {
            return $result;
        }

        foreach ($attributes as $attribute) {
            if (isset($attribute['attribute_id']) && isset($attribute['attribute_value_id'])) {
                $result[] = $attribute;

                continue;
            }

            if (! isset($attribute['attribute']) || ! isset($attribute['attribute_value'])) {
                throw new Exception('请提供 attribute 和 attribute_value');
            }

            $attributeRow = AttributeRepo::getInstance()->findOrCreateByName($attribute['attribute']);
            if (empty($attributeRow)) {
                throw new Exception("无法创建属性 {$attribute['attribute']} ");
            }

            $attributeValueRow = ValueRepo::getInstance()->findOrCreateByName($attributeRow, $attribute['attribute_value']);
            if (empty($attributeValueRow)) {
                throw new Exception("无法创建属性值 {$attribute['attribute_value']} ");
            }

            $attribute['attribute_id']       = $attributeRow->id;
            $attribute['attribute_value_id'] = $attributeValueRow->id;

            $result[] = $attribute;
        }

        return $result;
    }

    /**
     * 输入 "categories": ["帽子", "鞋子"],
     * 输出 "categories": [1, 2],
     * @param  $categories
     * @return array
     * @throws Throwable
     */
    private function handleCategories($categories): array
    {
        $result = [];

        foreach ($categories as $name) {
            if (is_int($name)) {
                $result[] = $name;

                continue;
            }
            $category = CategoryRepo::getInstance()->findOrCreateByName($name);
            $result[] = $category->id;
        }

        return $result;
    }
}
