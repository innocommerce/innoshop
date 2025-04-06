<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Install\Tests\Database;

use InnoShop\Install\Libraries\Database\MySQLDatabase;
use PDO;
use Tests\TestCase;

/**
 * MySQLDatabaseTest class
 *
 * Tests the MySQL database connection and configuration
 */
class MySQLDatabaseTest extends TestCase
{
    private MySQLDatabase $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = new MySQLDatabase;
        app()->setLocale('zh-cn');
    }

    public function test_can_connect_with_valid_credentials(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('getAttribute')
            ->with(PDO::ATTR_SERVER_VERSION)
            ->willReturn('8.0.0');

        $this->database->setPdo($mockPdo);

        $result = $this->database->checkConnection([
            'db_hostname' => 'localhost',
            'db_port'     => '3306',
            'db_name'     => 'test_db',
            'db_username' => 'test_user',
            'db_password' => 'test_pass',
        ]);

        $this->assertTrue($result['db_success']);
    }

    public function test_cannot_connect_with_invalid_version(): void
    {
        $mockPdo = $this->createMock(PDO::class);
        $mockPdo->method('getAttribute')
            ->with(PDO::ATTR_SERVER_VERSION)
            ->willReturn('5.6.0');

        $this->database->setPdo($mockPdo);

        $result = $this->database->checkConnection([
            'db_hostname' => 'localhost',
            'db_port'     => '3306',
            'db_name'     => 'test_db',
            'db_username' => 'test_user',
            'db_password' => 'test_pass',
        ]);

        $this->assertFalse($result['db_success']);
        $this->assertArrayHasKey('db_version', $result);
    }

    public function test_cannot_connect_with_invalid_host(): void
    {
        $this->database->setPdo(null);

        $result = $this->database->checkConnection([
            'db_hostname' => 'invalid_host',
            'db_port'     => '3306',
            'db_name'     => 'test_db',
            'db_username' => 'test_user',
            'db_password' => 'test_pass',
        ]);

        $this->assertFalse($result['db_success']);
        $this->assertArrayHasKey('db_hostname', $result);
        $this->assertArrayHasKey('db_port', $result);
    }

    public function test_cannot_connect_with_invalid_credentials(): void
    {
        $this->database->setPdo(null);

        $result = $this->database->checkConnection([
            'db_hostname' => 'localhost',
            'db_port'     => '3306',
            'db_name'     => 'test_db',
            'db_username' => 'invalid_user',
            'db_password' => 'invalid_pass',
        ]);

        $this->assertFalse($result['db_success']);
        $this->assertArrayHasKey('db_username', $result);
        $this->assertArrayHasKey('db_password', $result);
    }
}
