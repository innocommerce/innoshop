<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Extend option display types with free-text entries (input / textarea) for
 * C2B style customization requirements (innoshop/factory#145).
 */
return new class extends Migration
{
    private const ORIGINAL = "ENUM('select','radio','checkbox') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'select' COMMENT 'Display type'";

    private const EXTENDED = "ENUM('select','radio','checkbox','input','textarea') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'select' COMMENT 'Display type'";

    public function up(): void
    {
        $connection = Schema::getConnection();
        $table      = $connection->getTablePrefix().'options';

        if ($connection->getDriverName() === 'mysql') {
            $connection->statement("ALTER TABLE `{$table}` MODIFY `type` ".self::EXTENDED);

            return;
        }

        if ($connection->getDriverName() !== 'sqlite') {
            return;
        }

        // SQLite emulates enums via a CHECK constraint on TEXT. The original
        // 2025_09_15 migration set it to (select/radio/checkbox); we need to
        // extend it to allow input/textarea. SQLite cannot ALTER a CHECK
        // constraint, so rebuild the table with the new constraint and copy
        // data over (only test environments run this branch).
        $this->rebuildSqliteOptionsTable($connection, $table, self::EXTENDED);
    }

    public function down(): void
    {
        $connection = Schema::getConnection();
        if ($connection->getDriverName() === 'mysql') {
            $table = $connection->getTablePrefix().'options';
            $connection->statement("ALTER TABLE `{$table}` MODIFY `type` ".self::ORIGINAL);
        }
        // SQLite down() is intentionally a no-op: rolling back to the old
        // CHECK constraint is non-trivial and tests start from a fresh
        // database anyway.
    }

    /**
     * SQLite cannot ALTER CHECK constraints in place, so rebuild the table
     * with the new definition and migrate existing rows. The driver scope
     * already filtered out non-sqlite connections.
     */
    private function rebuildSqliteOptionsTable($connection, string $table, string $newTypeColumn): void
    {
        $original = "{$table}__options_backup";
        $connection->statement("ALTER TABLE {$table} RENAME TO {$original}");

        // Mirror the original 2025_09_15 schema so test environments stay
        // structurally aligned with MySQL: json() columns (TEXT in SQLite),
        // boolean defaults, and primary key bigIncrements.
        Schema::create('options', function ($table) {
            $table->comment('Product options for value-added services and customization items');
            $table->bigIncrements('id')->comment('Primary key');
            $table->json('name')->comment('Option name (multilingual JSON)');
            $table->json('description')->nullable()->comment('Option description (multilingual JSON)');
            $table->enum('type', ['select', 'radio', 'checkbox', 'input', 'textarea'])->default('select')->comment('Display type');
            $table->integer('position')->default(0)->comment('Display position');
            $table->boolean('active')->default(true)->comment('Is active status');
            $table->boolean('required')->default(false)->comment('Is required selection');
            $table->timestamps();
        });

        $connection->statement("INSERT INTO {$table} SELECT * FROM {$original}");
        $connection->statement("DROP TABLE {$original}");
    }
};
