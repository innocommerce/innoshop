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

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('database.connections.mysql.prefix', '');
        DB::statement("UPDATE {$prefix}plugins SET type = 'payment' WHERE type = 'billing'");
    }

    public function down(): void
    {
        $prefix = config('database.connections.mysql.prefix', '');
        DB::statement("UPDATE {$prefix}plugins SET type = 'billing' WHERE type = 'payment'");
    }
};
