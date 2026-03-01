<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\NewsletterSubscriberController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class NewsletterSubscriberControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(NewsletterSubscriberController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(NewsletterSubscriberController::class));
    }

    #[Test]
    public function test_controller_extends_base_controller(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('InnoShop\Panel\Controllers\BaseController'));
    }

    #[Test]
    public function test_index_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('index'));
        $method = $this->reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_show_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('show'));
        $method = $this->reflection->getMethod('show');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_destroy_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('destroy'));
        $method = $this->reflection->getMethod('destroy');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_unsubscribe_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('unsubscribe'));
        $method = $this->reflection->getMethod('unsubscribe');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_resubscribe_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('resubscribe'));
        $method = $this->reflection->getMethod('resubscribe');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_index_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('index');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_show_method_accepts_newsletter_subscriber_parameter(): void
    {
        $method     = $this->reflection->getMethod('show');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('newsletterSubscriber', $parameters[0]->getName());
    }

    #[Test]
    public function test_destroy_method_accepts_newsletter_subscriber_parameter(): void
    {
        $method     = $this->reflection->getMethod('destroy');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('newsletterSubscriber', $parameters[0]->getName());
    }

    #[Test]
    public function test_unsubscribe_method_accepts_newsletter_subscriber_parameter(): void
    {
        $method     = $this->reflection->getMethod('unsubscribe');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('newsletterSubscriber', $parameters[0]->getName());
    }

    #[Test]
    public function test_resubscribe_method_accepts_newsletter_subscriber_parameter(): void
    {
        $method     = $this->reflection->getMethod('resubscribe');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('newsletterSubscriber', $parameters[0]->getName());
    }

    #[Test]
    public function test_show_method_returns_newsletter_subscriber(): void
    {
        $method     = $this->reflection->getMethod('show');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('InnoShop\Common\Models\NewsletterSubscriber', $returnType->getName());
    }

    #[Test]
    public function test_destroy_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('destroy');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }

    #[Test]
    public function test_unsubscribe_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('unsubscribe');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }

    #[Test]
    public function test_resubscribe_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('resubscribe');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }

    #[Test]
    public function test_index_data_structure(): void
    {
        $data = [
            'criteria'    => [],
            'subscribers' => [],
        ];

        $this->assertArrayHasKey('criteria', $data);
        $this->assertArrayHasKey('subscribers', $data);
    }
}
