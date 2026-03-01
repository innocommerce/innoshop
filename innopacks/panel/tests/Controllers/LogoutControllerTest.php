<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\LogoutController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LogoutControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(LogoutController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(LogoutController::class));
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
    public function test_index_method_has_no_parameters(): void
    {
        $method     = $this->reflection->getMethod('index');
        $parameters = $method->getParameters();
        $this->assertCount(0, $parameters);
    }
}
