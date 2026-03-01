<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Tests\Console;

use InnoShop\DevTools\Console\Commands\MakePlugin;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MakePlugin command.
 * Tests command signature, description, and handle method.
 */
class MakePluginTest extends TestCase
{
    #[Test]
    public function test_command_class_exists(): void
    {
        $this->assertTrue(class_exists(MakePlugin::class));
    }

    #[Test]
    public function test_command_extends_illuminate_command(): void
    {
        $reflection = new \ReflectionClass(MakePlugin::class);
        $this->assertTrue($reflection->isSubclassOf(\Illuminate\Console\Command::class));
    }

    #[Test]
    public function test_command_has_signature(): void
    {
        $reflection = new \ReflectionClass(MakePlugin::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakePlugin;
        $signature = $property->getValue($command);

        $this->assertNotEmpty($signature);
        $this->assertStringContainsString('dev:make-plugin', $signature);
        $this->assertStringContainsString('{name', $signature);
    }

    #[Test]
    public function test_command_has_description(): void
    {
        $reflection = new \ReflectionClass(MakePlugin::class);
        $property   = $reflection->getProperty('description');
        $property->setAccessible(true);

        $command     = new MakePlugin;
        $description = $property->getValue($command);

        $this->assertNotEmpty($description);
        $this->assertStringContainsString('plugin', strtolower($description));
    }

    #[Test]
    public function test_command_has_handle_method(): void
    {
        $this->assertTrue(method_exists(MakePlugin::class, 'handle'));
    }

    #[Test]
    public function test_handle_method_returns_int(): void
    {
        $reflection = new \ReflectionMethod(MakePlugin::class, 'handle');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
    }

    #[Test]
    public function test_command_signature_has_type_option(): void
    {
        $reflection = new \ReflectionClass(MakePlugin::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakePlugin;
        $signature = $property->getValue($command);

        $this->assertStringContainsString('--type', $signature);
    }

    #[Test]
    public function test_command_signature_has_with_controller_option(): void
    {
        $reflection = new \ReflectionClass(MakePlugin::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakePlugin;
        $signature = $property->getValue($command);

        $this->assertStringContainsString('--with-controller', $signature);
    }

    #[Test]
    public function test_command_signature_has_with_model_option(): void
    {
        $reflection = new \ReflectionClass(MakePlugin::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakePlugin;
        $signature = $property->getValue($command);

        $this->assertStringContainsString('--with-model', $signature);
    }

    #[Test]
    public function test_command_signature_has_with_migration_option(): void
    {
        $reflection = new \ReflectionClass(MakePlugin::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakePlugin;
        $signature = $property->getValue($command);

        $this->assertStringContainsString('--with-migration', $signature);
    }
}
