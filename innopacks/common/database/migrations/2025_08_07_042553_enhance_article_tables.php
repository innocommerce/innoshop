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
     * Run the migrations
     * Enhance article related tables: add main image field and create article relations table
     */
    public function up(): void
    {
        // 1. Add image field to articles main table
        if (Schema::hasTable('articles') && ! Schema::hasColumn('articles', 'image')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->string('image')->nullable()->after('author')->comment('Article Main Image');
            });
        }

        // 2. Create article relations table
        if (! Schema::hasTable('article_relations')) {
            Schema::create('article_relations', function (Blueprint $table) {
                $table->comment('Article Relations');
                $table->bigIncrements('id')->comment('ID');
                $table->integer('article_id')->index('ar_article_id')->comment('Article ID');
                $table->integer('relation_id')->index('ar_relation_id')->comment('Related Article ID');
                $table->timestamps();

                // Add unique index to prevent duplicate relations
                $table->unique(['article_id', 'relation_id'], 'unique_article_relation');
            });
        }
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        // Remove image field from articles table
        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'image')) {
            Schema::table('articles', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }

        // Drop article_relations table
        Schema::dropIfExists('article_relations');
    }
};
