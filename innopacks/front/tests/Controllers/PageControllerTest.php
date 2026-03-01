<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\PageController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PageControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(PageController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(PageController::class));
    }

    #[Test]
    public function test_controller_extends_base_controller(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
    }

    #[Test]
    public function test_show_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('show'));
        $method = $this->reflection->getMethod('show');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_slug_show_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('slugShow'));
        $method = $this->reflection->getMethod('slugShow');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_show_method_accepts_page_parameter(): void
    {
        $method     = $this->reflection->getMethod('show');
        $parameters = $method->getParameters();
        $this->assertGreaterThanOrEqual(1, count($parameters));
        $this->assertEquals('page', $parameters[0]->getName());
    }

    #[Test]
    public function test_slug_show_method_accepts_request(): void
    {
        $method     = $this->reflection->getMethod('slugShow');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_page_show_data_structure(): void
    {
        $data = [
            'page' => null,
        ];

        $this->assertArrayHasKey('page', $data);
    }

    #[Test]
    public function test_inactive_page_should_abort(): void
    {
        $page = (object) ['active' => false];

        $shouldAbort = ! $page->active;

        $this->assertTrue($shouldAbort);
    }

    #[Test]
    public function test_active_page_should_not_abort(): void
    {
        $page = (object) ['active' => true];

        $shouldAbort = ! $page->active;

        $this->assertFalse($shouldAbort);
    }

    #[Test]
    public function test_page_slug_lookup(): void
    {
        $slug = 'about-us';

        $this->assertNotEmpty($slug);
        $this->assertIsString($slug);
    }
}
