<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Tests\Libraries;

use InnoShop\Install\Libraries\Database\MySQLDatabase;
use InnoShop\Install\Libraries\Database\SQLiteDatabase;
use InnoShop\Install\Libraries\Environment\EnvironmentChecker;
use InnoShop\Install\Libraries\Installer;
use Mockery;
use PHPUnit\Framework\TestCase;

class InstallerBugTest extends TestCase
{
    private $installer;

    private $basePath;

    private $envFile;

    private $installedFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir().'/innoshop_test_'.uniqid();
        mkdir($this->basePath, 0755, true);
        mkdir($this->basePath.'/storage', 0755, true);

        $this->envFile       = $this->basePath.'/.env';
        $this->installedFile = $this->basePath.'/storage/installed';

        $this->installer = new Installer($this->basePath);

        // Mock dependencies to isolate tests
        $envChecker = Mockery::mock(EnvironmentChecker::class);
        $mysqlDb    = Mockery::mock(MySQLDatabase::class);
        $sqliteDb   = Mockery::mock(SQLiteDatabase::class);

        $this->installer->setEnvironmentChecker($envChecker);
        $this->installer->setDatabases($mysqlDb, $sqliteDb);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        if (file_exists($this->envFile)) {
            unlink($this->envFile);
        }
        if (file_exists($this->installedFile)) {
            unlink($this->installedFile);
        }
        if (is_dir($this->basePath.'/storage')) {
            rmdir($this->basePath.'/storage');
        }
        if (is_dir($this->basePath)) {
            rmdir($this->basePath);
        }

        parent::tearDown();
    }

    public function test_is_installed_returns_true_if_env_has_db_config_even_if_installed_file_missing()
    {
        // This simulates the issue where manual installation (copying .env) should be detected
        // but 'installed' file might be missing.

        // Create .env with DB config
        $envContent = "APP_NAME=InnoShop\nDB_HOST=127.0.0.1\nDB_DATABASE=innoshop";
        file_put_contents($this->envFile, $envContent);

        // Ensure 'installed' file is missing
        if (file_exists($this->installedFile)) {
            unlink($this->installedFile);
        }

        $this->assertTrue($this->installer->isInstalled());
    }

    public function test_is_installed_returns_false_if_env_missing_db_config()
    {
        // Create .env WITHOUT DB config (e.g. just example file copied)
        $envContent = "APP_NAME=InnoShop\nAPP_ENV=local"; // No DB_HOST or DB_DATABASE
        file_put_contents($this->envFile, $envContent);

        // Ensure 'installed' file is missing
        if (file_exists($this->installedFile)) {
            unlink($this->installedFile);
        }

        $this->assertFalse($this->installer->isInstalled());
    }

    public function test_is_installed_returns_true_if_both_files_exist()
    {
        file_put_contents($this->envFile, 'APP_NAME=Test');
        touch($this->installedFile);

        $this->assertTrue($this->installer->isInstalled());
    }
}
