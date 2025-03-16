<?php

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
        Schema::create('cloaks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('target_url')->comment('URL to show to regular visitors');
            $table->string('safe_url')->nullable()->comment('URL to show to bots and ad reviewers');
            $table->boolean('is_active')->default(true);
            $table->json('ip_filters')->nullable()->comment('IP addresses or ranges to filter');
            $table->json('country_filters')->nullable()->comment('Countries to filter');
            $table->json('user_agent_filters')->nullable()->comment('User agents to filter');
            $table->json('referrer_filters')->nullable()->comment('Referrers to filter');
            $table->boolean('detect_bots')->default(true)->comment('Automatically detect and filter bots');
            $table->boolean('one_time_redirect')->default(false)->comment('Show target URL only on first visit');
            $table->integer('visits_count')->default(0)->comment('Total visits count');
            $table->integer('redirects_count')->default(0)->comment('Total redirects to target URL');
            $table->string('utm_source')->nullable()->comment('UTM source parameter');
            $table->string('utm_medium')->nullable()->comment('UTM medium parameter');
            $table->string('utm_campaign')->nullable()->comment('UTM campaign parameter');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloaks');
    }
};
