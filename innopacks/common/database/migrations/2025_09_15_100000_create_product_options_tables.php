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
    /**
     * Run the migrations
     */
    public function up(): void
    {
        // Product options table for value-added services and customization items
        if (! Schema::hasTable('options')) {
            Schema::create('options', function (Blueprint $table) {
                $table->comment('Product options for value-added services and customization items');
                $table->bigIncrements('id')->comment('Primary key');
                $table->json('name')->comment('Option name (multilingual JSON)');
                $table->json('description')->nullable()->comment('Option description (multilingual JSON)');
                $table->enum('type', ['select', 'radio', 'checkbox'])->default('select')->comment('Display type');
                $table->integer('position')->default(0)->comment('Display position');
                $table->boolean('active')->default(true)->comment('Is active status');
                $table->boolean('required')->default(false)->comment('Is required selection');
                $table->timestamps();
            });
        }

        // Option values table for specific choices within each option
        if (! Schema::hasTable('option_values')) {
            Schema::create('option_values', function (Blueprint $table) {
                $table->comment('Option values for specific choices within each option');
                $table->bigIncrements('id')->comment('Primary key');
                $table->unsignedBigInteger('option_id')->index()->comment('Option ID reference');
                $table->json('name')->comment('Value name (multilingual JSON)');
                $table->string('image')->nullable()->comment('Value image path');
                $table->integer('position')->default(0)->comment('Display position');
                $table->boolean('active')->default(true)->comment('Is active status');
                $table->timestamps();
            });
        }

        // Product options relation table defining which options are available for each product
        if (! Schema::hasTable('product_options')) {
            Schema::create('product_options', function (Blueprint $table) {
                $table->comment('Product options relation for available customization options');
                $table->bigIncrements('id')->comment('Primary key');
                $table->unsignedBigInteger('product_id')->index()->comment('Product ID reference');
                $table->unsignedBigInteger('option_id')->index()->comment('Option ID reference');
                $table->integer('position')->default(0)->comment('Display position');
                $table->timestamps();

                $table->unique(['product_id', 'option_id']);
            });
        }

        // Product option values configuration table with pricing and inventory
        if (! Schema::hasTable('product_option_values')) {
            Schema::create('product_option_values', function (Blueprint $table) {
                $table->comment('Product option values with pricing and inventory configuration');
                $table->bigIncrements('id')->comment('Primary key');
                $table->unsignedBigInteger('product_id')->index()->comment('Product ID reference');
                $table->unsignedBigInteger('option_id')->index()->comment('Option ID reference');
                $table->unsignedBigInteger('option_value_id')->index()->comment('Option value ID reference');
                $table->decimal('price_adjustment', 10, 2)->default(0)->comment('Price adjustment amount (+/-)');
                $table->integer('quantity')->default(0)->comment('Available stock quantity');
                $table->boolean('subtract_stock')->default(false)->comment('Whether to subtract from stock');
                $table->timestamps();

                $table->unique(['product_id', 'option_id', 'option_value_id'], 'product_option_values_unique');
            });
        }

        // Cart option values snapshot table for preserving selection history
        if (! Schema::hasTable('cart_option_values')) {
            Schema::create('cart_option_values', function (Blueprint $table) {
                $table->comment('Cart item selected option values snapshot');
                $table->bigIncrements('id')->comment('Primary key');
                $table->unsignedBigInteger('cart_item_id')->index()->comment('Cart item ID reference');
                $table->unsignedBigInteger('option_id')->index()->comment('Option ID reference');
                $table->unsignedBigInteger('option_value_id')->index()->comment('Option value ID reference');
                $table->json('option_name')->comment('Option name snapshot (multilingual)');
                $table->json('option_value_name')->comment('Option value name snapshot (multilingual)');
                $table->decimal('price_adjustment', 10, 2)->default(0)->comment('Price adjustment snapshot');
                $table->timestamps();
            });
        }

        // Order option values snapshot table for preserving order history
        if (! Schema::hasTable('order_option_values')) {
            Schema::create('order_option_values', function (Blueprint $table) {
                $table->comment('Order item selected option values snapshot');
                $table->bigIncrements('id')->comment('Primary key');
                $table->unsignedBigInteger('order_item_id')->index()->comment('Order item ID reference');
                $table->unsignedBigInteger('option_id')->index()->comment('Option ID reference');
                $table->unsignedBigInteger('option_value_id')->index()->comment('Option value ID reference');
                $table->string('option_name')->comment('Option name snapshot');
                $table->string('option_value_name')->comment('Option value name snapshot');
                $table->decimal('price_adjustment', 10, 2)->default(0)->comment('Price adjustment snapshot');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('order_option_values');
        Schema::dropIfExists('cart_option_values');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('option_values');
        Schema::dropIfExists('options');
    }
};
