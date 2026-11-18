<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Feature;

use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Plugin\Core\PluginManager;
use InnoShop\Plugin\Models\Plugin;
use InnoShop\Plugin\Models\Setting;
use InnoShop\Plugin\Repositories\PluginRepo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Reading of plugin settings (issue #370) and normalisation of the array
 * shaped "available" setting (issue #379).
 *
 * Settings are saved under the raw plugin code (PluginController::update),
 * so the lookup must start with that code instead of a converted one.
 */
class PluginSettingLookupTest extends TestCase
{
    #[Test]
    public function test_setting_is_found_under_the_raw_plugin_code(): void
    {
        $this->storeSetting('PayPal', 'available', '["pc_web"]');

        $this->assertSame('["pc_web"]', plugin_setting('PayPal', 'available'));
    }

    #[Test]
    public function test_setting_falls_back_to_the_snake_case_code(): void
    {
        $this->storeSetting('pay_pal', 'active', '1');

        $this->assertSame('1', plugin_setting('PayPal', 'active'));
    }

    #[Test]
    public function test_snake_case_plugins_keep_working(): void
    {
        $this->storeSetting('pay_pal', 'active', '1');

        $this->assertSame('1', plugin_setting('pay_pal', 'active'));
    }

    #[Test]
    public function test_the_raw_code_wins_over_the_snake_case_code(): void
    {
        $this->storeSetting('PayPal', 'active', '1');
        $this->storeSetting('pay_pal', 'active', '0');

        $this->assertSame('1', plugin_setting('PayPal', 'active'));
        $this->assertSame('0', plugin_setting('pay_pal', 'active'));
    }

    #[Test]
    public function test_missing_setting_returns_the_default(): void
    {
        $this->assertSame('fallback', plugin_setting('not_installed', 'active', 'fallback'));
        $this->assertSame([], plugin_setting_array('not_installed', 'available'));
    }

    #[Test]
    public function test_array_setting_decodes_a_json_string(): void
    {
        $this->storeSetting('PayPal', 'available', '["pc_web","app"]');

        $this->assertSame(['pc_web', 'app'], plugin_setting_array('PayPal', 'available'));
    }

    #[Test]
    public function test_array_setting_of_a_plain_string_is_empty(): void
    {
        $this->storeSetting('PayPal', 'available', 'pc_web');

        $this->assertSame([], plugin_setting_array('PayPal', 'available'));
    }

    #[Test]
    public function test_get_billing_methods_survives_a_json_string_available_setting(): void
    {
        $this->prepareEnabledPaymentPlugins();

        $codes = PluginRepo::getInstance()->getBillingMethods()->map(fn ($plugin) => $plugin->code)->values()->toArray();

        $this->assertContains('pay_pal', $codes);
        $this->assertContains('bank_transfer', $codes);
    }

    /**
     * Store a setting row and refresh the config snapshot it is read from.
     *
     * @param  string  $space
     * @param  string  $name
     * @param  string  $value
     */
    private function storeSetting(string $space, string $name, string $value): void
    {
        Setting::query()->create([
            'space' => $space,
            'name'  => $name,
            'value' => $value,
            'json'  => false,
        ]);

        config(['inno' => SettingRepo::getInstance()->groupedSettings()]);
    }

    /**
     * Install bank_transfer + pay_pal and make both look enabled and available
     * on the storefront. The "available" value is stored as a plain string
     * instead of a JSON flagged row, which is how a plugin setting can reach
     * the checkout as a string.
     */
    private function prepareEnabledPaymentPlugins(): void
    {
        if (! is_dir(base_path('plugins/BankTransfer')) || ! is_dir(base_path('plugins/PayPal'))) {
            $this->markTestSkipped('bank_transfer / pay_pal plugin directories are not present');
        }

        Plugin::query()->create(['type' => 'payment', 'code' => 'bank_transfer', 'priority' => 0]);
        Plugin::query()->create(['type' => 'payment', 'code' => 'pay_pal', 'priority' => 0]);

        foreach (['bank_transfer', 'pay_pal'] as $code) {
            Setting::query()->create(['space' => $code, 'name' => 'active', 'value' => '1', 'json' => false]);
            Setting::query()->create([
                'space' => $code,
                'name'  => 'available',
                'value' => json_encode(['pc_web']),
                'json'  => false,
            ]);
        }

        PluginRepo::resetCache();
        config(['inno' => SettingRepo::getInstance()->groupedSettings()]);

        // Plugin instances (and their enabled flag) are built once per request
        // from the settings snapshot; drop the cache so they pick up the rows
        // inserted above, the same way a fresh request would.
        $plugins = new \ReflectionProperty(PluginManager::class, 'plugins');
        $plugins->setAccessible(true);
        $plugins->setValue(null, null);
    }
}
