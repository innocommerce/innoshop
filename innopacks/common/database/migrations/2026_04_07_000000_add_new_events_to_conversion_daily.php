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
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('conversion_daily', function (Blueprint $table) {
            $table->unsignedInteger('home_views')->default(0)->after('date')->comment('Home Page Views');
            $table->unsignedInteger('category_views')->default(0)->after('home_views')->comment('Category Page Views');
            $table->unsignedInteger('searches')->default(0)->after('registers')->comment('Search Events');
            $table->unsignedInteger('cart_views')->default(0)->after('searches')->comment('Cart Page Views');
            $table->unsignedInteger('order_cancelled')->default(0)->after('cart_views')->comment('Order Cancelled Events');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('conversion_daily', function (Blueprint $table) {
            $table->dropColumn(['home_views', 'category_views', 'searches', 'cart_views', 'order_cancelled']);
        });
    }
};
