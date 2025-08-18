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
     */
    public function up(): void
    {
        // Add video column to products table
        if (! Schema::hasColumn('products', 'video')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('video')->nullable()->after('images');
            });
        }

        // Drop product_videos table
        Schema::dropIfExists('product_videos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate product_videos table
        Schema::create('product_videos', function (Blueprint $table) {
            $table->increments('id')->comment('ID');
            $table->integer('product_id')->index('pv_product_id')->comment('Product ID');
            $table->string('type', 11)->comment('Type: path or embed');
            $table->text('content')->comment('Video Path or Embed HTML');
            $table->timestamps();
        });

        // Remove video column from products table
        if (Schema::hasColumn('products', 'video')) {
            Schema::dropColumns('products', 'video');
        }
    }
};
