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
        Schema::table('products', function (Blueprint $table) {
            $table->removeColumn('product_image_id');
            $table->removeColumn('product_video_id');
            $table->removeColumn('product_sku_id');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->addColumn('boolean', 'belong_sku')->after('path');
            $table->addColumn('boolean', 'is_cover')->after('path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product', function (Blueprint $table) {
            //
        });
    }
};
