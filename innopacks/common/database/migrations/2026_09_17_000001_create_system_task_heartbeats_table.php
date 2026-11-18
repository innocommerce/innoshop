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

/**
 * Heartbeats of the scheduler and individual scheduled tasks.
 *
 * Persisted in a dedicated table instead of the cache so liveness stays
 * observable even when CACHE_STORE=array (no persistence across requests).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_task_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('task_key', 64)->unique()->comment('Task identifier, e.g. visits-aggregate-today');
            $table->timestamp('ran_at')->nullable()->comment('Last run time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_task_heartbeats');
    }
};
