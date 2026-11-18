<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Plugin\Controllers\PluginController;
use InnoShop\Plugin\Core\PluginManager;
use InnoShop\Plugin\Models\Plugin;
use InnoShop\Plugin\Models\Setting;
use InnoShop\Plugin\Repositories\PluginRepo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Storage + endpoint behaviour of the per-plugin-type display order (issue #1).
 *
 * getTypeOrder() reads the settings row straight from the database on purpose:
 * system_setting() resolves through the boot-time config snapshot, which is
 * stale for the rest of the request right after a write.
 */
class PluginTypeOrderSettingTest extends TestCase
{
    #[Test]
    public function test_type_order_is_empty_by_default(): void
    {
        $this->assertSame([], PluginRepo::getInstance()->getTypeOrder('payment'));
    }

    #[Test]
    public function test_save_type_order_is_readable_again(): void
    {
        PluginRepo::getInstance()->saveTypeOrder('payment', ['pay_pal', 'bank_transfer']);

        $this->assertSame(['pay_pal', 'bank_transfer'], PluginRepo::getInstance()->getTypeOrder('payment'));
    }

    #[Test]
    public function test_save_type_order_keeps_codes_missing_from_payload(): void
    {
        $repo = PluginRepo::getInstance();
        $repo->saveTypeOrder('payment', ['pay_pal', 'bank_transfer']);

        // A code disappears from the panel (plugin dir removed): it must stay
        // in storage so that restoring it puts it back where the operator left it.
        $repo->saveTypeOrder('payment', ['bank_transfer']);

        $this->assertSame(['bank_transfer', 'pay_pal'], $repo->getTypeOrder('payment'));
    }

    #[Test]
    public function test_save_type_order_does_not_touch_other_types(): void
    {
        $repo = PluginRepo::getInstance();
        $repo->saveTypeOrder('shipping', ['flat_shipping']);
        $repo->saveTypeOrder('payment', ['pay_pal']);

        $this->assertSame(['flat_shipping'], $repo->getTypeOrder('shipping'));
        $this->assertSame(['pay_pal'], $repo->getTypeOrder('payment'));
    }

    #[Test]
    public function test_reorder_rejects_unknown_type(): void
    {
        $request = Request::create('/panel/plugins/reorder', 'POST', [
            'type'  => 'not_a_type',
            'codes' => ['pay_pal'],
        ]);

        $payload = (new PluginController)->reorder($request)->getData(true);

        $this->assertFalse($payload['success']);
        $this->assertSame([], PluginRepo::getInstance()->getTypeOrder('payment'));
    }

    #[Test]
    public function test_reorder_rejects_non_array_codes(): void
    {
        $request = Request::create('/panel/plugins/reorder', 'POST', [
            'type'  => 'payment',
            'codes' => 'pay_pal',
        ]);

        $payload = (new PluginController)->reorder($request)->getData(true);

        $this->assertFalse($payload['success']);
        $this->assertSame([], PluginRepo::getInstance()->getTypeOrder('payment'));
    }

    #[Test]
    public function test_reorder_persists_the_posted_order(): void
    {
        $request = Request::create('/panel/plugins/reorder', 'POST', [
            'type'  => 'payment',
            'codes' => ['pay_pal', 'bank_transfer'],
        ]);

        $payload = (new PluginController)->reorder($request)->getData(true);

        $this->assertTrue($payload['success']);
        $this->assertSame(['pay_pal', 'bank_transfer'], PluginRepo::getInstance()->getTypeOrder('payment'));

        $row = Setting::query()->where('space', 'system')->where('name', 'plugin_type_order')->first();
        $this->assertNotNull($row);
        $this->assertTrue((bool) $row->json);
        $this->assertSame(['payment' => ['pay_pal', 'bank_transfer']], json_decode($row->value, true));
    }

    #[Test]
    public function test_reorder_accepts_a_numeric_json_array(): void
    {
        // JSON round-trip yields list<int|string>; everything must normalise to strings.
        $request = Request::create('/panel/plugins/reorder', 'POST', [
            'type'  => 'payment',
            'codes' => json_decode('[0,1,"pay_pal"]'),
        ]);

        $payload = (new PluginController)->reorder($request)->getData(true);

        $this->assertTrue($payload['success']);
        $this->assertSame(['0', '1', 'pay_pal'], PluginRepo::getInstance()->getTypeOrder('payment'));
    }

    #[Test]
    public function test_reorder_restores_the_settings_snapshot(): void
    {
        // updateValues() nulls the whole `inno` config snapshot; without a
        // restore, every setting() read after the write returns its default.
        Setting::query()->create(['space' => 'pay_pal', 'name' => 'active', 'value' => '1', 'json' => false]);
        config(['inno' => SettingRepo::getInstance()->groupedSettings()]);

        $request = Request::create('/panel/plugins/reorder', 'POST', [
            'type'  => 'payment',
            'codes' => ['pay_pal'],
        ]);
        $payload = (new PluginController)->reorder($request)->getData(true);

        $this->assertTrue($payload['success']);
        $this->assertTrue((bool) plugin_setting('pay_pal', 'active'));
    }

    #[Test]
    public function test_get_billing_methods_follows_the_configured_order(): void
    {
        $repo = $this->prepareEnabledPaymentPlugins();

        $this->postReorder(['pay_pal', 'bank_transfer']);

        $codes = $repo->getBillingMethods()->map(fn ($plugin) => $plugin->code)->values()->toArray();

        $this->assertSame(['pay_pal', 'bank_transfer'], $codes);
    }

    #[Test]
    public function test_get_billing_methods_falls_back_to_install_order_without_configured_order(): void
    {
        $repo = $this->prepareEnabledPaymentPlugins();

        $codes = $repo->getBillingMethods()->map(fn ($plugin) => $plugin->code)->values()->toArray();

        $this->assertSame(['bank_transfer', 'pay_pal'], $codes);
    }

    #[Test]
    public function test_get_shipping_methods_follows_the_configured_order(): void
    {
        $repo = $this->prepareEnabledShippingPlugins();

        $this->postReorder(['flex_shipping', 'fixed_shipping'], 'shipping');

        $codes = $repo->getShippingMethods()->map(fn ($plugin) => $plugin->code)->values()->toArray();

        $this->assertSame(['flex_shipping', 'fixed_shipping'], $codes);
    }

    #[Test]
    public function test_index_ignores_an_unknown_type(): void
    {
        $request = Request::create('/panel/plugins/index', 'GET', ['type' => 'not_a_type']);

        $data = (new PluginController)->index($request)->getData();

        // An unknown type must not reach the view: the blade renders the drag
        // handle for any truthy $type, and the server would reject the POST.
        $this->assertNull($data['type']);
    }

    #[Test]
    public function test_index_ignores_an_array_type(): void
    {
        $request = Request::create('/panel/plugins/index', 'GET', ['type' => ['payment']]);

        $data = (new PluginController)->index($request)->getData();

        $this->assertNull($data['type']);
    }

    #[Test]
    public function test_reorder_ignores_non_scalar_codes(): void
    {
        $request = Request::create('/panel/plugins/reorder', 'POST', [
            'type'  => 'payment',
            'codes' => [['nested' => 'x'], 'pay_pal'],
        ]);

        $payload = (new PluginController)->reorder($request)->getData(true);

        // A nested array used to raise "Array to string conversion", which
        // aborted the whole reorder instead of storing the usable codes.
        $this->assertTrue($payload['success']);
        $this->assertSame(['pay_pal'], PluginRepo::getInstance()->getTypeOrder('payment'));
    }

    /**
     * @param  array<int, string>  $codes
     */
    private function postReorder(array $codes, string $type = 'payment'): void
    {
        $request = Request::create('/panel/plugins/reorder', 'POST', [
            'type'  => $type,
            'codes' => $codes,
        ]);

        $this->assertTrue((new PluginController)->reorder($request)->getData(true)['success']);
    }

    /**
     * Install bank_transfer + pay_pal and make both look enabled and available
     * on the storefront, the same way an operator would have configured them.
     */
    private function prepareEnabledPaymentPlugins(): PluginRepo
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
                'json'  => true,
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

        return PluginRepo::getInstance();
    }

    /**
     * Install fixed_shipping + flex_shipping and make both look enabled, the
     * same way an operator would have configured them.
     */
    private function prepareEnabledShippingPlugins(): PluginRepo
    {
        if (! is_dir(base_path('plugins/FixedShipping')) || ! is_dir(base_path('plugins/FlexShipping'))) {
            $this->markTestSkipped('fixed_shipping / flex_shipping plugin directories are not present');
        }

        Plugin::query()->create(['type' => 'shipping', 'code' => 'fixed_shipping', 'priority' => 0]);
        Plugin::query()->create(['type' => 'shipping', 'code' => 'flex_shipping', 'priority' => 0]);

        foreach (['fixed_shipping', 'flex_shipping'] as $code) {
            Setting::query()->create(['space' => $code, 'name' => 'active', 'value' => '1', 'json' => false]);
        }

        PluginRepo::resetCache();
        config(['inno' => SettingRepo::getInstance()->groupedSettings()]);

        $plugins = new \ReflectionProperty(PluginManager::class, 'plugins');
        $plugins->setAccessible(true);
        $plugins->setValue(null, null);

        return PluginRepo::getInstance();
    }
}
