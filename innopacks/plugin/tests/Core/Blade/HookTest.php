<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Core\Blade;

use InnoShop\Plugin\Core\Blade\Hook;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class HookTest extends TestCase
{
    #[Test]
    public function test_hook_class_exists(): void
    {
        $this->assertTrue(class_exists(Hook::class));
    }

    #[Test]
    public function test_has_get_singleton_method(): void
    {
        $this->assertTrue(method_exists(Hook::class, 'getSingleton'));
    }

    #[Test]
    public function test_get_singleton_returns_hook_instance(): void
    {
        $hook = Hook::getSingleton();
        $this->assertInstanceOf(Hook::class, $hook);
    }

    #[Test]
    public function test_get_singleton_returns_same_instance(): void
    {
        $hook1 = Hook::getSingleton();
        $hook2 = Hook::getSingleton();
        $this->assertSame($hook1, $hook2);
    }

    #[Test]
    public function test_has_get_method(): void
    {
        $this->assertTrue(method_exists(Hook::class, 'get'));
    }

    #[Test]
    public function test_has_get_hook_method(): void
    {
        $this->assertTrue(method_exists(Hook::class, 'getHook'));
    }

    #[Test]
    public function test_has_get_wrapper_method(): void
    {
        $this->assertTrue(method_exists(Hook::class, 'getWrapper'));
    }

    #[Test]
    public function test_has_stop_method(): void
    {
        $this->assertTrue(method_exists(Hook::class, 'stop'));
    }

    #[Test]
    public function test_has_listen_method(): void
    {
        $this->assertTrue(method_exists(Hook::class, 'listen'));
    }

    #[Test]
    public function test_has_get_hooks_method(): void
    {
        $this->assertTrue(method_exists(Hook::class, 'getHooks'));
    }

    #[Test]
    public function test_has_get_events_method(): void
    {
        $this->assertTrue(method_exists(Hook::class, 'getEvents'));
    }

    #[Test]
    public function test_has_mock_method(): void
    {
        $this->assertTrue(method_exists(Hook::class, 'mock'));
    }

    #[Test]
    public function test_has_get_listeners_method(): void
    {
        $this->assertTrue(method_exists(Hook::class, 'getListeners'));
    }

    #[Test]
    public function test_get_hooks_returns_array(): void
    {
        $hook = Hook::getSingleton();
        $this->assertIsArray($hook->getHooks());
    }

    #[Test]
    public function test_get_listeners_returns_array(): void
    {
        $hook = Hook::getSingleton();
        $this->assertIsArray($hook->getListeners());
    }
}
