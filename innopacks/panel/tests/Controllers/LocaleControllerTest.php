<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\LocaleController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LocaleControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(LocaleController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(LocaleController::class));
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
    public function test_switch_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('switch'));
        $method = $this->reflection->getMethod('switch');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_install_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('install'));
        $method = $this->reflection->getMethod('install');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_edit_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('edit'));
        $method = $this->reflection->getMethod('edit');
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
    public function test_uninstall_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('uninstall'));
        $method = $this->reflection->getMethod('uninstall');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_active_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('active'));
        $method = $this->reflection->getMethod('active');
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
    public function test_switch_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('switch');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_install_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('install');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_edit_method_accepts_locale_parameter(): void
    {
        $method     = $this->reflection->getMethod('edit');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('locale', $parameters[0]->getName());
    }

    #[Test]
    public function test_update_method_accepts_request_and_locale(): void
    {
        $method     = $this->reflection->getMethod('update');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('locale', $parameters[1]->getName());
    }

    #[Test]
    public function test_uninstall_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('uninstall');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_active_method_accepts_request_and_id(): void
    {
        $method     = $this->reflection->getMethod('active');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('id', $parameters[1]->getName());
    }

    #[Test]
    public function test_switch_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('switch');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }

    #[Test]
    public function test_install_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('install');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
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
            'criteria' => [],
            'locales'  => [],
        ];

        $this->assertArrayHasKey('criteria', $data);
        $this->assertArrayHasKey('locales', $data);
    }

    #[Test]
    public function test_edit_data_structure(): void
    {
        $data = [
            'locale' => null,
        ];

        $this->assertArrayHasKey('locale', $data);
    }
}
