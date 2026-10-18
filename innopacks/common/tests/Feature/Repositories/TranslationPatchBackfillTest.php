<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Repositories;

use Illuminate\Support\Str;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Tests\TestCase;
use Tests\Traits\CreatesProduct;

/**
 * PATCH semantics must accept partial (e.g. meta-only) translations for a
 * locale that has no translation row yet: the repo backfills the NOT NULL
 * column (article_translations.title / product_translations.name) from the
 * fallback translation instead of violating the constraint.
 */
class TranslationPatchBackfillTest extends TestCase
{
    use CreatesProduct;

    public function test_article_patch_new_locale_meta_only_backfills_title(): void
    {
        $article = $this->createArticleWithTitle('Original Title');

        ArticleRepo::getInstance()->patch($article, [
            'translations' => ['zh-cn' => ['meta_title' => 'Chinese SEO Title']],
        ]);

        $zh = $article->translations()->where('locale', 'zh-cn')->first();
        $this->assertNotNull($zh, 'New-locale meta-only patch must create the zh-cn row');
        $this->assertSame('Chinese SEO Title', $zh->meta_title);
        $this->assertSame('Original Title', $zh->title, 'New-locale row must inherit the fallback title');

        $en = $article->translations()->where('locale', 'en')->first();
        $this->assertSame('Original Title', $en->title, 'Existing en row must stay untouched');
        $this->assertEmpty($en->meta_title);
    }

    public function test_article_patch_existing_locale_meta_only_keeps_title(): void
    {
        $article = $this->createArticleWithTitle('Original Title');

        ArticleRepo::getInstance()->patch($article, [
            'translations' => ['en' => ['meta_description' => 'Fresh description']],
        ]);

        $en = $article->translations()->where('locale', 'en')->first();
        $this->assertSame('Original Title', $en->title, 'Meta-only patch must not clobber the title');
        $this->assertSame('Fresh description', $en->meta_description);
    }

    public function test_product_patch_new_locale_meta_only_backfills_name(): void
    {
        $product = $this->createProduct(['brand_id' => 0, 'tax_class_id' => 0]);
        $product->translations()->create(['locale' => 'en', 'name' => 'Base Name']);
        $product->refresh();

        ProductRepo::getInstance()->patch($product, [
            'translations' => [['locale' => 'zh-cn', 'meta_title' => 'Chinese SEO Title']],
        ]);

        $zh = $product->translations()->where('locale', 'zh-cn')->first();
        $this->assertNotNull($zh, 'New-locale meta-only patch must create the zh-cn row');
        $this->assertSame('Chinese SEO Title', $zh->meta_title);
        $this->assertSame('Base Name', $zh->name, 'New-locale row must inherit the fallback name');

        $en = $product->translations()->where('locale', 'en')->first();
        $this->assertSame('Base Name', $en->name);
        $this->assertEmpty($en->meta_title);
    }

    public function test_article_patch_new_locale_prefers_in_request_title(): void
    {
        $article = $this->createArticleWithTitle('Original Title');

        // Rename the default locale and add a meta-only locale in one request:
        // the new row must inherit the NEW title, not the stale stored one.
        ArticleRepo::getInstance()->patch($article, [
            'translations' => [
                'en'    => ['title' => 'Renamed Title'],
                'zh-cn' => ['meta_title' => 'Chinese SEO Title'],
            ],
        ]);

        $zh = $article->translations()->where('locale', 'zh-cn')->first();
        $this->assertNotNull($zh);
        $this->assertSame('Renamed Title', $zh->title, 'New-locale row must not inherit a stale fallback title');
    }

    public function test_article_patch_new_locale_explicit_title_not_overwritten(): void
    {
        $article = $this->createArticleWithTitle('Original Title');

        ArticleRepo::getInstance()->patch($article, [
            'translations' => ['zh-cn' => ['title' => '自定义标题']],
        ]);

        $zh = $article->translations()->where('locale', 'zh-cn')->first();
        $this->assertSame('自定义标题', $zh->title, 'Explicit title must survive the backfill logic');
    }

    public function test_article_patch_new_locale_empty_fields_creates_no_row(): void
    {
        $article = $this->createArticleWithTitle('Original Title');

        ArticleRepo::getInstance()->patch($article, [
            'translations' => ['zh-cn' => []],
        ]);

        $this->assertNull(
            $article->translations()->where('locale', 'zh-cn')->first(),
            'An empty patch for an unknown locale must not clone a full fallback row'
        );
    }

    public function test_article_patch_new_locale_without_any_translations_stays_valid(): void
    {
        // Article with zero translation rows: no fallback source exists, the
        // insert must still succeed (empty-string title satisfies NOT NULL).
        $article = Article::query()->create([
            'catalog_id' => 0,
            'slug'       => 'no-translations-'.Str::lower(Str::random(8)),
            'position'   => 0,
            'viewed'     => 0,
            'author'     => 'Edward',
            'image'      => '',
            'active'     => true,
        ]);

        ArticleRepo::getInstance()->patch($article, [
            'translations' => ['en' => ['meta_title' => 'Bare SEO Title']],
        ]);

        $en = $article->translations()->where('locale', 'en')->first();
        $this->assertNotNull($en);
        $this->assertSame('Bare SEO Title', $en->meta_title);
        $this->assertSame('', $en->title, 'No fallback source means an empty-string title');
    }

    public function test_product_patch_new_locale_prefers_in_request_name(): void
    {
        $product = $this->createProduct(['brand_id' => 0, 'tax_class_id' => 0]);
        $product->translations()->create(['locale' => 'en', 'name' => 'Base Name']);
        $product->refresh();

        ProductRepo::getInstance()->patch($product, [
            'translations' => [
                ['locale' => 'en', 'name' => 'Renamed Name'],
                ['locale' => 'zh-cn', 'meta_title' => 'Chinese SEO Title'],
            ],
        ]);

        $zh = $product->translations()->where('locale', 'zh-cn')->first();
        $this->assertNotNull($zh);
        $this->assertSame('Renamed Name', $zh->name, 'New-locale row must not inherit a stale fallback name');
    }

    /**
     * @param  string  $title
     * @return Article
     */
    private function createArticleWithTitle(string $title): Article
    {
        $article = Article::query()->create([
            'catalog_id' => 0,
            'slug'       => 'patch-backfill-'.Str::lower(Str::random(8)),
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
}
