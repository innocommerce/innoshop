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
        Schema::table('visits', function (Blueprint $table) {
            if (! Schema::hasColumn('visits', 'is_bot')) {
                $table->boolean('is_bot')->default(false)->after('device_type')->index('v_is_bot')->comment('是否爬虫/机器人 UA');
            }
        });

        if (! Schema::hasTable('visit_country_daily')) {
            Schema::create('visit_country_daily', function (Blueprint $table) {
                $table->comment('Daily Visit Statistics by Country — pre-aggregated to avoid COUNT(DISTINCT) at query time');
                $table->date('date');
                $table->string('country_code', 8);
                $table->string('country_name', 100)->default('');

                $table->unsignedInteger('visits')->default(0)->comment('Total visit rows for this country/date');
                $table->unsignedInteger('ip_count')->default(0)->comment('Distinct IPs (pre-computed at aggregation time)');

                $table->primary(['date', 'country_code']);
                $table->index('country_code');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('visit_hourly_daily')) {
            Schema::create('visit_hourly_daily', function (Blueprint $table) {
                $table->comment('Daily Visit Statistics by Hour — pre-aggregated to avoid HOUR()+COUNT(DISTINCT) at query time');
                $table->date('date');
                $table->unsignedTinyInteger('hour');

                $table->unsignedInteger('visits')->default(0)->comment('Visit count for this hour');
                $table->unsignedInteger('ip_count')->default(0)->comment('Distinct IPs (pre-computed at aggregation time)');

                $table->primary(['date', 'hour']);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('visit_device_daily')) {
            Schema::create('visit_device_daily', function (Blueprint $table) {
                $table->comment('Daily Visit Statistics by Device — already partially in visit_daily but here for full pivot (desktop/mobile/tablet/bot)');
                $table->date('date');
                $table->string('device_type', 16);

                $table->unsignedInteger('visits')->default(0)->comment('Visit count for this device');
                $table->unsignedInteger('ip_count')->default(0)->comment('Distinct IPs (pre-computed at aggregation time)');
                $table->unsignedInteger('page_views')->default(0)->comment('Page view events from this device');

                $table->primary(['date', 'device_type']);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_device_daily');
        Schema::dropIfExists('visit_hourly_daily');
        Schema::dropIfExists('visit_country_daily');

        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'is_bot')) {
                $table->dropIndex('v_is_bot');
                $table->dropColumn('is_bot');
            }
        });
    }
};
