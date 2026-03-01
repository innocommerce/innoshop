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
        if (! Schema::hasTable('plugin_coordination')) {
            Schema::create('plugin_coordination', function (Blueprint $table) {
                $table->id();
                $table->string('type', 50)->comment('Plugin type: price, orderfee');
                $table->json('sort_order')->nullable()->comment('Plugin execution order');
                $table->string('exclusive_mode', 20)->default('all_stack')->comment('Exclusive mode: first_only, all_stack, custom');
                $table->json('exclusive_pairs')->nullable()->comment('Custom exclusive plugin pairs');
                $table->timestamps();

                $table->unique('type');
                $table->index('type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plugin_coordination');
    }
};
