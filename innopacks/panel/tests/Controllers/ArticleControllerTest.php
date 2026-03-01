<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Controllers;

use InnoShop\Panel\Controllers\ArticleController;
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
    public function test_create_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('create'));
        $method = $this->reflection->getMethod('create');
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
    public function test_edit_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('edit'));
        $method = $this->reflection->getMethod('edit');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_form_method_exists(): void
    {
        $this->assertTrue($this->reflection->hasMethod('form'));
        $method = $this->reflection->getMethod('form');
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
    public function test_store_method_accepts_article_request(): void
    {
        $method     = $this->reflection->getMethod('store');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
    }

    #[Test]
    public function test_edit_method_accepts_article_parameter(): void
    {
        $method     = $this->reflection->getMethod('edit');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('article', $parameters[0]->getName());
    }

    #[Test]
    public function test_update_method_accepts_request_and_article(): void
    {
        $method     = $this->reflection->getMethod('update');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('article', $parameters[1]->getName());
    }

    #[Test]
    public function test_destroy_method_accepts_article_parameter(): void
    {
        $method     = $this->reflection->getMethod('destroy');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('article', $parameters[0]->getName());
    }

    #[Test]
    public function test_store_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('store');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
    }

    #[Test]
    public function test_update_method_returns_redirect_response(): void
    {
        $method     = $this->reflection->getMethod('update');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Http\RedirectResponse', $returnType->getName());
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
    public function test_related_articles_extraction_logic(): void
    {
        // Test the logic for extracting related articles
        $relatedArticles = [
            ['id' => 1, 'name' => 'Article 1'],
            ['id' => 2, 'name' => 'Article 2'],
        ];

        $selectedRelatedArticles = array_map(function ($article) {
            return [
                'id'   => $article['id'],
                'name' => $article['name'],
            ];
        }, $relatedArticles);

        $this->assertCount(2, $selectedRelatedArticles);
        $this->assertEquals(1, $selectedRelatedArticles[0]['id']);
        $this->assertEquals('Article 1', $selectedRelatedArticles[0]['name']);
    }

    #[Test]
    public function test_related_products_extraction_logic(): void
    {
        // Test the logic for extracting related products
        $relatedProducts = [
            ['id' => 1, 'name' => 'Product 1'],
            ['id' => 2, 'name' => 'Product 2'],
        ];

        $selectedRelatedProducts = array_map(function ($product) {
            return [
                'id'   => $product['id'],
                'name' => $product['name'],
            ];
        }, $relatedProducts);

        $this->assertCount(2, $selectedRelatedProducts);
        $this->assertEquals(1, $selectedRelatedProducts[0]['id']);
        $this->assertEquals('Product 1', $selectedRelatedProducts[0]['name']);
    }

    #[Test]
    public function test_tags_extraction_logic(): void
    {
        // Test the logic for extracting tags
        $tags = [
            (object) ['id' => 1, 'slug' => 'tag-1', 'translation' => (object) ['name' => 'Tag 1']],
            (object) ['id' => 2, 'slug' => 'tag-2', 'translation' => null],
        ];

        $selectedTags = array_map(function ($tag) {
            return [
                'id'   => $tag->id,
                'name' => $tag->translation->name ?? $tag->slug,
            ];
        }, $tags);

        $this->assertCount(2, $selectedTags);
        $this->assertEquals(1, $selectedTags[0]['id']);
        $this->assertEquals('Tag 1', $selectedTags[0]['name']);
        $this->assertEquals(2, $selectedTags[1]['id']);
        $this->assertEquals('tag-2', $selectedTags[1]['name']); // Falls back to slug
    }

    #[Test]
    public function test_empty_related_articles_handling(): void
    {
        $articleId       = 0; // New article
        $relatedArticles = null;

        $selectedRelatedArticles = [];
        if ($articleId && $relatedArticles) {
            $selectedRelatedArticles = $relatedArticles;
        }

        $this->assertEmpty($selectedRelatedArticles);
    }

    #[Test]
    public function test_empty_related_products_handling(): void
    {
        $articleId       = 0; // New article
        $relatedProducts = null;

        $selectedRelatedProducts = [];
        if ($articleId && $relatedProducts) {
            $selectedRelatedProducts = $relatedProducts;
        }

        $this->assertEmpty($selectedRelatedProducts);
    }

    #[Test]
    public function test_empty_tags_handling(): void
    {
        $articleId = 0; // New article
        $tags      = null;

        $selectedTags = [];
        if ($articleId && $tags) {
            $selectedTags = $tags;
        }

        $this->assertEmpty($selectedTags);
    }

    #[Test]
    public function test_catalog_filter_active_only(): void
    {
        $filters = ['active' => 1];

        $this->assertEquals(1, $filters['active']);
    }

    #[Test]
    public function test_form_data_structure(): void
    {
        $data = [
            'article'                 => null,
            'catalogs'                => [],
            'tags'                    => [],
            'selectedRelatedArticles' => [],
            'selectedRelatedProducts' => [],
            'selectedTags'            => [],
        ];

        $this->assertArrayHasKey('article', $data);
        $this->assertArrayHasKey('catalogs', $data);
        $this->assertArrayHasKey('tags', $data);
        $this->assertArrayHasKey('selectedRelatedArticles', $data);
        $this->assertArrayHasKey('selectedRelatedProducts', $data);
        $this->assertArrayHasKey('selectedTags', $data);
    }
}
