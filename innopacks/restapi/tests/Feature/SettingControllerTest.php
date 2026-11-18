<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Restapi\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use InnoShop\Restapi\FrontApiControllers\SettingController;
use Tests\TestCase;

class SettingControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/api/settings', [SettingController::class, 'index']);
    }

    public function test_public_settings_only_include_the_explicit_allowlist(): void
    {
        Config::set('inno.system', [
            'country_code'                          => 'CN',
            'currency'                              => 'CNY',
            'login_checkout'                        => '1',
            'enableInquiry'                         => true,
            'enableRegister'                        => '1',
            'enableGoogleLogin'                     => false,
            'smtp_password'                         => 'mail-secret',
            'sms_aliyun_access_key_secret'          => 'sms-secret',
            'storage_s3_secret'                     => 'storage-secret',
            'panel_name'                            => 'internal-panel',
            'js_code'                               => '<script>alert(1)</script>',
            'unknown_plugin_integration_credential' => 'unknown-secret',
        ]);

        $response = $this->getJson('/api/settings');

        $response->assertOk()->assertJsonPath('success', true);
        $data = $response->json('data');
        $this->assertSame(
            ['country_code', 'currencies', 'currency', 'enableGoogleLogin', 'enableInquiry', 'enableRegister', 'locales', 'login_checkout'],
            collect(array_keys($data))->sort()->values()->all()
        );
        $this->assertSame('CN', $data['country_code']);
        $this->assertSame('CNY', $data['currency']);
        $this->assertSame('1', $data['login_checkout']);
        $this->assertFalse($data['enableGoogleLogin']);
        $this->assertStringNotContainsString('mail-secret', $response->getContent());
        $this->assertStringNotContainsString('unknown-secret', $response->getContent());
    }

    public function test_missing_optional_flags_are_not_fabricated_and_locale_fields_are_limited(): void
    {
        Config::set('inno.system', ['currency' => 'USD', 'smtp_password' => 'do-not-expose']);

        $response = $this->getJson('/api/settings');
        $response->assertOk();
        $data = $response->json('data');

        $this->assertSame(['currencies', 'currency', 'locales'], collect(array_keys($data))->sort()->values()->all());
        $this->assertArrayNotHasKey('enableRegister', $data);
        $this->assertArrayNotHasKey('smtp_password', $data);

        if ($data['locales'] !== []) {
            $this->assertSame(['code', 'name'], collect(array_keys($data['locales'][0]))->sort()->values()->all());
        }
        if ($data['currencies'] !== []) {
            $this->assertSame(['code', 'name'], collect(array_keys($data['currencies'][0]))->sort()->values()->all());
        }
    }
}
