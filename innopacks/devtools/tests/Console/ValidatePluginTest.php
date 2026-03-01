<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Tests\Console;

use InnoShop\DevTools\Console\Commands\ValidatePlugin;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ValidatePlugin command.
 * Tests command signature, description, and handle method.
 */
class ValidatePluginTest extends TestCase
{
    #[Test]
    public function test_command_class_exists(): void
    {
        $this->assertTrue(class_exists(ValidatePlugin::class));
    }

    #[Test]
    public function test_command_extends_illuminate_command(): void
    {
        $reflection = new \ReflectionClass(ValidatePlugin::class);
        $this->assertTrue($reflection->isSubclassOf(\Illuminate\Console\Command::class));
    }

    #[Test]
    public function test_command_has_signature(): void
    {
        $reflection = new \ReflectionClass(ValidatePlugin::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new ValidatePlugin;
        $signature = $property->getValue($command);

        $this->assertNotEmpty($signature);
        $this->assertStringContainsString('dev:validate-plugin', $signature);
        $this->assertStringContainsString('{path', $signature);
    }

    #[Test]
    public function test_command_has_description(): void
    {
        $reflection = new \ReflectionClass(ValidatePlugin::class);
        $property   = $reflection->getProperty('description');
        $property->setAccessible(true);

        $command     = new ValidatePlugin;
        $description = $property->getValue($command);

        $this->assertNotEmpty($description);
        $this->assertStringContainsString('plugin', strtolower($description));
    }

    #[Test]
    public function test_command_has_handle_method(): void
    {
        $this->assertTrue(method_exists(ValidatePlugin::class, 'handle'));
    }

    #[Test]
    public function test_handle_method_returns_int(): void
    {
        $reflection = new \ReflectionMethod(ValidatePlugin::class, 'handle');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
    }

    #[Test]
    public function test_command_signature_requires_path_argument(): void
    {
        $reflection = new \ReflectionClass(ValidatePlugin::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new ValidatePlugin;
        $signature = $property->getValue($command);

        // Path is a required argument (no ? or default value)
        $this->assertStringContainsString('{path :', $signature);
    }
}
