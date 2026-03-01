<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Tests;

use InnoShop\Install\Libraries\Database\MySQLDatabase;
use InnoShop\Install\Libraries\Database\SQLiteDatabase;
use InnoShop\Install\Libraries\Environment\EnvironmentChecker;
use InnoShop\Install\Libraries\Installer;
use PHPUnit\Framework\TestCase;

/**
 * InstallerTest class
 *
 * Tests the main installer class
 */
class TestInstaller extends Installer
{
    protected function generateRandomKey(): string
    {
        return 'test-key-32-characters-long-string';
    }

    protected function getAppUrl(): string
    {
        return 'http://localhost';
    }

    protected function hashPassword(string $password): string
    {
        return 'hashed-'.$password;
    }

    protected function now(): string
    {
        return '2024-01-01 00:00:00';
    }

    protected function createAdmin(array $data): void
    {
        // Skip creating admin in tests
    }

    protected function saveEnv(array $data): void
    {
        // Skip saving .env in tests
    }

    protected function migrate(): void
    {
        // Skip migrations in tests
    }

    protected function markAsInstalled(): void
    {
        // Skip marking as installed in tests
    }
}

class InstallerTest extends TestCase
{
    private TestInstaller $installer;

    private MySQLDatabase $mysqlDatabase;

    private SQLiteDatabase $sqliteDatabase;

    private EnvironmentChecker $environmentChecker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mysqlDatabase      = $this->createMock(MySQLDatabase::class);
        $this->sqliteDatabase     = $this->createMock(SQLiteDatabase::class);
        $this->environmentChecker = $this->createMock(EnvironmentChecker::class);

        $this->installer = new TestInstaller('/tmp/test');
        $this->installer->setDatabases($this->mysqlDatabase, $this->sqliteDatabase);
        $this->installer->setEnvironmentChecker($this->environmentChecker);
    }

    public function test_can_install_with_my_sql(): void
    {
        $data = [
            'db_type'        => 'mysql',
            'db_hostname'    => 'localhost',
            'db_port'        => '3306',
            'db_name'        => 'test_db',
            'db_username'    => 'test_user',
            'db_password'    => 'test_pass',
            'admin_email'    => 'admin@test.com',
            'admin_password' => 'password123',
        ];

        $this->mysqlDatabase->expects($this->once())
            ->method('checkConnection')
            ->with($data)
            ->willReturn(['db_success' => true]);

        $this->environmentChecker->expects($this->once())
            ->method('checkPhpVersion')
            ->willReturn(['success' => true]);

        $this->environmentChecker->expects($this->once())
            ->method('checkExtensions')
            ->willReturn(['success' => true]);

        $this->environmentChecker->expects($this->once())
            ->method('checkDirectoryPermissions')
            ->willReturn(['success' => true]);

        $result = $this->installer->install($data);
        $this->assertTrue($result['success']);
    }

    public function test_can_install_with_sq_lite(): void
    {
        $data = [
            'db_type'        => 'sqlite',
            'admin_email'    => 'admin@test.com',
            'admin_password' => 'password123',
        ];

        $this->sqliteDatabase->expects($this->once())
            ->method('checkConnection')
            ->with($data)
            ->willReturn(['db_success' => true]);

        $this->environmentChecker->expects($this->once())
            ->method('checkPhpVersion')
            ->willReturn(['success' => true]);

        $this->environmentChecker->expects($this->once())
            ->method('checkExtensions')
            ->willReturn(['success' => true]);

        $this->environmentChecker->expects($this->once())
            ->method('checkDirectoryPermissions')
            ->willReturn(['success' => true]);

        $result = $this->installer->install($data);
        $this->assertTrue($result['success']);
    }

    public function test_cannot_install_with_invalid_data(): void
    {
        $data = [
            'db_type'        => 'mysql',
            'db_hostname'    => '',
            'db_port'        => '',
            'db_name'        => '',
            'db_username'    => '',
            'db_password'    => '',
            'admin_email'    => 'invalid-email',
            'admin_password' => 'short',
        ];

        $result = $this->installer->install($data);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertNotEmpty($result['errors']);
    }

    public function test_can_check_installation_status(): void
    {
        // Create test directories
        $testBasePath    = '/tmp/test';
        $testStoragePath = $testBasePath.'/storage';
        $testEnvPath     = $testBasePath.'/.env';

        // Ensure directories don't exist
        if (file_exists($testStoragePath)) {
            system("rm -rf {$testStoragePath}");
        }
        if (file_exists($testEnvPath)) {
            unlink($testEnvPath);
        }

        // Check uninstalled state
        $this->assertFalse($this->installer->isInstalled());

        // Create directories and files
        mkdir($testStoragePath, 0755, true);
        touch($testStoragePath.'/installed');
        touch($testEnvPath);

        // Check installed state
        $this->assertTrue($this->installer->isInstalled());

        // Cleanup
        system("rm -rf {$testBasePath}");
    }
}
