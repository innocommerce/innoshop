<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\OptionController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class OptionControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(OptionController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(OptionController::class));
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
    public function test_store_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('store'));
        $method = $this->reflection->getMethod('store');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_update_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('update'));
        $method = $this->reflection->getMethod('update');
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
    public function test_destroy_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('destroy'));
        $method = $this->reflection->getMethod('destroy');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_available_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('available'));
        $method = $this->reflection->getMethod('available');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_values_by_option_id_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('valuesByOptionId'));
        $method = $this->reflection->getMethod('valuesByOptionId');
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
    public function test_store_method_accepts_option_request(): void
    {
        $method     = $this->reflection->getMethod('store');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_update_method_accepts_request_and_option(): void
    {
        $method     = $this->reflection->getMethod('update');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('option', $parameters[1]->getName());
    }

    #[Test]
    public function test_show_method_accepts_option_parameter(): void
    {
        $method     = $this->reflection->getMethod('show');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('option', $parameters[0]->getName());
    }

    #[Test]
    public function test_destroy_method_accepts_option_parameter(): void
    {
        $method     = $this->reflection->getMethod('destroy');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('option', $parameters[0]->getName());
    }

    #[Test]
    public function test_available_method_accepts_request_parameter(): void
    {
        $method     = $this->reflection->getMethod('available');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_values_by_option_id_method_accepts_request_and_option_id(): void
    {
        $method     = $this->reflection->getMethod('valuesByOptionId');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('optionId', $parameters[1]->getName());
    }

    #[Test]
    public function test_show_method_returns_json_response(): void
    {
        $method     = $this->reflection->getMethod('show');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\JsonResponse', $returnType->getName());
    }

    #[Test]
    public function test_destroy_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('destroy');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }

    #[Test]
    public function test_available_method_returns_json_response(): void
    {
        $method     = $this->reflection->getMethod('available');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\JsonResponse', $returnType->getName());
    }

    #[Test]
    public function test_values_by_option_id_method_returns_json_response(): void
    {
        $method     = $this->reflection->getMethod('valuesByOptionId');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\JsonResponse', $returnType->getName());
    }

    #[Test]
    public function test_index_data_structure(): void
    {
        $data = [
            'option_groups'      => [],
            'option_groups_data' => [],
        ];

        $this->assertArrayHasKey('option_groups', $data);
        $this->assertArrayHasKey('option_groups_data', $data);
    }
}
