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
        Schema::table('verify_codes', function (Blueprint $table) {
            if (! Schema::hasColumn('verify_codes', 'type')) {
                $table->string('type', 20)->default('register')->after('code')->comment('Verification Type: register, login, reset');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('verify_codes', function (Blueprint $table) {
            if (Schema::hasColumn('verify_codes', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
