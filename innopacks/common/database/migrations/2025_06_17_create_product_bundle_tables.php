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

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('products', 'type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('type')->default('normal')->after('price');
            });
        }

        if (! Schema::hasTable('product_bundles')) {
            Schema::create('product_bundles', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id');
                $table->integer('sku_id');
                $table->integer('quantity');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('product_bundles');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
