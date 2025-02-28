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
        if (! Schema::hasTable('product_custom_sku_cart')) {
            Schema::create('product_custom_sku_cart', function (Blueprint $table) {
                $table->comment('sku其他内容');
                $table->id()->comment('ID');
                $table->string('session_id')->nullable(true)->comment('下单人的session_id');
                $table->integer('user_id')->nullable(true)->comment('下单人的user_id');
                $table->json('custom_sku')->comment('填写的内容');
                $table->integer('sku_id')->default(0)->comment('sku_id');
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
