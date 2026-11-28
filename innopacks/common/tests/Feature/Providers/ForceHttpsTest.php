<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Providers;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

/**
 * APP_FORCE_HTTPS is read through config('app.force_https') so the setting
 * keeps working when the configuration is cached.
 */
class ForceHttpsTest extends TestCase
{
    public function test_urls_use_https_when_force_https_is_enabled(): void
    {
        config(['app.url' => 'http://shop.test', 'app.force_https' => true]);
        url()->useOrigin('http://shop.test');

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', url('/products'));
    }

    public function test_urls_keep_their_scheme_when_force_https_is_disabled(): void
    {
        config(['app.url' => 'http://shop.test', 'app.force_https' => false]);
        url()->useOrigin('http://shop.test');
        url()->forceScheme(null);

        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('http://', url('/products'));
    }
}
