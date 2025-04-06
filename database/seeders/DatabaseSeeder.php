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

class DatabaseSeeder extends Seeder
{
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
            ArticleSeeder::class,
            AttributeSeeder::class,
            BrandSeeder::class,
            CatalogSeeder::class,
            CategorySeeder::class,
            CountrySeeder::class,
            CustomerGroupSeeder::class,
            PageSeeder::class,
            ProductSeeder::class,
            StateSeeder::class,
            TagSeeder::class,
            RegionSeeder::class,
            TaxSeeder::class,
            PluginSeeder::class,
        ]);

        touch(storage_path('installed'));
    }
}
