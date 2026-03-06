<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Unit\Resources;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Relation;
use InnoShop\Common\Tests\TestCase;
use InnoShop\Panel\Resources\ProductNameResource;

class ProductNameResourceBugTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_name_resource_handles_missing_relation_product()
    {
        // Create Brand
        $brand = new \InnoShop\Common\Models\Brand;
        $brand->fill([
            'name'   => 'Test Brand',
            'first'  => 'T',
            'logo'   => 'test_logo.jpg',
            'active' => true,
        ]);
        $brand->save();

        // Create main product
        $productA = new Product;
        $productA->fill([
            'name'     => 'Product A',
            'brand_id' => $brand->id,
            'slug'     => 'product-a',
            'active'   => true,
            'price'    => 100,
        ]);
        $productA->save();

        // Create related product
        $productB = new Product;
        $productB->fill([
            'name'     => 'Product B',
            'brand_id' => $brand->id,
            'slug'     => 'product-b',
            'active'   => true,
            'price'    => 100,
        ]);
        $productB->save();
        $productBId = $productB->id;

        // Create relation
        $relation              = new Relation;
        $relation->product_id  = $productA->id;
        $relation->relation_id = $productBId;
        $relation->save();

        // Delete product B to simulate the issue
        $productB->delete();

        // Retrieve relation with soft-deleted or missing product
        // The relation record still exists, but the related product is gone.

        // This simulates how the controller likely accesses relations
        // $relations = $productA->relations()->get();
        // $relatedProducts = $relations->map(function($rel) { return $rel->relationProduct; });

        // $relations->map(...) will result in a collection where some items are null because relationProduct returns null.

        $relations       = $productA->relations()->get();
        $relatedProducts = $relations->map(function ($rel) {
            return $rel->relationProduct;
        });

        // Try to create resource collection
        try {
            // This is where it should fail if the bug exists and Laravel doesn't filter nulls automatically in some way,
            // OR if the code iterates over it and manually creates new ProductNameResource($product) where $product is null.

            // If we use collection(), Laravel filters nulls usually.
            // But if we iterate manually...

            $resources = ProductNameResource::collection($relatedProducts);
            $response  = $resources->response()->getData(true);

            // If we reach here, maybe Laravel handled the nulls in the collection.
            // But the issue report says: "Attempt to read property 'id' on null".

            // This suggests that inside ProductNameResource, $this->resource is null, but we access $this->id.
            // Wait, if $this->resource is null, $this->id delegates to property on null? No.

            // Let's force the error by manually instantiating with null, which simulates what might happen
            // if a loop doesn't check for null.

            $resource = new ProductNameResource(null);
            // Calling toArray manually on it.
            $result = $resource->toArray(new Request);
            $this->assertEmpty($result);

        } catch (\Throwable $e) {
            // We expect an error here or in the collection processing
            $this->fail('Exception thrown: '.$e->getMessage());

            return;
        }
    }
}
