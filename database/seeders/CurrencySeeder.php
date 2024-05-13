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
use InnoShop\Common\Models\Currency;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getCurrencies();
        if ($items) {
            Currency::query()->truncate();
            foreach ($items as $item) {
                Currency::query()->create($item);
            }
        }
    }

    /**
     * @return array[]
     */
    private function getCurrencies(): array
    {
        return [
            ['name' => 'USD', 'code' => 'usd', 'symbol_left' => '$', 'symbol_right' => '', 'decimal_place' => 2, 'value' => 1, 'active' => 1],
            ['name' => '人民币', 'code' => 'cny', 'symbol_left' => '￥', 'symbol_right' => '', 'decimal_place' => 2, 'value' => 7.2, 'active' => 1],
        ];
    }
}
