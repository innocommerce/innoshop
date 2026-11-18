<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Repositories;

use Illuminate\Support\Collection;
use InnoShop\Plugin\Models\Plugin;
use InnoShop\Plugin\Repositories\PluginRepo;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Ordering semantics of the per-plugin-type display order (issue #1).
 *
 * applyOrder() is deliberately a pure static method: no settings read, no DB.
 * The stored order is passed in by the caller so the semantics can be pinned
 * down without bootstrapping Laravel.
 */
class PluginTypeOrderTest extends TestCase
{
    /**
     * @param  array<int, string>  $codes
     */
    private function plugins(array $codes): Collection
    {
        return new Collection(array_map(fn (string $code) => new Plugin(['code' => $code]), $codes));
    }

    /**
     * @param  Collection<int, Plugin>  $plugins
     * @return array<int, string>
     */
    private function codes(Collection $plugins): array
    {
        return $plugins->map(fn ($plugin) => $plugin->code)->values()->toArray();
    }

    #[Test]
    public function test_apply_order_reorders_by_stored_order(): void
    {
        $result = PluginRepo::applyOrder($this->plugins(['bank_transfer', 'pay_pal']), ['pay_pal', 'bank_transfer']);

        $this->assertSame(['pay_pal', 'bank_transfer'], $this->codes($result));
    }

    #[Test]
    public function test_apply_order_keeps_unlisted_codes_at_tail_in_original_order(): void
    {
        $result = PluginRepo::applyOrder($this->plugins(['alipay', 'bank_transfer', 'pay_pal']), ['pay_pal']);

        $this->assertSame(['pay_pal', 'alipay', 'bank_transfer'], $this->codes($result));
    }

    #[Test]
    public function test_apply_order_ignores_stored_codes_that_are_not_installed(): void
    {
        $result = PluginRepo::applyOrder($this->plugins(['bank_transfer', 'pay_pal']), ['stripe', 'pay_pal', 'alipay']);

        $this->assertSame(['pay_pal', 'bank_transfer'], $this->codes($result));
    }

    #[Test]
    public function test_apply_order_returns_original_when_order_is_empty(): void
    {
        $result = PluginRepo::applyOrder($this->plugins(['bank_transfer', 'pay_pal']), []);

        $this->assertSame(['bank_transfer', 'pay_pal'], $this->codes($result));
    }

    #[Test]
    public function test_apply_order_returns_original_when_order_matches_nothing(): void
    {
        $result = PluginRepo::applyOrder($this->plugins(['bank_transfer', 'pay_pal']), ['stripe', 'alipay']);

        $this->assertSame(['bank_transfer', 'pay_pal'], $this->codes($result));
    }

    #[Test]
    public function test_apply_order_does_not_mutate_input_collection(): void
    {
        $plugins = $this->plugins(['bank_transfer', 'pay_pal']);

        PluginRepo::applyOrder($plugins, ['pay_pal', 'bank_transfer']);

        $this->assertSame(['bank_transfer', 'pay_pal'], $this->codes($plugins));
    }
}
