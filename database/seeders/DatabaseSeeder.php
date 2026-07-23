<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * Run all seeders: `php artisan db:seed`
 * Run one seeder: `php artisan db:seed --class=ProductSeeder`
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * When false, only framework seeders run and demo content (categories,
     * products, brands, articles, catalogs, tags) is skipped. Set by the
     * installer when the user opts out of demo data.
     */
    public static bool $withDemo = true;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            LocaleSeeder::class,
            CurrencySeeder::class,
            WeightClassSeeder::class,

            AdminSeeder::class,
            AttributeSeeder::class,
            CountrySeeder::class,
            CustomerGroupSeeder::class,
            PageSeeder::class,
            OptionSeeder::class,
            StateSeeder::class,
            RegionSeeder::class,
            TaxSeeder::class,
            PluginSeeder::class,
        ]);

        if (self::$withDemo) {
            $this->call([
                ArticleSeeder::class,
                BrandSeeder::class,
                CatalogSeeder::class,
                CategorySeeder::class,
                ProductSeeder::class,
                TagSeeder::class,
            ]);
        }

        touch(storage_path('installed'));
    }

    /**
     * Safely truncate a table by disabling foreign key checks.
     *
     * @param  string  $modelClass
     * @return void
     */
    protected function safeTruncate(string $modelClass): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $modelClass::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
