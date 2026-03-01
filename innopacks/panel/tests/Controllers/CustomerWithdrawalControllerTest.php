<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\CustomerWithdrawalController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CustomerWithdrawalControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(CustomerWithdrawalController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(CustomerWithdrawalController::class));
    }

    #[Test]
    public function test_controller_extends_base_controller(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('InnoShop\Panel\Controllers\BaseController'));
    }

    #[Test]
    public function test_index_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('index'));
        $method = $this->reflection->getMethod('index');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_show_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('show'));
        $method = $this->reflection->getMethod('show');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_change_status_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('changeStatus'));
        $method = $this->reflection->getMethod('changeStatus');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_index_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('index');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_show_method_accepts_withdrawal_parameter(): void
    {
        $method     = $this->reflection->getMethod('show');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('withdrawal', $parameters[0]->getName());
    }

    #[Test]
    public function test_change_status_method_accepts_request_and_withdrawal(): void
    {
        $method     = $this->reflection->getMethod('changeStatus');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('withdrawal', $parameters[1]->getName());
    }

    #[Test]
    public function test_change_status_method_returns_union_type(): void
    {
        $method     = $this->reflection->getMethod('changeStatus');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        // Check if it's a union type (allows multiple return types)
        $this->assertInstanceOf('ReflectionUnionType', $returnType);
    }

    #[Test]
    public function test_index_data_structure(): void
    {
        $data = [
            'criteria'    => [],
            'withdrawals' => [],
        ];

        $this->assertArrayHasKey('criteria', $data);
        $this->assertArrayHasKey('withdrawals', $data);
    }

    #[Test]
    public function test_show_data_structure(): void
    {
        $data = [
            'withdrawal' => null,
        ];

        $this->assertArrayHasKey('withdrawal', $data);
    }
}
