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
 * Long-text attribute support:
 *
 * - attributes.type now also accepts `richtext`; textarea/richtext values are
 *   edited in a dedicated product tab instead of the specification table.
 * - product_attributes.value_text becomes longText: long-text values are
 *   stored as a locale=>content JSON map, multi-language values may exceed
 *   the 64KB limit of the text type.
 * - product_attributes.is_visible controls whether a long-text value renders
 *   on the storefront product detail page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->longText('value_text')->nullable()->comment('Free Text Value (long-text types: locale JSON map)')->change();
            $table->boolean('is_visible')->default(true)->after('value_number')->comment('Show On Detail Page');
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->string('type', 20)->default('select')->comment('Input Type: select/input/number/textarea/richtext/date')->change();
        });
    }

    public function down(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            $table->string('type', 20)->default('select')->comment('Input Type: select/input/number/textarea/date')->change();
        });

        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropColumn('is_visible');
            $table->text('value_text')->nullable()->comment('Free Text Value')->change();
        });
    }
};
