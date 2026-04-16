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
    public function up(): void
    {
        Schema::create('brand_translations', function (Blueprint $table) {
            $table->comment('Brand Translation');
            $table->bigIncrements('id')->comment('ID');
            $table->unsignedBigInteger('brand_id')->index('brand_trans_brand_id')->comment('Brand ID');
            $table->string('locale')->comment('Locale Code');
            $table->string('name')->comment('Name');
            $table->text('summary')->nullable()->comment('Summary');
            $table->longText('content')->nullable()->comment('Content');
            $table->string('meta_title', 500)->nullable()->comment('Meta Title');
            $table->string('meta_description', 1000)->nullable()->comment('Meta Description');
            $table->string('meta_keywords', 500)->nullable()->comment('Meta Keywords');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_translations');
    }
};
