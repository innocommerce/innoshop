<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Tests\Database;

use InnoShop\Install\Libraries\Database\SQLiteDatabase;
use Tests\TestCase;

/**
 * SQLiteDatabaseTest class
 *
 * Tests the SQLite database connection and configuration
 */
class SQLiteDatabaseTest extends TestCase
{
    private SQLiteDatabase $database;

    private string $testDbPath;

    private string $readOnlyDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database    = new SQLiteDatabase;
        $this->testDbPath  = sys_get_temp_dir().'/test.sqlite';
        $this->readOnlyDir = sys_get_temp_dir().'/readonly';
        app()->setLocale('zh-cn');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (file_exists($this->testDbPath)) {
            unlink($this->testDbPath);
        }
        if (is_dir($this->readOnlyDir)) {
            chmod($this->readOnlyDir, 0755); // Restore permissions for cleanup
            rmdir($this->readOnlyDir);
        }
    }

    public function test_can_create_and_connect_to_sqlite_database(): void
    {
        $result = $this->database->checkConnection(['db_path' => $this->testDbPath]);

        $this->assertTrue($result['db_success']);
        $this->assertTrue(file_exists($this->testDbPath));
        $this->assertEquals('0666', substr(sprintf('%o', fileperms($this->testDbPath)), -4));
    }

    public function test_cannot_connect_to_invalid_path(): void
    {
        // Create a read-only directory
        mkdir($this->readOnlyDir);
        chmod($this->readOnlyDir, 0444); // Set as read-only

        $result = $this->database->checkConnection([
            'db_path' => $this->readOnlyDir.'/test.sqlite',
        ]);

        $this->assertFalse($result['db_success']);
        $this->assertArrayHasKey('db_path', $result);
        $this->assertEquals(trans('install/common.dir_not_writable'), $result['db_path']);
    }

    public function test_database_file_has_correct_permissions(): void
    {
        $result = $this->database->checkConnection(['db_path' => $this->testDbPath]);

        $this->assertTrue($result['db_success']);
        $this->assertEquals('0666', substr(sprintf('%o', fileperms($this->testDbPath)), -4));
    }
}
