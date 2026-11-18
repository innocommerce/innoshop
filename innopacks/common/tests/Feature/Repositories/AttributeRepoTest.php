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
use InnoShop\Common\Repositories\AttributeRepo;
use InnoShop\Common\Tests\TestCase;

class AttributeRepoTest extends TestCase
{
    /**
     * Attribute definitions persist type + is_filterable through the repo
     * pipeline so the panel store/edit forms can round-trip them.
     */
    public function test_handle_data_persists_type_and_filterable(): void
    {
        $attribute = AttributeRepo::getInstance()->create([
            'translations'  => [['locale' => 'en', 'name' => 'Water Absorption '.uniqid()]],
            'type'          => 'number',
            'is_filterable' => true,
        ]);

        $this->assertSame('number', $attribute->type);
        $this->assertTrue($attribute->is_filterable);

        // Update path must also round-trip the flags without clearing them.
        AttributeRepo::getInstance()->update($attribute, [
            'translations'  => [['locale' => 'en', 'name' => 'Water Absorption '.uniqid()]],
            'type'          => 'input',
            'is_filterable' => false,
        ]);

        $attribute->refresh();
        $this->assertSame('input', $attribute->type);
        $this->assertFalse($attribute->is_filterable);
    }

    /**
     * The storefront sidebar (FilterSidebarTrait) relies on
     * getAttributesWithValues(filterableOnly: true) to omit non-filterable
     * attributes from facets. An accidental regression here would silently
     * disable every facet when an admin toggles the flag off.
     */
    public function test_get_attributes_with_values_filters_by_filterable_flag(): void
    {
        $filterable = AttributeRepo::getInstance()->create([
            'translations'  => [['locale' => 'en', 'name' => 'Facet On '.uniqid()]],
            'type'          => 'select',
            'is_filterable' => true,
        ]);
        $hidden = AttributeRepo::getInstance()->create([
            'translations'  => [['locale' => 'en', 'name' => 'Facet Off '.uniqid()]],
            'type'          => 'select',
            'is_filterable' => false,
        ]);

        $ids = collect(AttributeRepo::getInstance()->getAttributesWithValues(filterableOnly: true))
            ->pluck('id')
            ->all();

        $this->assertContains($filterable->id, $ids);
        $this->assertNotContains($hidden->id, $ids);

        // And the unrestricted call still includes both.
        $allIds = collect(AttributeRepo::getInstance()->getAttributesWithValues())->pluck('id')->all();
        $this->assertContains($hidden->id, $allIds);
    }

    /**
     * Long-text types hold free-form content and can never act as storefront
     * filter facets — the repo must force is_filterable off even when the
     * panel form submits it as true.
     */
    public function test_long_text_types_force_is_filterable_off(): void
    {
        $richtext = AttributeRepo::getInstance()->create([
            'translations'  => [['locale' => 'en', 'name' => 'Rich Content '.uniqid()]],
            'type'          => Attribute::TYPE_RICHTEXT,
            'is_filterable' => true,
        ]);
        $this->assertSame(Attribute::TYPE_RICHTEXT, $richtext->type);
        $this->assertFalse($richtext->is_filterable);

        $textarea = AttributeRepo::getInstance()->create([
            'translations'  => [['locale' => 'en', 'name' => 'Long Notes '.uniqid()]],
            'type'          => Attribute::TYPE_TEXTAREA,
            'is_filterable' => true,
        ]);
        $this->assertFalse($textarea->is_filterable);
    }
}
