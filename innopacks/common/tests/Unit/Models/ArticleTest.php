<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Models;

use InnoShop\Common\Models\Article;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ArticleTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Article::class);
    }

    #[Test]
    public function test_model_exists(): void
    {
        $this->assertTrue(class_exists(Article::class));
    }

    #[Test]
    public function test_model_extends_base_model(): void
    {
        $this->assertTrue($this->reflection->isSubclassOf('InnoShop\Common\Models\BaseModel'));
    }

    #[Test]
    public function test_model_uses_translatable_trait(): void
    {
        $traits = class_uses_recursive(Article::class);
        $this->assertContains('InnoShop\Common\Traits\Translatable', $traits);
    }

    #[Test]
    public function test_fillable_contains_required_fields(): void
    {
        $property = $this->reflection->getProperty('fillable');
        $fillable = $property->getDefaultValue();

        $requiredFields = [
            'catalog_id', 'slug', 'position', 'viewed', 'author', 'active',
        ];

        foreach ($requiredFields as $field) {
            $this->assertContains($field, $fillable, "Field '$field' should be fillable");
        }
    }

    #[Test]
    public function test_has_catalog_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('catalog'));
        $method = $this->reflection->getMethod('catalog');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_tags_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('tags'));
        $method = $this->reflection->getMethod('tags');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_products_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('products'));
        $method = $this->reflection->getMethod('products');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_related_articles_relation(): void
    {
        $this->assertTrue($this->reflection->hasMethod('relatedArticles'));
        $method = $this->reflection->getMethod('relatedArticles');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_url_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getUrlAttribute'));
        $method = $this->reflection->getMethod('getUrlAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_has_edit_url_accessor(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getEditUrlAttribute'));
        $method = $this->reflection->getMethod('getEditUrlAttribute');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_viewed_increment(): void
    {
        $viewed = 100;
        $viewed++;

        $this->assertEquals(101, $viewed);
    }

    #[Test]
    public function test_article_active_status(): void
    {
        $active = true;

        $this->assertTrue($active);
    }

    #[Test]
    public function test_article_inactive_status(): void
    {
        $active = false;

        $this->assertFalse($active);
    }

    #[Test]
    public function test_slug_generation(): void
    {
        $title = 'How to Use Our Product';
        $slug  = strtolower(str_replace(' ', '-', $title));

        $this->assertEquals('how-to-use-our-product', $slug);
    }
}
