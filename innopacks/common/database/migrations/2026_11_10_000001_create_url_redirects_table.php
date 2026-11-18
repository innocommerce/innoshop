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
        Schema::create('url_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 32)->nullable()->comment('product/article/category/… null for manual rules');
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->string('old_path')->unique()->comment('Locale-less canonical path, e.g. product-demo-a');
            $table->string('new_path')->comment('Internal path, home (/) or absolute external URL')->index();
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_redirects');
    }
};
