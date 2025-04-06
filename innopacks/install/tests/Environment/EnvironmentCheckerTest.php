<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Tests\Environment;

use InnoShop\Install\Libraries\Environment\EnvironmentChecker;
use PHPUnit\Framework\TestCase;

/**
 * EnvironmentCheckerTest class
 *
 * Tests for system requirements and environment settings
 */
class EnvironmentCheckerTest extends TestCase
{
    private EnvironmentChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new EnvironmentChecker;
    }

    public function test_check_php_version()
    {
        $result = $this->checker->checkPhpVersion();

        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual('8.2.0', $result['version']);
    }

    public function test_check_required_extensions()
    {
        $result = $this->checker->checkExtensions();

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('extensions', $result);

        $requiredExtensions = [
            'pdo',
            'pdo_mysql',
            'pdo_sqlite',
            'mbstring',
            'openssl',
            'json',
            'fileinfo',
            'tokenizer',
            'ctype',
            'xml',
        ];

        foreach ($requiredExtensions as $extension) {
            $this->assertTrue($result['extensions'][$extension]);
        }
    }

    public function test_check_directory_permissions()
    {
        $result = $this->checker->checkDirectoryPermissions();

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('directories', $result);

        $requiredDirectories = [
            'storage',
            'bootstrap/cache',
            'public/cache',
        ];

        foreach ($requiredDirectories as $directory) {
            $this->assertTrue($result['directories'][$directory]);
        }
    }
}
