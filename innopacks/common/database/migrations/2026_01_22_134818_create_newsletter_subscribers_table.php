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
        if (! Schema::hasTable('newsletter_subscribers')) {
            Schema::create('newsletter_subscribers', function (Blueprint $table) {
                $table->comment('Newsletter Subscribers');
                $table->bigIncrements('id')->comment('ID');
                $table->string('email')->unique()->comment('Email Address');
                $table->string('name')->nullable()->comment('Subscriber Name');
                $table->unsignedInteger('customer_id')->nullable()->index('customer_id')->comment('Customer ID (if registered)');
                $table->string('status')->default('active')->comment('Status: active, unsubscribed, bounced');
                $table->string('source')->nullable()->comment('Subscription Source: footer, popup, checkout, etc.');
                $table->timestamp('subscribed_at')->nullable()->comment('Subscription Date');
                $table->timestamp('unsubscribed_at')->nullable()->comment('Unsubscription Date');
                $table->text('notes')->nullable()->comment('Admin Notes');
                $table->timestamps();

                $table->index(['status', 'created_at']);
                $table->index('email');
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
        Schema::dropIfExists('newsletter_subscribers');
    }
};
