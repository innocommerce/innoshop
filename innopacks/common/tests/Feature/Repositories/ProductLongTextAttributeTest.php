<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Repositories;

use InnoShop\Common\Models\Attribute;
use InnoShop\Common\Models\Product\Attribute as ProductAttribute;
use InnoShop\Common\Repositories\AttributeRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Tests\TestCase;
use Tests\Traits\CreatesProduct;

class ProductLongTextAttributeTest extends TestCase
{
    use CreatesProduct;

    private function createLongTextAttribute(string $type, string $name): Attribute
    {
        return AttributeRepo::getInstance()->create([
            'translations' => [['locale' => 'en', 'name' => $name]],
            'type'         => $type,
        ]);
    }

    /**
     * Long-text values arrive from the panel as a locale=>content map and
     * must round-trip through value_text as JSON, together with is_visible.
     */
    public function test_patch_stores_long_text_values_as_locale_map(): void
    {
        $richtext = $this->createLongTextAttribute(Attribute::TYPE_RICHTEXT, 'Care');
        $product  = $this->createProduct();

        ProductRepo::getInstance()->patch($product, [
            'long_attributes' => [
                $richtext->id => [
                    'value_text' => ['en' => '<p>EN</p>', 'zh-cn' => '<p>中文</p>', 'fr' => ''],
                    'is_visible' => '1',
                ],
            ],
        ]);

        $row = $product->productAttributes()->where('attribute_id', $richtext->id)->first();
        $this->assertNotNull($row);
        $this->assertSame(['en' => '<p>EN</p>', 'zh-cn' => '<p>中文</p>'], $row->valueTextMap());
        $this->assertTrue($row->is_visible);
    }

    /**
     * A locale map that is empty in every language carries no content —
     * the row must be dropped instead of stored as an empty shell.
     */
    public function test_patch_skips_rows_when_all_locales_empty(): void
    {
        $textarea = $this->createLongTextAttribute(Attribute::TYPE_TEXTAREA, 'Notes');
        $product  = $this->createProduct();

        ProductRepo::getInstance()->patch($product, [
            'long_attributes' => [
                $textarea->id => ['value_text' => ['en' => '', 'zh-cn' => '  '], 'is_visible' => '1'],
            ],
        ]);

        $this->assertSame(0, $product->productAttributes()->where('attribute_id', $textarea->id)->count());
    }

    /**
     * The 显示到详情页 checkbox must round-trip through patch(): unchecking
     * hides the value on the storefront detail page.
     */
    public function test_patch_persists_is_visible_false(): void
    {
        $richtext = $this->createLongTextAttribute(Attribute::TYPE_RICHTEXT, 'FAQ');
        $product  = $this->createProduct();

        ProductRepo::getInstance()->patch($product, [
            'long_attributes' => [
                $richtext->id => ['value_text' => ['en' => 'content'], 'is_visible' => '0'],
            ],
        ]);

        $row = $product->productAttributes()->where('attribute_id', $richtext->id)->first();
        $this->assertFalse($row->is_visible);
        $this->assertEmpty($product->visibleLongTextAttributes());
    }

    /**
     * API callers may still submit a plain string for long-text values;
     * it must be stored verbatim, not wrapped into a locale map.
     */
    public function test_patch_accepts_plain_string_for_backward_compat(): void
    {
        $textarea = $this->createLongTextAttribute(Attribute::TYPE_TEXTAREA, 'Legacy');
        $product  = $this->createProduct();

        ProductRepo::getInstance()->patch($product, [
            'long_attributes' => [
                $textarea->id => ['value_text' => 'plain legacy text', 'is_visible' => '1'],
            ],
        ]);

        $row = $product->productAttributes()->where('attribute_id', $textarea->id)->first();
        $this->assertSame('plain legacy text', $row->value_text);
    }

    /**
     * The panel submits spec-table rows and long-text rows in two separate
     * payloads; both must land in product_attributes in one save.
     */
    public function test_patch_merges_spec_and_long_text_attributes(): void
    {
        $input    = $this->createLongTextAttribute(Attribute::TYPE_INPUT, 'Material');
        $richtext = $this->createLongTextAttribute(Attribute::TYPE_RICHTEXT, 'Scenarios');
        $product  = $this->createProduct();

        ProductRepo::getInstance()->patch($product, [
            'attributes' => [
                ['attribute_id' => $input->id, 'value_text' => 'Cotton'],
            ],
            'long_attributes' => [
                $richtext->id => ['value_text' => ['en' => '<p>outdoor</p>'], 'is_visible' => '1'],
            ],
        ]);

        $this->assertSame(2, $product->productAttributes()->count());
        $this->assertSame('Cotton', $product->productAttributes()->where('attribute_id', $input->id)->first()->value_text);
    }

    /**
     * Storefront resolution follows the fallback chain:
     * current locale -> system default locale -> any non-empty locale.
     */
    public function test_visible_long_text_attributes_resolves_locale_fallback(): void
    {
        $richtext = $this->createLongTextAttribute(Attribute::TYPE_RICHTEXT, 'Feature');
        $textarea = $this->createLongTextAttribute(Attribute::TYPE_TEXTAREA, 'Remark');
        $product  = $this->createProduct();

        ProductRepo::getInstance()->patch($product, [
            'long_attributes' => [
                $richtext->id => ['value_text' => ['en' => '<p>EN</p>', 'zh-cn' => '<p>中文</p>'], 'is_visible' => '1'],
                $textarea->id => ['value_text' => ['zh-cn' => '仅中文'], 'is_visible' => '1'],
            ],
        ]);

        session(['locale' => 'en']);
        $items = collect($product->visibleLongTextAttributes())->pluck('value', 'name');
        $this->assertSame('<p>EN</p>', $items[$richtext->fallbackName()]);
        $this->assertSame('仅中文', $items[$textarea->fallbackName()]);

        session(['locale' => 'zh-cn']);
        $items = collect($product->visibleLongTextAttributes())->pluck('value', 'name');
        $this->assertSame('<p>中文</p>', $items[$richtext->fallbackName()]);
    }

    /**
     * Long-text rows must never leak into the specification table, and rows
     * whose attribute definition was deleted must not fatal the storefront.
     */
    public function test_grouped_attributes_excludes_long_text_and_orphans(): void
    {
        $input    = $this->createLongTextAttribute(Attribute::TYPE_INPUT, 'Material');
        $richtext = $this->createLongTextAttribute(Attribute::TYPE_RICHTEXT, 'Feature');
        $product  = $this->createProduct();

        ProductRepo::getInstance()->patch($product, [
            'attributes' => [
                ['attribute_id' => $input->id, 'value_text' => 'Cotton'],
            ],
            'long_attributes' => [
                $richtext->id => ['value_text' => ['en' => '<p>x</p>'], 'is_visible' => '1'],
            ],
        ]);

        // Simulate an attribute definition deleted while product rows remain.
        ProductAttribute::query()->create([
            'product_id'   => $product->id,
            'attribute_id' => 999999,
            'value_text'   => 'orphan',
        ]);

        $grouped = $product->groupedAttributes();
        $flat    = collect($grouped)->pluck('attributes')->flatten(1)->pluck('attribute');

        $this->assertContains('Material', $flat);
        $this->assertNotContains('Feature', $flat);
        $this->assertCount(1, $flat);
    }
}
