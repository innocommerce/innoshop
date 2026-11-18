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
        Schema::table('visit_device_daily', function (Blueprint $table) {
            if (! Schema::hasColumn('visit_device_daily', 'event_sessions')) {
                $table->unsignedInteger('event_sessions')->default(0)->after('page_views')->comment('Sessions with at least one page-view event on this date (distinct per day)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visit_device_daily', function (Blueprint $table) {
            if (Schema::hasColumn('visit_device_daily', 'event_sessions')) {
                $table->dropColumn('event_sessions');
            }
        });
    }
};
