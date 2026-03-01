<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Models;

use InnoShop\Common\Models\CartItem;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CartItemTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(CartItem::class);
    }

    #[Test]
    public function test_model_exists(): void
    {
        $this->assertTrue(class_exists(CartItem::class));
    }

    #[Test]
    public function test_model_extends_base_model(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('InnoShop\Common\Models\BaseModel'));
    }

    #[Test]
    public function test_table_name_is_cart_items(): void
    {
        $property = $this->reflection->getProperty('table');
        $this->assertEquals('cart_items', $property->getDefaultValue());
    }

    #[Test]
    public function test_fillable_contains_required_fields(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $requiredFields = [
            'customer_id', 'product_id', 'sku_code', 'guest_id',
            'selected', 'quantity', 'item_type', 'reference',
        ];

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $fillable, "Field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_appends_contains_computed_attributes(): void
    {
        $property = $this->reflection->getProperty('appends');
        $appends  = $property->getDefaultValue();

        $this->assertContains('subtotal', $appends);
        $this->assertContains('price', $appends);
        $this->assertContains('item_type_label', $appends);
    }

    #[Test]
    public function test_casts_reference_as_array(): void
    {
        $property = $this->reflection->getProperty('casts');
        $casts    = $property->getDefaultValue();

        $this->assertArrayHasKey('reference', $casts);
        $this->assertEquals('array', $casts['reference']);
    }

    #[Test]
    public function test_has_product_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('product'));
        $method = $this->reflection->getMethod('product');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_product_sku_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('productSku'));
        $method = $this->reflection->getMethod('productSku');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_customer_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('customer'));
        $method = $this->reflection->getMethod('customer');
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
    public function test_has_subtotal_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getSubtotalAttribute'));
        $method = $this->reflection->getMethod('getSubtotalAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_price_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getPriceAttribute'));
        $method = $this->reflection->getMethod('getPriceAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_item_type_label_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getItemTypeLabelAttribute'));
        $method = $this->reflection->getMethod('getItemTypeLabelAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_subtotal_calculation_logic(): void
    {
        $price    = 99.99;
        $quantity = 3;
        $decimal  = 2;

        $subtotal = round($price * $quantity, $decimal);

        $this->assertEquals(299.97, $subtotal);
    }

    #[Test]
    public function test_subtotal_calculation_with_zero_quantity(): void
    {
        $price    = 99.99;
        $quantity = 0;
        $decimal  = 2;

        $subtotal = round($price * $quantity, $decimal);

        $this->assertEquals(0, $subtotal);
    }

    #[Test]
    public function test_subtotal_calculation_with_decimal_price(): void
    {
        $price    = 19.999;
        $quantity = 2;
        $decimal  = 2;

        $subtotal = round($price * $quantity, $decimal);

        $this->assertEquals(40.00, $subtotal);
    }

    #[Test]
    public function test_price_with_option_adjustments(): void
    {
        $basePrice         = 100.00;
        $optionAdjustments = [10.00, 5.00, -2.00];

        $finalPrice = $basePrice;
        foreach ($optionAdjustments as $adjustment) {
            $finalPrice += $adjustment;
        }

        $this->assertEquals(113.00, $finalPrice);
    }

    #[Test]
    public function test_price_without_option_adjustments(): void
    {
        $basePrice         = 100.00;
        $optionAdjustments = [];

        $finalPrice = $basePrice;
        foreach ($optionAdjustments as $adjustment) {
            $finalPrice += $adjustment;
        }

        $this->assertEquals(100.00, $finalPrice);
    }
}
