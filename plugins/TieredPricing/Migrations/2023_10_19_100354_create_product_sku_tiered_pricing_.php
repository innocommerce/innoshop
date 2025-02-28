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
        if (! Schema::hasTable('product_sku_tiered_pricing')) {
            Schema::create('product_sku_tiered_pricing', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id');
                $table->integer('sku_index');
                $table->integer('sku_id');
                $table->string('sku_code');
                $table->double('price');
                $table->integer('num');
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
