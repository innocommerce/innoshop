<?php

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
        if (! Schema::hasTable('google_feed')) {
            Schema::create('google_feed', function (Blueprint $table) {
                $table->comment('google_feed');
                $table->id()->comment('ID');
                $table->integer('product_id')->default(0)->comment('商品ID');
                $table->string('gtin')->nullable(true)->comment('gtin');
                $table->string('condition')->default('new')->comment('全新 [new],翻新 [refurbished],二手 [used]');
                $table->integer('status')->default(1)->comment('开启状态');
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
