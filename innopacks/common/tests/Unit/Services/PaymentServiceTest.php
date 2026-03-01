<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\Checkout\BillingService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PaymentServiceTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(BillingService::class);
    }

    #[Test]
    public function test_billing_service_has_get_instance_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getInstance'));
        $method = $this->reflection->getMethod('getInstance');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_billing_service_has_get_methods_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getMethods'));
        $method = $this->reflection->getMethod('getMethods');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_get_methods_returns_array(): void
    {
        $method     = $this->reflection->getMethod('getMethods');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    #[Test]
    public function test_get_instance_returns_static(): void
    {
        $method     = $this->reflection->getMethod('getInstance');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('static', $returnType->getName());
    }

    #[Test]
    public function test_billing_service_uses_plugin_repo(): void
    {
        // Document that billing service uses PluginRepo
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('PluginRepo', $source);
    }

    #[Test]
    public function test_billing_service_uses_payment_method_item_resource(): void
    {
        // Document that billing service uses PaymentMethodItem resource
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('PaymentMethodItem', $source);
    }

    #[Test]
    public function test_billing_service_applies_hook_filter(): void
    {
        // Document that billing service applies hook filter for extensibility
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('fire_hook_filter', $source);
        $this->assertStringContainsString('service.checkout.billing.methods', $source);
    }

    #[Test]
    public function test_billing_service_gets_billing_methods_from_plugins(): void
    {
        // Document that billing methods come from plugins
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('getBillingMethods', $source);
    }

    #[Test]
    public function test_billing_service_serializes_methods_to_json(): void
    {
        // Document that methods are serialized to JSON
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('jsonSerialize', $source);
    }

    #[Test]
    public function test_billing_service_is_in_checkout_namespace(): void
    {
        $this->assertEquals(
            'InnoShop\Common\Services\Checkout',
            $this->reflection->getNamespaceName()
        );
    }
}
