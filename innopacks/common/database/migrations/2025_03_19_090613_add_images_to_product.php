<?php

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
        if (! Schema::hasColumn('products', 'images')) {
            Schema::table('products', function (Blueprint $table) {
                $table->json('images')->nullable()->after('brand_id');
            });
        }

        if (! Schema::hasColumn('products', 'price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('price')->default(0)->after('brand_id');
            });
        }

        if (! Schema::hasColumn('product_skus', 'images')) {
            Schema::table('product_skus', function (Blueprint $table) {
                $table->json('images')->nullable()->after('product_id');
            });
        }

        if (Schema::hasColumn('products', 'product_image_id')) {
            Schema::dropColumns('products', 'product_image_id');
        }
        if (Schema::hasColumn('products', 'product_video_id')) {
            Schema::dropColumns('products', 'product_video_id');
        }
        if (Schema::hasColumn('products', 'product_sku_id')) {
            Schema::dropColumns('products', 'product_sku_id');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
