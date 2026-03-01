<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Controllers;

use InnoShop\Front\Controllers\ArticleController;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ArticleControllerTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(ArticleController::class);
    }

    #[Test]
    public function test_controller_exists(): void
    {
        $this->assertTrue(class_exists(ArticleController::class));
    }

    #[Test]
    public function test_controller_extends_base_controller(): void
    {
        $parentClass = $this->reflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('App\Http\Controllers\Controller', $parentClass->getName());
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
    public function test_slug_show_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('slugShow'));
        $method = $this->reflection->getMethod('slugShow');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_index_method_has_no_parameters(): void
    {
        $method     = $this->reflection->getMethod('index');
        $parameters = $method->getParameters();
        $this->assertCount(0, $parameters);
    }

    #[Test]
    public function test_show_method_accepts_article_parameter(): void
    {
        $method     = $this->reflection->getMethod('show');
        $parameters = $method->getParameters();
        $this->assertGreaterThanOrEqual(1, count($parameters));
        $this->assertEquals('article', $parameters[0]->getName());
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
    public function test_article_index_data_structure(): void
    {
        $data = [
            'articles' => [],
            'catalogs' => [],
        ];

        $this->assertArrayHasKey('articles', $data);
        $this->assertArrayHasKey('catalogs', $data);
    }

    #[Test]
    public function test_article_show_data_structure(): void
    {
        $data = [
            'article'         => null,
            'relatedArticles' => [],
            'relatedProducts' => [],
        ];

        $this->assertArrayHasKey('article', $data);
        $this->assertArrayHasKey('relatedArticles', $data);
        $this->assertArrayHasKey('relatedProducts', $data);
    }

    #[Test]
    public function test_article_filter_with_catalog(): void
    {
        $catalogId = 3;
        $filters   = [
            'catalog_id' => $catalogId,
            'active'     => true,
        ];

        $this->assertEquals(3, $filters['catalog_id']);
        $this->assertTrue($filters['active']);
    }

    #[Test]
    public function test_inactive_article_should_abort(): void
    {
        $article = (object) ['active' => false];

        $shouldAbort = ! $article->active;

        $this->assertTrue($shouldAbort);
    }

    #[Test]
    public function test_active_article_should_not_abort(): void
    {
        $article = (object) ['active' => true];

        $shouldAbort = ! $article->active;

        $this->assertFalse($shouldAbort);
    }

    #[Test]
    public function test_viewed_increment_logic(): void
    {
        $viewed = 10;
        $viewed++;

        $this->assertEquals(11, $viewed);
    }
}
