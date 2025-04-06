<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WeightClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $weightClasses = [
            [
                'id'         => 1,
                'code'       => 'g',
                'name'       => 'Gram',
                'unit'       => 'g',
                'value'      => 1.00000000,
                'position'   => 1,
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 2,
                'code'       => 'kg',
                'name'       => 'Kilogram',
                'unit'       => 'kg',
                'value'      => 1000.00000000,
                'position'   => 2,
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 3,
                'code'       => 'lb',
                'name'       => 'Pound',
                'unit'       => 'lb',
                'value'      => 453.59237000,
                'position'   => 3,
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => 4,
                'code'       => 'oz',
                'name'       => 'Ounce',
                'unit'       => 'oz',
                'value'      => 28.34952000,
                'position'   => 4,
                'active'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('weight_classes')->truncate();
        foreach ($weightClasses as $weightClass) {
            DB::table('weight_classes')->insert($weightClass);
        }

        // Add default weight class setting
        DB::table('settings')->insert([
            'space'      => 'system',
            'name'       => 'default_weight_class',
            'value'      => 'g',
            'json'       => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
