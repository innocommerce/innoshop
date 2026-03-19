<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Tests\Console;

use Illuminate\Console\Command;
use InnoShop\DevTools\Console\Commands\ValidateTheme;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ValidateTheme command.
 * Tests command signature, description, and handle method.
 */
class ValidateThemeTest extends TestCase
{
    #[Test]
    public function test_command_class_exists(): void
    {
        $this->assertTrue(class_exists(ValidateTheme::class));
    }

    #[Test]
    public function test_command_extends_illuminate_command(): void
    {
        $reflection = new \ReflectionClass(ValidateTheme::class);
        $this->assertTrue($reflection->isSubclassOf(Command::class));
    }

    #[Test]
    public function test_command_has_signature(): void
    {
        $reflection = new \ReflectionClass(ValidateTheme::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new ValidateTheme;
        $signature = $property->getValue($command);

        $this->assertNotEmpty($signature);
        $this->assertStringContainsString('dev:validate-theme', $signature);
        $this->assertStringContainsString('{path', $signature);
    }

    #[Test]
    public function test_command_has_description(): void
    {
        $reflection = new \ReflectionClass(ValidateTheme::class);
        $property   = $reflection->getProperty('description');
        $property->setAccessible(true);

        $command     = new ValidateTheme;
        $description = $property->getValue($command);

        $this->assertNotEmpty($description);
        $this->assertStringContainsString('theme', strtolower($description));
    }

    #[Test]
    public function test_command_has_handle_method(): void
    {
        $this->assertTrue(method_exists(ValidateTheme::class, 'handle'));
    }

    #[Test]
    public function test_handle_method_returns_int(): void
    {
        $reflection = new \ReflectionMethod(ValidateTheme::class, 'handle');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
    }

    #[Test]
    public function test_command_signature_requires_path_argument(): void
    {
        $reflection = new \ReflectionClass(ValidateTheme::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new ValidateTheme;
        $signature = $property->getValue($command);

        // Path is a required argument (no ? or default value)
        $this->assertStringContainsString('{path :', $signature);
    }
}
