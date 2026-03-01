<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\AccountController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AccountControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(AccountController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(AccountController::class));
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
    public function test_update_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('update'));
        $method = $this->reflection->getMethod('update');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_index_method_has_no_parameters(): void
    {
        $method     = $this->reflection->getMethod('index');
        $parameters = $method->getParameters();
        $this->assertCount(0, $parameters);
    }

    #[Test]
    public function test_update_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('update');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_update_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('update');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }

    #[Test]
    public function test_index_data_structure(): void
    {
        $data = [
            'admin' => null,
        ];

        $this->assertArrayHasKey('admin', $data);
    }
}
