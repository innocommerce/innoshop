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
use InnoShop\Common\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getBrands();
        if ($items) {
            Brand::query()->truncate();
            foreach ($items as $item) {
                Brand::query()->create($item);
            }
        }
    }

    private function getBrands(): array
    {
        return [
            [
                'name'     => 'Adidas',
                'slug'     => 'adidas',
                'first'    => 'A',
                'logo'     => 'images/brands/adidas.png',
                'position' => 0,
                'active'   => true,
            ],
            [
                'name'     => 'Nike',
                'slug'     => 'nike',
                'first'    => 'N',
                'logo'     => 'images/brands/nike.png',
                'position' => 1,
                'active'   => true,
            ],
        ];
    }
}
