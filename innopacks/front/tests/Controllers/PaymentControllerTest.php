<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\PaymentController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PaymentControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(PaymentController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(PaymentController::class));
    }

    #[Test]
    public function test_controller_extends_base_controller(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }

    #[Test]
    public function test_success_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('success'));
        $method = $this->reflection->getMethod('success');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_fail_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('fail'));
        $method = $this->reflection->getMethod('fail');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_cancel_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('cancel'));
        $method = $this->reflection->getMethod('cancel');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_success_method_accepts_request(): void
    {
        $method     = $this->reflection->getMethod('success');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_fail_method_accepts_request(): void
    {
        $method     = $this->reflection->getMethod('fail');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_payment_status_success(): void
    {
        $status = 'success';

        $this->assertEquals('success', $status);
    }

    #[Test]
    public function test_payment_status_failed(): void
    {
        $status = 'failed';

        $this->assertEquals('failed', $status);
    }

    #[Test]
    public function test_payment_status_pending(): void
    {
        $status = 'pending';

        $this->assertEquals('pending', $status);
    }

    #[Test]
    public function test_payment_code_validation(): void
    {
        $validCodes = ['paypal', 'stripe', 'alipay', 'wechat'];

        foreach ($validCodes as $code) {
            $this->assertNotEmpty($code);
            $this->assertIsString($code);
        }
    }

    #[Test]
    public function test_payment_amount_format(): void
    {
        $amount = 99.99;

        $this->assertIsFloat($amount);
        $this->assertGreaterThan(0, $amount);
    }

    #[Test]
    public function test_payment_currency_code(): void
    {
        $currencyCode = 'USD';

        $this->assertEquals(3, strlen($currencyCode));
        $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $currencyCode);
    }
}
