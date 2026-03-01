<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Tests\Console;

use InnoShop\DevTools\Console\Commands\MakeController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MakeController command.
 * Tests command signature, description, and handle method.
 */
class MakeControllerTest extends TestCase
{
    #[Test]
    public function test_command_class_exists(): void
    {
        $this->assertTrue(class_exists(MakeController::class));
    }

    #[Test]
    public function test_command_extends_illuminate_command(): void
    {
        $reflection = new \ReflectionClass(MakeController::class);
        $this->assertTrue($reflection->isSubclassOf(\Illuminate\Console\Command::class));
    }

    #[Test]
    public function test_command_has_signature(): void
    {
        $reflection = new \ReflectionClass(MakeController::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakeController;
        $signature = $property->getValue($command);

        $this->assertNotEmpty($signature);
        $this->assertStringContainsString('dev:make-controller', $signature);
        $this->assertStringContainsString('{name', $signature);
    }

    #[Test]
    public function test_command_has_description(): void
    {
        $reflection = new \ReflectionClass(MakeController::class);
        $property   = $reflection->getProperty('description');
        $property->setAccessible(true);

        $command     = new MakeController;
        $description = $property->getValue($command);

        $this->assertNotEmpty($description);
        $this->assertStringContainsString('controller', strtolower($description));
    }

    #[Test]
    public function test_command_has_handle_method(): void
    {
        $this->assertTrue(method_exists(MakeController::class, 'handle'));
    }

    #[Test]
    public function test_handle_method_returns_int(): void
    {
        $reflection = new \ReflectionMethod(MakeController::class, 'handle');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
    }

    #[Test]
    public function test_command_signature_has_plugin_option(): void
    {
        $reflection = new \ReflectionClass(MakeController::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakeController;
        $signature = $property->getValue($command);

        $this->assertStringContainsString('--plugin', $signature);
    }
}
