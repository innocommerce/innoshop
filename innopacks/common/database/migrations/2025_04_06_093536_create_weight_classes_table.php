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
        if (! Schema::hasTable('weight_classes')) {
            Schema::create('weight_classes', function (Blueprint $table) {
                $table->id()->comment('ID');
                $table->string('code', 16)->unique()->comment('Unit code');
                $table->string('name')->comment('Unit name');
                $table->string('unit')->comment('Unit symbol');
                $table->decimal('value', 15, 8)->default(1)->comment('Conversion rate');
                $table->integer('position')->default(0)->comment('Sort order');
                $table->boolean('active')->default(true)->comment('Status');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_classes');
    }
};
