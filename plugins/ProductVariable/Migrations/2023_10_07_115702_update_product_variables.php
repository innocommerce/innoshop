<?php

/**
 * @author     村长+ <178277164@qq.com>
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('product_custom_skus')) {
            Schema::create('product_custom_skus', function (Blueprint $table) {
                $table->comment('sku自定义sku');
                $table->id()->comment('ID');
                $table->integer('product_id')->comment('产品id');
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('product_custom_sku_translations')) {
            Schema::create('product_custom_sku_translations', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('ID');
                $table->integer('product_custom_skus_id')->index('product_custom_skus_id');
                $table->string('locale')->comment('Locale Code');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('order_item_custom_skus')) {
            Schema::create('order_item_custom_skus', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('ID');
                $table->integer('order_item_id')->comment('订单明细id');
                $table->json('custom_sku');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}
};
