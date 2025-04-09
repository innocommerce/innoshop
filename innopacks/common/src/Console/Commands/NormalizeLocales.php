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
use Illuminate\Support\Facades\Schema;
use InnoShop\Common\Models\Admin;
use InnoShop\Common\Models\Locale;
use InnoShop\Common\Models\Setting;

/**
 * Locale normalization command
 *
 * This command requires InnoShop version >= 0.4.0
 */
class NormalizeLocales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locale:normalize {--table= : Only process specific table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace zh_cn with zh-cn and zh_hk with zh-hk in all locale fields across the database';

    /**
     * The locales to replace
     *
     * @var array
     */
    protected $localeMap = [
        'zh_cn' => 'zh-cn',
        'zh_hk' => 'zh-hk',
    ];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        // Check if system version is greater than or equal to 0.4.0
        $currentVersion = config('innoshop.version');
        if (version_compare($currentVersion, '0.4.0', '<')) {
            $this->error('This command requires InnoShop version 0.4.0 or higher.');
            $this->error('Current version: '.$currentVersion);

            return;
        }

        $this->info('Starting locale normalization...');

        // Process admin and settings tables
        $this->updateAdminLocales();
        $this->updateFrontLocaleSetting();
        $this->updateLocalesCodes();

        // Process all translation tables and other tables with locale fields
        $this->updateLocaleFields();

        $this->info('Locale normalization completed.');
    }

    /**
     * Update admin locales
     *
     * @return void
     */
    private function updateAdminLocales(): void
    {
        $this->info('Updating admin locales...');
        $admins = Admin::whereIn('locale', array_keys($this->localeMap))->get();
        foreach ($admins as $admin) {
            $oldLocale     = $admin->locale;
            $admin->locale = $this->localeMap[$oldLocale] ?? $oldLocale;
            $admin->save();
        }
        $this->info('Admin locales updated: '.$admins->count());
    }

    /**
     * Update front locale setting
     *
     * @return void
     */
    private function updateFrontLocaleSetting(): void
    {
        $this->info('Updating front_locale setting...');
        $frontLocaleSetting = Setting::where('name', 'front_locale')
            ->whereIn('value', array_keys($this->localeMap))
            ->first();
        if ($frontLocaleSetting) {
            $oldValue                  = $frontLocaleSetting->value;
            $frontLocaleSetting->value = $this->localeMap[$oldValue] ?? $oldValue;
            $frontLocaleSetting->save();
            $this->info('Front locale setting updated');
        }
    }

    /**
     * Update locales table codes and images
     *
     * @return void
     */
    private function updateLocalesCodes(): void
    {
        $this->info('Updating locales table...');
        $locales = Locale::whereIn('code', array_keys($this->localeMap))->get();
        foreach ($locales as $locale) {
            $oldCode = $locale->code;
            $newCode = $this->localeMap[$oldCode] ?? $oldCode;

            // Update image path if it contains the locale code
            if (strpos($locale->image, $oldCode) !== false) {
                $locale->image = str_replace($oldCode, $newCode, $locale->image);
            }

            $locale->code = $newCode;
            $locale->save();
        }
        $this->info('Locale codes updated: '.$locales->count());
    }

    /**
     * Update locale fields in translation tables and other specified tables
     *
     * @return void
     */
    private function updateLocaleFields(): void
    {
        $this->info('Processing tables with locale fields...');

        // List of tables with locale fields to process
        $tablesToProcess = [
            // Translation tables
            'article_translations',
            'attribute_group_translations',
            'attribute_translations',
            'attribute_value_translations',
            'catalog_translations',
            'category_translations',
            'customer_group_translations',
            'customers',
            'orders',
            'page_translations',
            'product_translations',
            'tag_translations',
        ];

        // Get specific table from option if provided
        $specificTable = $this->option('table');
        if ($specificTable) {
            if (in_array($specificTable, $tablesToProcess)) {
                $tablesToProcess = [$specificTable];
            } else {
                $this->warn("Table '{$specificTable}' is not in the list of tables to process.");

                return;
            }
        }

        foreach ($tablesToProcess as $table) {
            // Check if table exists
            if (! Schema::hasTable($table)) {
                $this->warn("Table '{$table}' does not exist, skipping...");

                continue;
            }

            // Check if the table has a locale column
            if (! Schema::hasColumn($table, 'locale')) {
                $this->warn("Table '{$table}' does not have a locale column, skipping...");

                continue;
            }

            $this->info("Processing table: {$table}");

            // Get records that need updating
            $records = DB::table($table)
                ->whereIn('locale', array_keys($this->localeMap))
                ->get();

            $this->info("  Found {$records->count()} records to update");

            foreach ($records as $record) {
                $oldValue = $record->locale;
                $newValue = $this->localeMap[$oldValue] ?? $oldValue;

                DB::table($table)
                    ->where('id', $record->id)
                    ->update(['locale' => $newValue]);
            }

            $this->info("  Updated {$records->count()} records in {$table}");
        }
    }
}
