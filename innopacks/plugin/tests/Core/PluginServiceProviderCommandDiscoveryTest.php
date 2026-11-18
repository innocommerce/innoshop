<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Tests\Core;

use Illuminate\Container\Container;
use InnoShop\Plugin\PluginServiceProvider;
use InnoShop\Plugin\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Command discovery must work on every platform.
 *
 * getClassesFromPathRecursive() used to split the relative path on '/'
 * only. On Windows RecursiveDirectoryIterator returns backslash paths,
 * so the split never produced [pluginCode, 'Commands', ...] and not a
 * single plugin artisan command was ever registered.
 *
 * The fixtures define real classes under the Plugin\ namespace and are
 * require'd by the tests, mirroring what class_exists() sees in
 * production.
 */
class PluginServiceProviderCommandDiscoveryTest extends TestCase
{
    private string $fixtureRoot;

    protected function setUp(): void
    {
        $this->fixtureRoot = sys_get_temp_dir().'/innoshop-pack-command-discovery-'.uniqid();
        mkdir($this->fixtureRoot.'/FixtDemo/Commands/Nested', 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->fixtureRoot);
    }

    #[Test]
    public function test_discovers_command_classes_from_os_native_paths(): void
    {
        $commandFile = $this->fixtureRoot.'/FixtDemo/Commands/DemoCommand.php';
        file_put_contents($commandFile, '<?php namespace Plugin\FixtDemo\Commands; class DemoCommand {}');
        $this->requireFixture($commandFile, 'Plugin\FixtDemo\Commands\DemoCommand');

        $classes = $this->discover($this->fixtureRoot.'/FixtDemo/Commands');

        $this->assertContains('Plugin\FixtDemo\Commands\DemoCommand', $classes);
    }

    #[Test]
    public function test_builds_namespace_for_nested_command_subdirectories(): void
    {
        $nestedFile = $this->fixtureRoot.'/FixtDemo/Commands/Nested/NestedCommand.php';
        file_put_contents($nestedFile, '<?php namespace Plugin\FixtDemo\Commands\Nested; class NestedCommand {}');
        $this->requireFixture($nestedFile, 'Plugin\FixtDemo\Commands\Nested\NestedCommand');

        $classes = $this->discover($this->fixtureRoot.'/FixtDemo/Commands');

        $this->assertContains('Plugin\FixtDemo\Commands\Nested\NestedCommand', $classes);
    }

    #[Test]
    public function test_ignores_php_files_without_matching_classes(): void
    {
        $commandFile = $this->fixtureRoot.'/FixtDemo/Commands/DemoCommand.php';
        file_put_contents($commandFile, '<?php namespace Plugin\FixtDemo\Commands; class DemoCommand {}');
        $this->requireFixture($commandFile, 'Plugin\FixtDemo\Commands\DemoCommand');

        file_put_contents($this->fixtureRoot.'/FixtDemo/Commands/helper.php', '<?php // no class here');

        $classes = $this->discover($this->fixtureRoot.'/FixtDemo/Commands');

        $this->assertSame(['Plugin\FixtDemo\Commands\DemoCommand'], $classes);
    }

    /**
     * Require the fixture file only when its class is not loaded yet —
     * a later test would otherwise redeclare a class an earlier one
     * already defined (same class name, different fixture path).
     */
    private function requireFixture(string $file, string $class): void
    {
        if (! class_exists($class)) {
            require_once $file;
        }
    }

    /**
     * Run the private scanner the same way loadPluginCommands() does,
     * with a plugin base path matching the OS-native separator style.
     */
    private function discover(string $commandsPath): array
    {
        $provider = new PluginServiceProvider(new Container);

        $basePath = new ReflectionProperty($provider, 'pluginBasePath');
        $basePath->setValue($provider, realpath($this->fixtureRoot));

        $method = new ReflectionMethod($provider, 'getClassesFromPathRecursive');

        return $method->invoke($provider, realpath($commandsPath));
    }

    /**
     * Remove the fixture tree, files first.
     */
    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($dir);
    }
}
