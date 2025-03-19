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
        if (! Schema::hasTable('faqs')) {
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();
                $table->integer('faq_category_id')->nullable()->default(0);
                $table->boolean('active')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('faq_translations')) {
            Schema::create('faq_translations', function (Blueprint $table) {
                $table->id();
                $table->integer('faq_id');
                $table->string('locale');
                $table->string('question');
                $table->text('answer');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('faq_categories')) {
            Schema::create('faq_categories', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id')->nullable()->default(0);
                $table->integer('article_id')->nullable()->default(0);
                $table->string('title');
                $table->boolean('active')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('faq_translations');
        Schema::dropIfExists('faq_categories');
    }
};
