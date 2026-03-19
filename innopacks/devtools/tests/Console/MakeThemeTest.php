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
use InnoShop\DevTools\Console\Commands\MakeTheme;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MakeTheme command.
 * Tests command signature, description, and handle method.
 */
class MakeThemeTest extends TestCase
{
    #[Test]
    public function test_command_class_exists(): void
    {
        $this->assertTrue(class_exists(MakeTheme::class));
    }

    #[Test]
    public function test_command_extends_illuminate_command(): void
    {
        $reflection = new \ReflectionClass(MakeTheme::class);
        $this->assertTrue($reflection->isSubclassOf(Command::class));
    }

    #[Test]
    public function test_command_has_signature(): void
    {
        $reflection = new \ReflectionClass(MakeTheme::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakeTheme;
        $signature = $property->getValue($command);

        $this->assertNotEmpty($signature);
        $this->assertStringContainsString('dev:make-theme', $signature);
        $this->assertStringContainsString('{name', $signature);
    }

    #[Test]
    public function test_command_has_description(): void
    {
        $reflection = new \ReflectionClass(MakeTheme::class);
        $property   = $reflection->getProperty('description');
        $property->setAccessible(true);

        $command     = new MakeTheme;
        $description = $property->getValue($command);

        $this->assertNotEmpty($description);
        $this->assertStringContainsString('theme', strtolower($description));
    }

    #[Test]
    public function test_command_has_handle_method(): void
    {
        $this->assertTrue(method_exists(MakeTheme::class, 'handle'));
    }

    #[Test]
    public function test_handle_method_returns_int(): void
    {
        $reflection = new \ReflectionMethod(MakeTheme::class, 'handle');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
    }

    #[Test]
    public function test_command_signature_has_name_zh_option(): void
    {
        $reflection = new \ReflectionClass(MakeTheme::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakeTheme;
        $signature = $property->getValue($command);

        $this->assertStringContainsString('--name-zh', $signature);
    }

    #[Test]
    public function test_command_signature_has_name_en_option(): void
    {
        $reflection = new \ReflectionClass(MakeTheme::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakeTheme;
        $signature = $property->getValue($command);

        $this->assertStringContainsString('--name-en', $signature);
    }

    #[Test]
    public function test_command_signature_has_description_options(): void
    {
        $reflection = new \ReflectionClass(MakeTheme::class);
        $property   = $reflection->getProperty('signature');
        $property->setAccessible(true);

        $command   = new MakeTheme;
        $signature = $property->getValue($command);

        $this->assertStringContainsString('--description-zh', $signature);
        $this->assertStringContainsString('--description-en', $signature);
    }
}
