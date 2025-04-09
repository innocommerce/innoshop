<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Image path migration command
 *
 * This command requires InnoShop version >= 0.4.0
 */
class MigrateImagePaths extends Command
{
    protected $signature = 'image:migrate-paths';

    protected $description = 'Migrate image paths from /public/catalog/ to /public/static/media/';

    /**
     * Tables and fields to be processed
     */
    protected array $tables = [
        'article_translations' => ['image'],
        'categories'           => ['image'],
        'products'             => ['images'],
        'product_skus'         => ['images'],
    ];

    /**
     * Old path
     */
    protected string $oldPath = '/catalog/';

    /**
     * New path
     */
    protected string $newPath = '/static/media/';

    public function handle(): void
    {
        // Check if system version is greater than or equal to 0.4.0
        $currentVersion = config('innoshop.version');
        if (version_compare($currentVersion, '0.4.0', '<')) {
            $this->error('This command requires InnoShop version 0.4.0 or higher.');
            $this->error('Current version: '.$currentVersion);

            return;
        }

        $this->info('Starting image path migration...');

        foreach ($this->tables as $table => $fields) {
            $this->migrateTable($table, $fields);
        }

        $this->info('Image path migration completed!');
    }

    /**
     * Migrate image paths for a specific table
     *
     * @param  string  $table  Table name
     * @param  array  $fields  Field list
     * @return void
     */
    private function migrateTable(string $table, array $fields): void
    {
        $this->info("Processing table: {$table}");
        $count = 0;

        foreach ($fields as $field) {
            $this->info("  - Processing field: {$field}");

            // Check if field type is JSON
            $isJson = in_array($field, ['images']);

            if ($isJson) {
                $this->migrateJsonField($table, $field, $count);
            } else {
                $this->migrateStringField($table, $field, $count);
            }
        }

        $this->info("Table {$table} processing completed, updated {$count} records");
    }

    /**
     * Migrate image paths for JSON type fields
     *
     * @param  string  $table  Table name
     * @param  string  $field  Field name
     * @param  int  $count  Counter reference
     * @return void
     */
    private function migrateJsonField(string $table, string $field, int &$count): void
    {
        $records = DB::table($table)
            ->whereNotNull($field)
            ->where($field, '!=', '[]')
            ->get();

        foreach ($records as $record) {
            $images = json_decode($record->$field, true) ?: [];

            if (empty($images)) {
                continue;
            }

            $updated   = false;
            $newImages = [];

            foreach ($images as $index => $image) {
                if (is_string($image) && Str::startsWith($image, $this->oldPath)) {
                    $newImages[$index] = Str::replaceFirst($this->oldPath, $this->newPath, $image);
                    $updated           = true;
                } else {
                    $newImages[$index] = $image;
                }
            }

            if ($updated) {
                DB::table($table)
                    ->where('id', $record->id)
                    ->update([
                        $field => json_encode($newImages),
                    ]);

                $count++;
                if ($count % 100 === 0) {
                    $this->info("    Processed {$count} records");
                }
            }
        }
    }

    /**
     * Migrate image paths for string type fields
     *
     * @param  string  $table  Table name
     * @param  string  $field  Field name
     * @param  int  $count  Counter reference
     * @return void
     */
    private function migrateStringField(string $table, string $field, int &$count): void
    {
        $records = DB::table($table)
            ->whereNotNull($field)
            ->where($field, 'like', "%{$this->oldPath}%")
            ->get();

        foreach ($records as $record) {
            $oldValue = $record->$field;
            $newValue = Str::replaceFirst($this->oldPath, $this->newPath, $oldValue);

            if ($oldValue !== $newValue) {
                DB::table($table)
                    ->where('id', $record->id)
                    ->update([
                        $field => $newValue,
                    ]);

                $count++;
                if ($count % 100 === 0) {
                    $this->info("    Processed {$count} records");
                }
            }
        }
    }
}
