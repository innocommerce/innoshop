<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Repositories;

use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Common\Tests\TestCase;

/**
 * clearConfigCache() used to blank the "inno" config snapshot without
 * rebuilding it, so every later setting() read in the same request fell
 * back to the defaults.
 */
class SettingRepoCacheRebuildTest extends TestCase
{
    public function test_system_value_update_rebuilds_the_settings_snapshot(): void
    {
        SettingRepo::getInstance()->updateSystemValue('store_name', 'InnoShop');

        $this->assertIsArray(config('inno'), 'the settings snapshot must not be left empty');
        $this->assertSame('InnoShop', config('inno.system.store_name'));
        $this->assertSame('InnoShop', setting('system.store_name'));
    }

    public function test_bulk_value_update_rebuilds_the_settings_snapshot(): void
    {
        SettingRepo::getInstance()->updateValues([
            'store_name'     => 'Inno Store',
            'default_locale' => 'en',
        ], 'system');

        $this->assertSame('Inno Store', setting('system.store_name'));
        $this->assertSame('en', setting('system.default_locale'));
    }

    public function test_plugin_value_update_rebuilds_the_settings_snapshot(): void
    {
        SettingRepo::getInstance()->updatePluginValue('demo_plugin', 'enabled', '1');

        $this->assertSame('1', setting('demo_plugin.enabled'));
    }

    public function test_snapshot_reflects_the_value_that_was_written(): void
    {
        SettingRepo::getInstance()->updateSystemValue('store_name', 'First');

        $this->assertSame('First', setting('system.store_name'));

        SettingRepo::getInstance()->updateSystemValue('store_name', 'Second');

        $this->assertSame('Second', setting('system.store_name'), 'the snapshot must follow the latest write');
    }
}
