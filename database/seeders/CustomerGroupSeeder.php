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
use InnoShop\Common\Models\Customer\Group as CustomerGroup;

class CustomerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getCustomerGroups();
        if ($items) {
            CustomerGroup::query()->truncate();
            CustomerGroup\Translation::query()->truncate();
            foreach ($items as $item) {
                $translations  = array_pop($item);
                $customerGroup = CustomerGroup::query()->create($item);
                $customerGroup->translations()->createMany($translations);
            }
        }
    }

    private function getCustomerGroups(): array
    {
        return [
            [
                'level'         => 1,
                'mini_cost'     => 0,
                'discount_rate' => 100,
                'translations'  => [
                    ['locale' => 'en', 'name' => 'Account', 'description' => 'Account'],
                    ['locale' => 'zh_cn', 'name' => '会员', 'description' => '会员等级'],
                ],
            ],
            [
                'level'         => 2,
                'mini_cost'     => 1000,
                'discount_rate' => 95,
                'translations'  => [
                    ['locale' => 'en', 'name' => 'VIP', 'description' => 'VIP'],
                    ['locale' => 'zh_cn', 'name' => 'VIP', 'description' => 'VIP'],
                ],
            ],
        ];
    }
}
