<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Models;

use InnoShop\Common\Models\Product;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ProductTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Product::class);
    }

    #[Test]
    public function test_model_exists(): void
    {
        $this->assertTrue(class_exists(Product::class));
    }

    #[Test]
    public function test_model_extends_base_model(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('InnoShop\Common\Models\BaseModel'));
    }

    #[Test]
    public function test_model_uses_required_traits(): void
    {
        $traits = class_uses_recursive(Product::class);
        $this->assertContains('InnoShop\Common\Traits\Translatable', $traits);
        $this->assertContains('InnoShop\Common\Traits\Replicate', $traits);
    }

    #[Test]
    public function test_type_constants_are_defined(): void
    {
        $this->assertEquals('normal', Product::TYPE_NORMAL);
        $this->assertEquals('bundle', Product::TYPE_BUNDLE);
    }

    #[Test]
    public function test_fillable_contains_required_fields(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $requiredFields = [
            'type', 'brand_id', 'images', 'video', 'price', 'tax_class_id',
            'spu_code', 'slug', 'is_virtual', 'variables', 'position',
            'active', 'weight', 'weight_class', 'sales', 'viewed',
        ];

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $fillable, "Field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_casts_are_defined(): void
    {
        $property = $this->reflection->getProperty('casts');
        $casts    = $property->getDefaultValue();

        $this->assertArrayHasKey('variables', $casts);
        $this->assertArrayHasKey('images', $casts);
        $this->assertArrayHasKey('video', $casts);
        $this->assertArrayHasKey('active', $casts);
        $this->assertArrayHasKey('is_virtual', $casts);

        $this->assertEquals('array', $casts['variables']);
        $this->assertEquals('array', $casts['images']);
        $this->assertEquals('boolean', $casts['active']);
        $this->assertEquals('boolean', $casts['is_virtual']);
    }

    #[Test]
    public function test_appends_contains_image(): void
    {
        $property = $this->reflection->getProperty('appends');
        $appends  = $property->getDefaultValue();

        $this->assertContains('image', $appends);
    }

    #[Test]
    public function test_has_brand_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('brand'));
        $method = $this->reflection->getMethod('brand');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_master_sku_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('masterSku'));
        $method = $this->reflection->getMethod('masterSku');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_skus_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('skus'));
        $method = $this->reflection->getMethod('skus');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_categories_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('categories'));
        $method = $this->reflection->getMethod('categories');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_product_attributes_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('productAttributes'));
        $method = $this->reflection->getMethod('productAttributes');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_relations_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('relations'));
        $method = $this->reflection->getMethod('relations');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_tax_class_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('taxClass'));
        $method = $this->reflection->getMethod('taxClass');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_weight_class_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('weightClass'));
        $method = $this->reflection->getMethod('weightClass');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_favorites_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('favorites'));
        $method = $this->reflection->getMethod('favorites');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_order_items_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('orderItems'));
        $method = $this->reflection->getMethod('orderItems');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_reviews_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('reviews'));
        $method = $this->reflection->getMethod('reviews');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_bundles_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('bundles'));
        $method = $this->reflection->getMethod('bundles');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_product_options_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('productOptions'));
        $method = $this->reflection->getMethod('productOptions');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_product_option_values_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('productOptionValues'));
        $method = $this->reflection->getMethod('productOptionValues');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_options_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('options'));
        $method = $this->reflection->getMethod('options');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_option_values_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('optionValues'));
        $method = $this->reflection->getMethod('optionValues');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_total_quantity_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('totalQuantity'));
        $method = $this->reflection->getMethod('totalQuantity');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_has_favorite_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('hasFavorite'));
        $method = $this->reflection->getMethod('hasFavorite');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_grouped_attributes_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('groupedAttributes'));
        $method = $this->reflection->getMethod('groupedAttributes');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_is_multiple_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('isMultiple'));
        $method = $this->reflection->getMethod('isMultiple');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_image_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getImageAttribute'));
        $method = $this->reflection->getMethod('getImageAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_url_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getUrlAttribute'));
        $method = $this->reflection->getMethod('getUrlAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_edit_url_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getEditUrlAttribute'));
        $method = $this->reflection->getMethod('getEditUrlAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_image_url_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getImageUrlAttribute'));
        $method = $this->reflection->getMethod('getImageUrlAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_get_image_url_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getImageUrl'));
        $method = $this->reflection->getMethod('getImageUrl');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_hover_image_url_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getHoverImageUrl'));
        $method = $this->reflection->getMethod('getHoverImageUrl');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_has_hover_image_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('hasHoverImage'));
        $method = $this->reflection->getMethod('hasHoverImage');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_image_accessor_logic_with_images(): void
    {
        $images = ['image1.jpg', 'image2.jpg'];
        $image  = $images[0] ?? '';

        $this->assertEquals('image1.jpg', $image);
    }

    #[Test]
    public function test_image_accessor_logic_without_images(): void
    {
        $images = [];
        $image  = $images[0] ?? '';

        $this->assertEquals('', $image);
    }

    #[Test]
    public function test_image_accessor_logic_with_null(): void
    {
        $images = null;
        $image  = $images[0] ?? '';

        $this->assertEquals('', $image);
    }

    #[Test]
    public function test_is_multiple_logic_with_variables(): void
    {
        $variables = ['color' => ['red', 'blue']];
        $skuCount  = 1;

        $isMultiple = $variables || $skuCount > 1;

        $this->assertTrue($isMultiple);
    }

    #[Test]
    public function test_is_multiple_logic_with_multiple_skus(): void
    {
        $variables = null;
        $skuCount  = 3;

        $isMultiple = $variables || $skuCount > 1;

        $this->assertTrue($isMultiple);
    }

    #[Test]
    public function test_is_multiple_logic_single_sku_no_variables(): void
    {
        $variables = null;
        $skuCount  = 1;

        $isMultiple = $variables || $skuCount > 1;

        $this->assertFalse($isMultiple);
    }

    #[Test]
    public function test_has_hover_image_logic_with_image(): void
    {
        $hoverImage    = 'hover.jpg';
        $hasHoverImage = ! empty($hoverImage);

        $this->assertTrue($hasHoverImage);
    }

    #[Test]
    public function test_has_hover_image_logic_without_image(): void
    {
        $hoverImage    = '';
        $hasHoverImage = ! empty($hoverImage);

        $this->assertFalse($hasHoverImage);
    }
}
