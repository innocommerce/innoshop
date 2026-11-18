<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attribute enhancement (innoshop/factory#145):
 *
 * - attributes.type               Rendering/storage type per attribute. Existing
 *                                 rows default to `select` so preset-value
 *                                 behaviour is unchanged.
 * - attributes.is_filterable      Opt-in flag for storefront filter facets.
 * - product_attributes.value_*    Free-form value columns. `attribute_value_id`
 *                                 becomes nullable: select attributes keep
 *                                 referencing preset values, all other types
 *                                 store the raw value here (flat wide-table
 *                                 design, one row per product x attribute).
 * - products.length/width/height  Physical dimensions of the product itself,
 *                                 placed next to weight/weight_class.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('type', 20)->default('select')->after('attribute_group_id')->comment('Input Type: select/input/number/textarea/date');
            $table->boolean('is_filterable')->default(false)->after('type')->comment('Filterable Or Not');
        });

        Schema::table('product_attributes', function (Blueprint $table) {
            $table->text('value_text')->nullable()->after('attribute_value_id')->comment('Free Text Value');
            $table->decimal('value_number', 20, 4)->nullable()->after('value_text')->comment('Numeric Value for Range Filtering');
            $table->unsignedInteger('attribute_value_id')->nullable()->comment('Attribute Value ID')->change();

            $table->index(['attribute_id', 'attribute_value_id'], 'pa_attribute_value_idx');
            $table->index(['attribute_id', 'value_number'], 'pa_attribute_number_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('length', 12, 2)->default(0)->after('weight_class')->comment('Length');
            $table->decimal('width', 12, 2)->default(0)->after('length')->comment('Width');
            $table->decimal('height', 12, 2)->default(0)->after('width')->comment('Height');
            $table->string('length_class', 20)->default('')->after('height')->comment('Length Class');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['length', 'width', 'height', 'length_class']);
        });

        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropIndex('pa_attribute_value_idx');
            $table->dropIndex('pa_attribute_number_idx');
            $table->dropColumn(['value_text', 'value_number']);
            $table->unsignedInteger('attribute_value_id')->nullable(false)->comment('Attribute Value ID')->change();
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn(['type', 'is_filterable']);
        });
    }
};
