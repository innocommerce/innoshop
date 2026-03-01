<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Tests\Console;

use InnoShop\DevTools\Console\Commands\MakeModel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MakeModel command.
 * Tests command signature, description, and handle method.
 */
class MakeModelTest extends TestCase
{
    #[Test]
    public function test_command_class_exists(): void
    {
        $this->assertTrue(class_exists(MakeModel::class));
    }

    #[Test]
    public function test_command_extends_illuminate_command(): void
    {
        $reflection = new \ReflectionClass(MakeModel::class);
        $this->assertTrue($reflection->isSubclassOf(\Illuminate\Console\Command::class));
    }

    #[Test]
    public function test_command_has_signature(): void
    {
        $reflection = new \ReflectionClass(MakeModel::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakeModel;
        $signature = $property->getValue($command);

        $this->assertNotEmpty($signature);
        $this->assertStringContainsString('dev:make-model', $signature);
        $this->assertStringContainsString('{name', $signature);
    }

    #[Test]
    public function test_command_has_description(): void
    {
        $reflection = new \ReflectionClass(MakeModel::class);
        $property   = $reflection->getProperty('description');
        $property->setAccessible(true);

        $command     = new MakeModel;
        $description = $property->getValue($command);

        $this->assertNotEmpty($description);
        $this->assertStringContainsString('model', strtolower($description));
    }

    #[Test]
    public function test_command_has_handle_method(): void
    {
        $this->assertTrue(method_exists(MakeModel::class, 'handle'));
    }

    #[Test]
    public function test_handle_method_returns_int(): void
    {
        $reflection = new \ReflectionMethod(MakeModel::class, 'handle');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
    }

    #[Test]
    public function test_command_signature_has_plugin_option(): void
    {
        $reflection = new \ReflectionClass(MakeModel::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakeModel;
        $signature = $property->getValue($command);

        $this->assertStringContainsString('--plugin', $signature);
    }
}
