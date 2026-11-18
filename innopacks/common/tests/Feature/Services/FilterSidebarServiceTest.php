<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Attribute;
use InnoShop\Common\Services\FilterSidebarService;
use InnoShop\Common\Tests\TestCase;

class FilterSidebarServiceTest extends TestCase
{
    /**
     * Variant rows are per-product, so the same logical dimension name appears
     * multiple times across products with disjoint value ids. The sidebar must
     * collapse them into a single dimension entry, aggregating all value ids
     * per display name — that aggregated id set is what the
     * variant_values URL parameter carries when a customer toggles a value.
     */
    public function test_process_variants_aggregates_value_ids_per_dimension_name(): void
    {
        // Two variants named "Size" on different products, with disjoint value ids.
        $variants = collect([
            $this->buildVariant('Size', ['4 inch' => 101, '6 inch' => 102]),
            $this->buildVariant('Size', ['4 inch' => 201, '8 inch' => 202]),
            // Different dimension name should not be merged.
            $this->buildVariant('Color', ['Red' => 301]),
        ]);

        $out    = FilterSidebarService::getInstance()->processVariants($variants, new Request);
        $byName = collect($out)->keyBy('name');

        $this->assertCount(2, $byName, 'Same logical dimension must collapse into one entry');

        $sizeValues = collect($byName['Size']['values'])->keyBy('name');
        $this->assertSame('101,201', $sizeValues['4 inch']['value_ids']);
        $this->assertSame('102', $sizeValues['6 inch']['value_ids']);
        $this->assertSame('202', $sizeValues['8 inch']['value_ids']);
        $this->assertFalse($sizeValues['4 inch']['selected']);
    }

    /**
     * When a value id is in the URL via variant_values, the matching facet
     * items should be flagged selected across all aggregated value ids.
     */
    public function test_process_variants_marks_selected_from_url(): void
    {
        $variants = collect([
            $this->buildVariant('Size', ['6 inch' => 102, '8 inch' => 103]),
            $this->buildVariant('Size', ['6 inch' => 202]),
        ]);

        $request    = Request::create('/', 'GET', ['variant_values' => '102,202']);
        $out        = FilterSidebarService::getInstance()->processVariants($variants, $request);
        $sizeValues = collect($out[0]['values'])->keyBy('name');

        $this->assertTrue($sizeValues['6 inch']['selected']);
        $this->assertFalse($sizeValues['8 inch']['selected']);
    }

    /**
     * Number-type attributes render as a min/max range facet (no preset values),
     * select-type as a value checklist. Mis-routing would either hide a
     * range filter or show a value list for a number attribute with no values.
     */
    public function test_process_attributes_routes_number_vs_select_types(): void
    {
        $numberAttr = $this->buildAttributeWithTranslations('number', 'Diameter');
        $selectAttr = $this->buildAttributeWithTranslations('select', 'Material');
        $valueId    = $this->buildPresetValue($selectAttr->id, 'Bone China');

        $attrs = [
            [
                'id'     => $numberAttr->id,
                'name'   => $numberAttr->translation->name,
                'type'   => 'number',
                'values' => [],
            ],
            [
                'id'     => $selectAttr->id,
                'name'   => $selectAttr->translation->name,
                'type'   => 'select',
                'values' => [['id' => $valueId, 'name' => 'Bone China']],
            ],
        ];

        $request = Request::create('/', 'GET', ['attribute_ranges' => [$numberAttr->id => '5.5,6.5']]);
        $out     = FilterSidebarService::getInstance()->processAttributes($attrs, $request);

        $byId = collect($out)->keyBy('id');
        $this->assertSame('number', $byId[$numberAttr->id]['type']);
        $this->assertSame('5.5', $byId[$numberAttr->id]['min']);
        $this->assertSame('6.5', $byId[$numberAttr->id]['max']);
        $this->assertSame([], $byId[$numberAttr->id]['values']);

        $this->assertSame('select', $byId[$selectAttr->id]['type']);
        $this->assertCount(1, $byId[$selectAttr->id]['values']);
    }

    /**
     * Build a processVariants input using plain stdClass so the Variant
     * model's Translatable accessor does not override the test fixtures.
     * Eloquent setRelation('translation', ...) is not enough for Variants
     * because the trait's translation accessor preempts property access.
     */
    private function buildVariant(string $dimensionName, array $valueNamesById): object
    {
        // $valueNamesById is map(name => id); key is the display name, value is the value id.
        $values = [];
        foreach ($valueNamesById as $valueName => $valueId) {
            $value              = new \stdClass;
            $value->id          = $valueId;
            $value->translation = (object) ['name' => $valueName, 'locale' => 'en'];

            $values[] = $value;
        }

        $variant              = new \stdClass;
        $variant->translation = (object) ['name' => $dimensionName, 'locale' => 'en'];
        $variant->values      = $values;

        return $variant;
    }

    /**
     * @param  string  $type
     * @param  string  $name
     */
    private function buildAttributeWithTranslations(string $type, string $name): Attribute
    {
        $attribute = Attribute::query()->create([
            'category_id'        => 0,
            'attribute_group_id' => 0,
            'type'               => $type,
            'is_filterable'      => true,
        ]);
        $attribute->translations()->create(['locale' => 'en', 'name' => $name.' '.uniqid()]);

        return $attribute->fresh();
    }

    /**
     * @param  int  $attributeId
     * @param  string  $name
     */
    private function buildPresetValue(int $attributeId, string $name): int
    {
        $valueId = DB::table('attribute_values')->insertGetId([
            'attribute_id' => $attributeId, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('attribute_value_translations')->insert([
            'attribute_value_id' => $valueId, 'locale' => 'en', 'name' => $name,
            'created_at'         => now(), 'updated_at' => now(),
        ]);

        return $valueId;
    }
}
