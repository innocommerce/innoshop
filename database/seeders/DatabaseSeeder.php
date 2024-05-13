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
            AdminSeeder::class,
            ArticleSeeder::class,
            AttributeSeeder::class,
            BrandSeeder::class,
            CatalogSeeder::class,
            CategorySeeder::class,
            CountrySeeder::class,
            CurrencySeeder::class,
            CustomerGroupSeeder::class,
            LocaleSeeder::class,
            PageSeeder::class,
            ProductSeeder::class,
            SettingSeeder::class,
            StateSeeder::class,
            TagSeeder::class,
            RegionSeeder::class,
            TaxSeeder::class,
        ]);

        touch(storage_path('installed'));
    }
}
