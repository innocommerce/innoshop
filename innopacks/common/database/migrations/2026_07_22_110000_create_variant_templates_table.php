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
        Schema::create('variant_templates', function (Blueprint $table) {
            $table->comment('Variant Template');
            $table->bigIncrements('id')->comment('ID');
            $table->string('name', 128)->comment('Template Name');
            $table->json('variables')->comment('Variant Variable Definitions');
            $table->json('sku_matrix')->comment('SKU Matrix Data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('variant_templates');
    }
};
