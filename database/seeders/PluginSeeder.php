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
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Plugin\Models\Plugin;
use Throwable;

class PluginSeeder extends Seeder
{
    /**
     * @return void
     * @throws Throwable
     */
    public function run(): void
    {
        $items = $this->getItems();
        if ($items) {
            Plugin::query()->truncate();
            foreach ($items as $item) {
                Plugin::query()->create($item);
            }
        }

        $settings = $this->getSettings();
        if ($settings) {
            foreach ($settings as $setting) {
                $space = $setting['space'] ?? 'system';
                SettingRepo::getInstance()->updatePluginValue($space, $setting['name'], $setting['value']);
            }
        }
    }

    /**
     * @return array
     */
    private function getItems(): array
    {
        return [
            ['type' => 'shipping', 'code' => 'fixed_shipping', 'priority' => 0],
            ['type' => 'billing', 'code' => 'bank_transfer', 'priority' => 0],
        ];
    }

    /**
     * @return array
     */
    private function getSettings(): array
    {
        return [
            ['space' => 'fixed_shipping', 'name' => 'active', 'value' => '1'],
            ['space' => 'fixed_shipping', 'name' => 'type', 'value' => 'fixed'],
            ['space' => 'fixed_shipping', 'name' => 'value', 'value' => '2'],
            ['space' => 'bank_transfer', 'name' => 'active', 'value' => '1'],
            ['space' => 'bank_transfer', 'name' => 'bank_name', 'value' => 'Bank of America'],
            ['space' => 'bank_transfer', 'name' => 'bank_account', 'value' => '12345678910'],
            ['space' => 'bank_transfer', 'name' => 'bank_username', 'value' => 'Sam'],
            ['space' => 'bank_transfer', 'name' => 'bank_comment', 'value' => ''],
            ['space' => 'bank_transfer', 'name' => 'available', 'value' => ['pc_web', 'mobile_web']],
        ];
    }
}
