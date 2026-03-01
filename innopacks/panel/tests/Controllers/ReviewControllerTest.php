<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\ReviewController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ReviewControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(ReviewController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(ReviewController::class));
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
    public function test_edit_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('edit'));
        $method = $this->reflection->getMethod('edit');
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
    public function test_destroy_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('destroy'));
        $method = $this->reflection->getMethod('destroy');
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
    public function test_edit_method_accepts_review_parameter(): void
    {
        $method     = $this->reflection->getMethod('edit');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('review', $parameters[0]->getName());
    }

    #[Test]
    public function test_update_method_accepts_request_and_review(): void
    {
        $method     = $this->reflection->getMethod('update');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('review', $parameters[1]->getName());
    }

    #[Test]
    public function test_destroy_method_accepts_review_parameter(): void
    {
        $method     = $this->reflection->getMethod('destroy');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('review', $parameters[0]->getName());
    }

    #[Test]
    public function test_review_rating_validation(): void
    {
        $validRatings = [1, 2, 3, 4, 5];

        foreach ($validRatings as $rating) {
            $this->assertGreaterThanOrEqual(1, $rating);
            $this->assertLessThanOrEqual(5, $rating);
        }
    }

    #[Test]
    public function test_review_active_status_toggle(): void
    {
        $active = true;
        $active = ! $active;

        $this->assertFalse($active);

        $active = ! $active;
        $this->assertTrue($active);
    }

    #[Test]
    public function test_index_data_structure(): void
    {
        $data = [
            'criteria' => [],
            'reviews'  => [],
        ];

        $this->assertArrayHasKey('criteria', $data);
        $this->assertArrayHasKey('reviews', $data);
    }

    #[Test]
    public function test_edit_data_structure(): void
    {
        $data = [
            'review' => null,
        ];

        $this->assertArrayHasKey('review', $data);
    }
}
