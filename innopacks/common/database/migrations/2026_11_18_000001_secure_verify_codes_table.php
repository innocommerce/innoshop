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
        Schema::table('verify_codes', function (Blueprint $table) {
            $table->string('code', 255)->change();
            $table->unsignedTinyInteger('attempts')->default(0)->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('verify_codes', function (Blueprint $table) {
            $table->dropColumn('attempts');
            $table->string('code', 16)->change();
        });
    }
};
