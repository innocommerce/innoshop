<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Traits;

use Database\Factories\BrandFactory;
use Database\Factories\CategoryFactory;
use Database\Factories\ProductFactory;
use Database\Factories\ProductSkuFactory;
use InnoShop\Common\Models\Brand;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Sku;

trait CreatesProduct
{
    protected function createProduct(array $attributes = []): Product
    {
        return ProductFactory::new()->create($attributes);
    }

    protected function createProductWithSku(array $productAttributes = [], array $skuAttributes = []): Product
    {
        $product = $this->createProduct($productAttributes);
        $this->createProductSku(array_merge(['product_id' => $product->id], $skuAttributes));

        return $product->fresh();
    }

    protected function createProductSku(array $attributes = []): Sku
    {
        return ProductSkuFactory::new()->create($attributes);
    }

    protected function createCategory(array $attributes = []): Category
    {
        return CategoryFactory::new()->create($attributes);
    }

    protected function createBrand(array $attributes = []): Brand
    {
        return BrandFactory::new()->create($attributes);
    }

    protected function createInactiveProduct(array $attributes = []): Product
    {
        return ProductFactory::new()->inactive()->create($attributes);
    }

    protected function createVirtualProduct(array $attributes = []): Product
    {
        return ProductFactory::new()->virtual()->create($attributes);
    }

    protected function createBundleProduct(array $attributes = []): Product
    {
        return ProductFactory::new()->bundle()->create($attributes);
    }
}
