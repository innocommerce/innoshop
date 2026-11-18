<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The '360' brand code is a numeric string, while bot_daily.bot_brand is a
     * varchar(50) column. MySQL coerces that comparison to DOUBLE, and strict
     * mode turns it into error 1292 ("Truncated incorrect DOUBLE value"), so
     * every 360spider request failed its analytics write and the brand never
     * showed up in the panel.
     *
     * Renaming the code to a non-numeric one (BotClassifier) and moving the
     * stored rows over keeps the history and stops the coercion.
     */
    public function up(): void
    {
        if (! Schema::hasTable('bot_daily')) {
            return;
        }

        DB::table('bot_daily')
            ->where('bot_brand', '360')
            ->update(['bot_brand' => 'spider360']);

        if (Schema::hasTable('bot_daily_ips')) {
            DB::table('bot_daily_ips')
                ->where('brand', '360')
                ->update(['brand' => 'spider360']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('bot_daily')) {
            return;
        }

        DB::table('bot_daily')
            ->where('bot_brand', 'spider360')
            ->update(['bot_brand' => '360']);

        if (Schema::hasTable('bot_daily_ips')) {
            DB::table('bot_daily_ips')
                ->where('brand', 'spider360')
                ->update(['brand' => '360']);
        }
    }
};
