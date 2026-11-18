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
        if (! Schema::hasTable('bot_daily')) {
            Schema::create('bot_daily', function (Blueprint $table) {
                $table->comment('Bot/Crawler daily stats — bots never hit the visits table, counted inline');

                $table->bigIncrements('id');
                $table->date('date');
                $table->string('bot_brand', 50)->comment('Brand code from BotClassifier (google, openai, curl, unknown...)');
                $table->string('category', 30)->comment('4-bucket: seo / geo / other / suspicious');

                $table->unsignedInteger('visits')->default(0)->comment('Bot requests on this date');
                $table->unsignedInteger('page_views')->default(0)->comment('GET requests counted as page views');
                $table->unsignedInteger('ip_count')->default(0)->comment('Kept for parity; distinct IPs are read live from bot_daily_ips');
                $table->string('last_ip', 45)->nullable()->comment('Last request IP (troubleshooting hint)');
                $table->string('last_path', 255)->nullable()->comment('Last request path (troubleshooting hint, truncated)');

                $table->unique(['date', 'bot_brand'], 'uq_bot_daily_date_brand');
                $table->index('category', 'idx_bot_daily_category');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('bot_daily_ips')) {
            Schema::create('bot_daily_ips', function (Blueprint $table) {
                $table->comment('Bot daily distinct IPs — rolling 30d window, pruned nightly. Brand dimension is required: the panel breakdown reports per-brand unique IPs.');

                $table->date('date');
                $table->string('ip', 45);
                $table->string('brand', 50);

                $table->primary(['date', 'ip', 'brand']);
            });
        }

        // Drop redundant single-column indexes: their left-most prefixes are
        // already covered by v_country_time / v_device_time composites, and
        // locale has no independent filter path.
        Schema::table('visits', function (Blueprint $table) {
            foreach (['v_locale', 'v_device_type', 'v_country_code'] as $index) {
                if (Schema::hasIndex('visits', $index)) {
                    $table->dropIndex($index);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_daily_ips');
        Schema::dropIfExists('bot_daily');

        Schema::table('visits', function (Blueprint $table) {
            $table->index('locale', 'v_locale');
            $table->index('device_type', 'v_device_type');
            $table->index('country_code', 'v_country_code');
        });
    }
};
