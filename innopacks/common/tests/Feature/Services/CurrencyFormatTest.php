<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InnoShop\Common\Libraries\Currency;
use InnoShop\Common\Models\Currency as CurrencyModel;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Repositories\CurrencyRepo;
use ReflectionClass;
use Tests\TestCase;

/**
 * The rate comes from a database string column and may be empty, null or
 * non-numeric text; formatting must tolerate those instead of throwing TypeError.
 */
class CurrencyFormatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CurrencyModel::query()->create([
            'name'          => 'US Dollar',
            'code'          => 'USD',
            'symbol_left'   => '$',
            'symbol_right'  => '',
            'decimal_place' => 2,
            'value'         => 1,
            'active'        => true,
        ]);

        $this->resetCurrencyCaches();
    }

    public function test_numeric_string_rate_is_applied(): void
    {
        $this->assertSame('$20.00', currency_format(10, 'usd', '2'));
    }

    public function test_empty_rate_falls_back_to_currency_rate(): void
    {
        $this->assertSame('$10.00', currency_format(10, 'usd', ''));
    }

    public function test_null_rate_falls_back_to_currency_rate(): void
    {
        $this->assertSame('$10.00', currency_format(10, 'usd', null));
    }

    public function test_garbage_rate_falls_back_to_currency_rate(): void
    {
        $this->assertSame('$10.00', currency_format(10, 'usd', 'not-a-number'));
    }

    public function test_order_total_format_tolerates_invalid_currency_value(): void
    {
        $order = new Order([
            'total'          => 1.63,
            'currency_code'  => 'usd',
            'currency_value' => '',
        ]);

        $this->assertSame('$1.63', $order->getTotalFormatAttribute());
    }

    /**
     * Both the currency list and the Currency singleton keep static caches;
     * reset them between tests to avoid reading stale data.
     */
    private function resetCurrencyCaches(): void
    {
        $repoProperty = (new ReflectionClass(CurrencyRepo::class))->getProperty('enabledCurrencies');
        $repoProperty->setAccessible(true);
        $repoProperty->setValue(null, null);

        $instanceProperty = (new ReflectionClass(Currency::class))->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }
}
