<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tests\Feature;

use Database\Factories\BrandFactory;
use Database\Factories\CategoryFactory;
use Database\Factories\ProductFactory;
use Illuminate\Support\Str;
use InnoShop\Aicore\Tools\ArticleUpdateTool;
use InnoShop\Aicore\Tools\BrandCreateTool;
use InnoShop\Aicore\Tools\BrandUpdateTool;
use InnoShop\Aicore\Tools\CategoryCreateTool;
use InnoShop\Aicore\Tools\CategoryUpdateTool;
use InnoShop\Aicore\Tools\ProductCreateTool;
use InnoShop\Aicore\Tools\ProductDetailTool;
use InnoShop\Aicore\Tools\ProductUpdateTool;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Models\Brand;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Product;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContentSeoToolsTest extends TestCase
{
    private function createProductWithTranslations(string $name = 'SEO Test Product', bool $withZh = true): mixed
    {
        $brand   = BrandFactory::new()->create();
        $product = ProductFactory::new()->withBrand($brand)->create(['tax_class_id' => 0]);
        $product->translations()->create(['locale' => 'en', 'name' => $name]);
        if ($withZh) {
            $product->translations()->create(['locale' => 'zh-cn', 'name' => $name.'-中文']);
        }
        $product->skus()->create([
            'images'       => [],
            'model'        => 'M1',
            'code'         => 'SKU-'.$product->id,
            'price'        => 90.00,
            'origin_price' => 120.00,
            'quantity'     => 10,
            'is_default'   => true,
            'position'     => 0,
        ]);

        return $product->refresh();
    }

    private function createArticle(string $title = 'Original Title'): Article
    {
        $article = Article::query()->create([
            'catalog_id' => 0,
            'slug'       => 'seo-article-'.Str::lower(Str::random(8)),
            'position'   => 0,
            'viewed'     => 0,
            'author'     => 'Edward',
            'image'      => '',
            'active'     => true,
        ]);
        $article->translations()->create([
            'locale'  => 'en',
            'title'   => $title,
            'summary' => 'Original summary',
            'content' => '<p>Original content</p>',
        ]);

        return $article->refresh();
    }

    #[Test]
    public function test_product_update_sets_seo_meta_for_default_locale(): void
    {
        $product = $this->createProductWithTranslations();

        $result = (new ProductUpdateTool)->execute([
            'id'           => $product->id,
            'translations' => ['en' => [
                'meta_title'       => 'Hot Pink Quilted Shoulder Bag — Chic Chain Strap Handbag | InnoShop',
                'meta_description' => 'Elevate your style with our Hot Pink Quilted Shoulder Bag.',
                'meta_keywords'    => 'hot pink shoulder bag, quilted handbag, chain strap bag',
            ]],
        ]);

        $this->assertSame('Hot Pink Quilted Shoulder Bag — Chic Chain Strap Handbag | InnoShop', $result['meta_title']);
        $this->assertSame('Elevate your style with our Hot Pink Quilted Shoulder Bag.', $result['meta_description']);
        $this->assertSame('hot pink shoulder bag, quilted handbag, chain strap bag', $result['meta_keywords']);

        $en = $product->translations()->where('locale', 'en')->first();
        $this->assertSame('Hot Pink Quilted Shoulder Bag — Chic Chain Strap Handbag | InnoShop', $en->meta_title);
        $this->assertSame('SEO Test Product', $en->name, 'Name must survive a meta-only update');

        // Response exposes all locales for verification
        $locales = collect($result['translations'])->keyBy('locale');
        $this->assertSame('Hot Pink Quilted Shoulder Bag — Chic Chain Strap Handbag | InnoShop', $locales['en']['meta_title']);
        $this->assertSame('SEO Test Product-中文', $locales['zh-cn']['name']);
    }

    #[Test]
    public function test_product_update_sets_seo_meta_for_other_locale(): void
    {
        $product = $this->createProductWithTranslations();

        $result = (new ProductUpdateTool)->execute([
            'id'           => $product->id,
            'translations' => ['zh-cn' => ['meta_title' => '玫粉色绗缝单肩包 — 时尚链条包 | InnoShop']],
        ]);

        $zh = $product->translations()->where('locale', 'zh-cn')->first();
        $this->assertSame('玫粉色绗缝单肩包 — 时尚链条包 | InnoShop', $zh->meta_title);
        $this->assertSame('SEO Test Product-中文', $zh->name, 'Existing zh-cn name must be preserved');
        $this->assertSame('SEO Test Product', $result['name']);

        $locales = collect($result['translations'])->keyBy('locale');
        $this->assertSame('玫粉色绗缝单肩包 — 时尚链条包 | InnoShop', $locales['zh-cn']['meta_title']);
    }

    #[Test]
    public function test_product_update_skips_new_locale_without_name(): void
    {
        $product = $this->createProductWithTranslations(withZh: false);

        try {
            (new ProductUpdateTool)->execute([
                'id'           => $product->id,
                'translations' => ['zh-cn' => ['meta_title' => 'only meta, no name']],
            ]);
            $this->fail('Meta-only entry for a new locale must be skipped (name is NOT NULL), leaving no fields to update.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('No fields to update', $e->getMessage());
        }

        $this->assertNull($product->translations()->where('locale', 'zh-cn')->first());
    }

    #[Test]
    public function test_product_create_with_seo_meta(): void
    {
        $result = (new ProductCreateTool)->execute([
            'name'         => 'TDK Create Product',
            'price'        => 90,
            'quantity'     => 5,
            'translations' => ['en' => [
                'meta_title'       => 'TDK Create Meta Title',
                'meta_description' => 'TDK Create Meta Description',
                'meta_keywords'    => 'tdk, create',
            ], 'zh-cn' => ['name' => 'TDK 创建产品', 'meta_title' => 'TDK 创建标题']],
        ]);

        $this->assertSame('TDK Create Meta Title', $result['meta_title']);
        $this->assertSame('TDK Create Meta Description', $result['meta_description']);
        $this->assertSame('tdk, create', $result['meta_keywords']);

        $zh = Product::query()->find($result['id'])->translations()->where('locale', 'zh-cn')->first();
        $this->assertSame('TDK 创建标题', $zh->meta_title);
    }

    #[Test]
    public function test_category_update_sets_seo_meta(): void
    {
        $category = CategoryFactory::new()->create(['parent_id' => 0]);
        $category->translations()->create(['locale' => 'en', 'name' => 'Tea Category']);

        $result = (new CategoryUpdateTool)->execute([
            'id'               => $category->id,
            'meta_title'       => 'Premium Tea Category',
            'meta_description' => 'Best herbal teas',
            'meta_keywords'    => 'tea, herbal',
            'translations'     => ['zh-cn' => ['name' => '茶分类', 'meta_title' => '优质茶分类']],
        ]);

        $this->assertSame('Premium Tea Category', $result['meta_title']);
        $this->assertSame('tea, herbal', $result['meta_keywords']);

        $en = $category->translations()->where('locale', 'en')->first();
        $this->assertSame('Tea Category', $en->name, 'Existing name must survive a meta-only update');
        $this->assertSame('Best herbal teas', $en->meta_description);

        $zh = $category->translations()->where('locale', 'zh-cn')->first();
        $this->assertSame('优质茶分类', $zh->meta_title);

        $locales = collect($result['translations'])->keyBy('locale');
        $this->assertSame('Premium Tea Category', $locales['en']['meta_title']);
        $this->assertSame('优质茶分类', $locales['zh-cn']['meta_title']);
    }

    #[Test]
    public function test_category_create_with_seo_meta(): void
    {
        $result = (new CategoryCreateTool)->execute([
            'name'         => 'TDK Category',
            'meta_title'   => 'TDK Category Title',
            'translations' => ['zh-cn' => ['name' => 'TDK 分类', 'meta_title' => 'TDK 分类标题']],
        ]);

        $this->assertSame('TDK Category Title', $result['meta_title']);

        $category = Category::query()->find($result['id']);
        $zh       = $category->translations()->where('locale', 'zh-cn')->first();
        $this->assertSame('TDK 分类标题', $zh->meta_title);
    }

    #[Test]
    public function test_brand_update_sets_seo_meta_and_preserves_other_locales(): void
    {
        $brand = BrandFactory::new()->create();
        $brand->translations()->create(['locale' => 'en', 'name' => 'HerbSoothe']);
        $brand->translations()->create(['locale' => 'zh-cn', 'name' => '草本舒']);
        $originalSlug = $brand->slug;

        $result = (new BrandUpdateTool)->execute([
            'id'            => $brand->id,
            'meta_title'    => 'HerbSoothe Official',
            'meta_keywords' => 'herbal, tea',
        ]);

        $this->assertSame('HerbSoothe Official', $result['meta_title']);
        $this->assertSame($originalSlug, $result['slug'], 'Slug must survive a meta-only update');

        $brand->refresh();
        $en = $brand->translations()->where('locale', 'en')->first();
        $this->assertSame('HerbSoothe', $en->name);
        $this->assertSame('herbal, tea', $en->meta_keywords);

        $zh = $brand->translations()->where('locale', 'zh-cn')->first();
        $this->assertNotNull($zh, 'zh-cn translation must be preserved');
        $this->assertSame('草本舒', $zh->name);

        $locales = collect($result['translations'])->keyBy('locale');
        $this->assertSame('HerbSoothe Official', $locales['en']['meta_title']);
        $this->assertSame('草本舒', $locales['zh-cn']['name']);
    }

    #[Test]
    public function test_brand_create_with_seo_meta(): void
    {
        $result = (new BrandCreateTool)->execute([
            'name'         => 'TDK Brand',
            'meta_title'   => 'TDK Brand Title',
            'translations' => ['zh-cn' => ['name' => 'TDK 品牌', 'meta_title' => 'TDK 品牌标题']],
        ]);

        $this->assertSame('TDK Brand Title', $result['meta_title']);

        $zh = Brand::query()->find($result['id'])->translations()->where('locale', 'zh-cn')->first();
        $this->assertSame('TDK 品牌标题', $zh->meta_title);
    }

    #[Test]
    public function test_article_update_sets_seo_meta(): void
    {
        $article = $this->createArticle();

        $result = (new ArticleUpdateTool)->execute([
            'id'               => $article->id,
            'meta_title'       => 'SEO Article Title',
            'meta_description' => 'SEO Article Description',
            'meta_keywords'    => 'seo, article',
        ]);

        $this->assertSame('SEO Article Title', $result['meta_title']);
        $this->assertSame('SEO Article Description', $result['meta_description']);
        $this->assertSame('seo, article', $result['meta_keywords']);
        $this->assertSame('Original Title', $result['title']);
        $this->assertSame('<p>Original content</p>', $result['content'], 'Content must survive a meta-only update');

        $en = $article->translations()->where('locale', 'en')->first();
        $this->assertSame('SEO Article Title', $en->meta_title);
    }

    #[Test]
    public function test_article_update_sets_seo_meta_for_other_locale(): void
    {
        $article = $this->createArticle();
        $article->translations()->create(['locale' => 'zh-cn', 'title' => '中文标题']);

        $result = (new ArticleUpdateTool)->execute([
            'id'           => $article->id,
            'translations' => ['zh-cn' => ['meta_title' => '中文 SEO 标题']],
        ]);

        $zh = $article->translations()->where('locale', 'zh-cn')->first();
        $this->assertSame('中文 SEO 标题', $zh->meta_title);
        $this->assertSame('中文标题', $zh->title, 'Existing zh-cn title must be preserved');

        $locales = collect($result['translations'])->keyBy('locale');
        $this->assertSame('中文 SEO 标题', $locales['zh-cn']['meta_title']);
        $this->assertSame('Original Title', $locales['en']['title']);
    }

    #[Test]
    public function test_article_update_throws_without_fields(): void
    {
        $article = $this->createArticle();

        $this->expectException(InvalidArgumentException::class);
        (new ArticleUpdateTool)->execute(['id' => $article->id]);
    }

    #[Test]
    public function test_product_detail_returns_meta_and_all_locale_translations(): void
    {
        $product = $this->createProductWithTranslations();
        $product->translations()->where('locale', 'en')->update([
            'meta_title'       => 'EN Meta Title',
            'meta_description' => 'EN Meta Description',
            'meta_keywords'    => 'en, keywords',
        ]);
        $product->translations()->where('locale', 'zh-cn')->update([
            'meta_title' => '中文 Meta 标题',
        ]);

        $result = (new ProductDetailTool)->execute(['id' => $product->id]);

        $this->assertSame('EN Meta Title', $result['meta_title']);
        $this->assertSame('EN Meta Description', $result['meta_description']);

        $locales = collect($result['translations'])->keyBy('locale');
        $this->assertSame('EN Meta Title', $locales['en']['meta_title']);
        $this->assertSame('中文 Meta 标题', $locales['zh-cn']['meta_title']);
        $this->assertSame('SEO Test Product-中文', $locales['zh-cn']['name']);
    }
}
