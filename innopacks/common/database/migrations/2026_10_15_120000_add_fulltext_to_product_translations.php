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
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Front keyword search probes product_translations via EXISTS + LIKE '%kw%'
     * for every product row (~12K probes × 11 locale rows = 134K row scans of
     * string matching). A ngram FULLTEXT index on name turns each probe into an
     * index lookup, and lets the repo use MATCH ... AGAINST in BOOLEAN MODE.
     *
     * Requires MySQL >= 5.7.6 (built-in ngram parser). 12K×11 rows builds in
     * seconds; InnoDB does it online without a blocking lock.
     */
    public function up(): void
    {
        // ngram FULLTEXT is MySQL-only (>= 5.7.6); SQLite test connections skip it.
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $table = Schema::getConnection()->getTablePrefix().'product_translations';

        if ($this->indexExists($table, 'ft_pt_name')) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` ADD FULLTEXT INDEX `ft_pt_name` (`name`) WITH PARSER ngram");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $table = Schema::getConnection()->getTablePrefix().'product_translations';

        if (! $this->indexExists($table, 'ft_pt_name')) {
            return;
        }

        DB::statement("ALTER TABLE `{$table}` DROP INDEX `ft_pt_name`");
    }

    /**
     * @param  string  $table
     * @param  string  $index
     * @return bool
     */
    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

        return ! empty($rows);
    }
};
