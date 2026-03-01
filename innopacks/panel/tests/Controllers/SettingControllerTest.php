<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\BaseController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Note: There is no dedicated SettingController in the panel module.
 * Settings are managed through ThemeController (settings, updateSettings methods).
 * This test verifies the BaseController which provides common functionality.
 */
class SettingControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(BaseController::class);
    }

    #[Test]
    public function test_base_controller_exists(): void
    {
        $this->assertTrue(class_exists(BaseController::class));
    }

    #[Test]
    public function test_base_controller_extends_laravel_controller(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('App\Http\Controllers\Controller'));
    }

    #[Test]
    public function test_has_model_class_property(): void
    {
        $this->assertTrue($this->reflection->hasProperty('modelClass'));
        $property = $this->reflection->getProperty('modelClass');
        $this->assertTrue($property->isProtected());
    }

    #[Test]
    public function test_active_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('active'));
        $method = $this->reflection->getMethod('active');
        $this->assertTrue($method->isPublic());
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
    public function test_constructor_exists(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPublic());
    }

    #[Test]
    public function test_get_model_by_controller_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getModelByController'));
        $method = $this->reflection->getMethod('getModelByController');
        $this->assertTrue($method->isPrivate());
    }

    #[Test]
    public function test_check_model_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('checkModel'));
        $method = $this->reflection->getMethod('checkModel');
        $this->assertTrue($method->isPrivate());
    }
}
