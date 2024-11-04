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
use InnoShop\Common\Models\Locale;

class LocaleSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getLocales();
        if ($items) {
            Locale::query()->truncate();
            foreach ($items as $item) {
                Locale::query()->create($item);
            }
        }
    }

    private function getLocales(): array
    {
        return [
            [
                'name'     => 'English',
                'code'     => 'en',
                'image'    => 'images/flag/en.png',
                'position' => 0,
                'active'   => 1,
            ],
            [
                'name'     => '简体中文',
                'code'     => 'zh_cn',
                'image'    => 'images/flag/zh_cn.png',
                'position' => 1,
                'active'   => 1,
            ],
        ];
    }
}
