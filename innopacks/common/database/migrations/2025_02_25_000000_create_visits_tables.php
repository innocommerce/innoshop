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
     * Creates visits, visit_events, visit_daily, and conversion_daily tables.
     *
     * @return void
     */
    public function up(): void
    {
        // Visits table - Session-level aggregated visit statistics
        if (! Schema::hasTable('visits')) {
            Schema::create('visits', function (Blueprint $table) {
                $table->comment('Visits - Session-level aggregated visit statistics');

                $table->bigIncrements('id')->comment('Primary Key');
                $table->string('session_id', 64)->unique('v_session_id_unique')->comment('Session ID (unique per session)');

                $table->unsignedInteger('customer_id')->nullable()->index('v_customer_id')->comment('Customer ID (if logged in)');
                $table->string('ip_address', 45)->index('v_ip_address')->comment('IP Address (for UV calculation)');

                $table->string('country_code', 2)->nullable()->index('v_country_code')->comment('Country Code (ISO 3166-1 alpha-2)');
                $table->string('country_name', 100)->nullable()->comment('Country Name');
                $table->string('city', 100)->nullable()->comment('City Name');

                $table->string('device_type', 20)->nullable()->index('v_device_type')->comment('Device Type: desktop, mobile, tablet');
                $table->string('browser', 50)->nullable()->comment('Browser Name');
                $table->string('os', 50)->nullable()->comment('Operating System');
                $table->text('user_agent')->nullable()->comment('User Agent String');

                $table->string('referrer', 1000)->nullable()->comment('Referrer URL (first visit)');
                $table->string('locale', 10)->nullable()->index('v_locale')->comment('Locale Code');
                $table->timestamp('first_visited_at')->index('v_first_visited_at')->comment('First Visit Time');
                $table->timestamp('last_visited_at')->index('v_last_visited_at')->comment('Last Visit Time');

                $table->timestamps();

                $table->index(['first_visited_at', 'last_visited_at'], 'v_visit_time_range');
                $table->index(['country_code', 'first_visited_at'], 'v_country_time');
                $table->index(['device_type', 'first_visited_at'], 'v_device_time');
                $table->index(['customer_id', 'first_visited_at'], 'v_customer_time');
                $table->index(['ip_address', 'first_visited_at'], 'v_ip_time');
            });
        }

        // Visit events table - Event tracking for conversion funnel analysis
        if (! Schema::hasTable('visit_events')) {
            Schema::create('visit_events', function (Blueprint $table) {
                $table->comment('Visit Events - Event tracking for conversion funnel analysis');

                $table->bigIncrements('id')->comment('Primary Key');
                $table->string('session_id', 64)->index('ve_session_id')->comment('Session ID (links to visits table via session_id)');

                $table->string('event_type', 50)->index('ve_event_type')->comment('Event Type: product_view, add_to_cart, checkout_start, order_placed, payment_completed, register');
                $table->json('event_data')->nullable()->comment('Event Data (JSON)');

                $table->unsignedInteger('customer_id')->nullable()->index('ve_customer_id')->comment('Customer ID (if logged in)');
                $table->string('ip_address', 45)->index('ve_ip_address')->comment('IP Address');

                $table->string('page_url', 1000)->nullable()->comment('Page URL where event occurred');
                $table->string('referrer', 1000)->nullable()->comment('Referrer URL');

                $table->timestamps();

                $table->index(['session_id', 'event_type'], 've_session_event');
                $table->index(['event_type', 'created_at'], 've_event_time');
                $table->index(['customer_id', 'created_at'], 've_customer_time');
                $table->index(['created_at'], 've_created_at');
            });
        }

        // Daily visit statistics table
        if (! Schema::hasTable('visit_daily')) {
            Schema::create('visit_daily', function (Blueprint $table) {
                $table->comment('Daily Visit Statistics - Aggregated from visits and visit_events');
                $table->date('date')->primary()->comment('Statistics date');
                $table->unsignedInteger('pv')->default(0)->comment('Page Views - product_view events');
                $table->unsignedInteger('uv')->default(0)->comment('Unique Visitors - distinct session_id');
                $table->unsignedInteger('ip')->default(0)->comment('Unique IPs - distinct ip_address');
                $table->unsignedInteger('new_visitors')->default(0)->comment('New Visitors (no previous visit)');
                $table->unsignedInteger('bounces')->default(0)->comment('Bounces - single page view sessions');
                $table->unsignedInteger('avg_duration')->default(0)->comment('Average Session Duration (seconds)');

                // Device breakdown
                $table->unsignedInteger('desktop_pv')->default(0)->comment('Desktop Page Views');
                $table->unsignedInteger('mobile_pv')->default(0)->comment('Mobile Page Views');
                $table->unsignedInteger('tablet_pv')->default(0)->comment('Tablet Page Views');

                $table->timestamps();
            });
        }

        // Daily conversion statistics table
        if (! Schema::hasTable('conversion_daily')) {
            Schema::create('conversion_daily', function (Blueprint $table) {
                $table->comment('Daily Conversion Statistics - Conversion funnel metrics');
                $table->date('date')->primary()->comment('Statistics date');

                // Funnel stage counts
                $table->unsignedInteger('product_views')->default(0)->comment('Product Views');
                $table->unsignedInteger('add_to_carts')->default(0)->comment('Add to Cart Events');
                $table->unsignedInteger('checkout_starts')->default(0)->comment('Checkout Start Events');
                $table->unsignedInteger('order_placed')->default(0)->comment('Orders Placed');
                $table->unsignedInteger('payment_completed')->default(0)->comment('Payments Completed');
                $table->unsignedInteger('registers')->default(0)->comment('User Registrations');

                // Conversion rates (percentage * 100 for precision, e.g., 25.5% = 2550)
                $table->unsignedInteger('cart_to_checkout_rate')->default(0)->comment('Cart → Checkout % (x100)');
                $table->unsignedInteger('checkout_to_order_rate')->default(0)->comment('Checkout → Order % (x100)');
                $table->unsignedInteger('order_to_payment_rate')->default(0)->comment('Order → Payment % (x100)');
                $table->unsignedInteger('overall_conversion_rate')->default(0)->comment('View → Payment % (x100)');

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('conversion_daily');
        Schema::dropIfExists('visit_daily');
        Schema::dropIfExists('visit_events');
        Schema::dropIfExists('visits');
    }
};
