<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\UploadController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class UploadControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(UploadController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(UploadController::class));
    }

    #[Test]
    public function test_images_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('images'));
        $method = $this->reflection->getMethod('images');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_files_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('files'));
        $method = $this->reflection->getMethod('files');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_images_method_accepts_upload_image_request(): void
    {
        $method     = $this->reflection->getMethod('images');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_files_method_accepts_upload_file_request(): void
    {
        $method     = $this->reflection->getMethod('files');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }
}
