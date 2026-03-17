<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\ThemeController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ThemeControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(ThemeController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(ThemeController::class));
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
    public function test_settings_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('settings'));
        $method = $this->reflection->getMethod('settings');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_update_settings_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('updateSettings'));
        $method = $this->reflection->getMethod('updateSettings');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_enable_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('enable'));
        $method = $this->reflection->getMethod('enable');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_import_demo_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('importDemo'));
        $method = $this->reflection->getMethod('importDemo');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_constructor_accepts_theme_service(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('themeService', $parameters[0]->getName());
    }

    #[Test]
    public function test_has_theme_service_property(): void
    {
        $this->assertTrue($this->reflection->hasProperty('themeService'));
        $property = $this->reflection->getProperty('themeService');
        $this->assertTrue($property->isProtected());
    }

    #[Test]
    public function test_enable_method_accepts_request_and_theme_code(): void
    {
        $method     = $this->reflection->getMethod('enable');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('themeCode', $parameters[1]->getName());
    }

    #[Test]
    public function test_import_demo_method_returns_json_response(): void
    {
        $method     = $this->reflection->getMethod('importDemo');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\JsonResponse', $returnType->getName());
    }
}
