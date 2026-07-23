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
        Schema::create('media_files', function (Blueprint $table) {
            $table->comment('Media Library File');
            $table->bigIncrements('id')->comment('Media ID');
            $table->string('disk', 32)->default('local')->comment('Storage Disk (local/oss/cos/qiniu/s3/obs/r2/minio)');
            $table->string('storage_key', 500)->comment('Storage Key (path relative to disk root, including static/media/ prefix)');
            $table->string('original_name', 255)->nullable()->comment('Original File Name from client');
            $table->char('checksum', 64)->nullable()->comment('SHA-256 Hash for dedup');
            $table->string('mime', 128)->nullable()->comment('MIME Type');
            $table->unsignedBigInteger('size')->default(0)->comment('File Size in Bytes');
            $table->unsignedInteger('width')->nullable()->comment('Image Width');
            $table->unsignedInteger('height')->nullable()->comment('Image Height');
            $table->string('alt', 255)->nullable()->comment('Alt Text');
            $table->string('source', 32)->default('upload')->comment('Source (upload/url_import/legacy)');
            $table->timestamps();
            $table->softDeletes();

            $table->index('storage_key');
            $table->index('checksum');
            $table->index('disk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};
