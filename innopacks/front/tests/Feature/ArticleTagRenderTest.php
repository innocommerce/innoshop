<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use Illuminate\Support\Str;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Models\Tag;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Front routes are registered per locale at provider boot, before test seeders
 * run, so locale URLs are not deterministic in tests. Render the shared
 * partial (used by the article list page) directly instead of HTTP requests.
 */
class ArticleTagRenderTest extends TestCase
{
    #[Test]
    public function test_article_list_renders_tag_missing_current_locale_translation(): void
    {
        $article = Article::query()->create([
            'catalog_id' => 0,
            'slug'       => 'tag-render-'.Str::lower(Str::random(8)),
            'position'   => 0,
            'viewed'     => 0,
            'author'     => 'Tester',
            'image'      => '',
            'active'     => true,
        ]);
        $article->translations()->create([
            'locale'  => 'en',
            'title'   => 'Tag Render Article',
            'summary' => 'summary',
            'content' => '<p>content</p>',
        ]);

        $tag = Tag::query()->create(['slug' => 'zh-only-'.Str::lower(Str::random(6)), 'active' => true]);
        $tag->translations()->create(['locale' => 'zh-cn', 'name' => '仅中文标签']);
        $article->tags()->sync([$tag->id]);

        $html = view()->file(inno_path('front/resources/views/shared/articles_list.blade.php'), [
            'articles' => Article::query()->with(['tags.translations'])->where('id', $article->id)->get(),
        ])->render();

        $this->assertStringContainsString('仅中文标签', $html);
    }

    #[Test]
    public function test_tag_fallback_name_never_returns_null(): void
    {
        $tag = Tag::query()->create(['slug' => 'no-translation-'.Str::lower(Str::random(6)), 'active' => true]);
        $this->assertSame('', $tag->fallbackName());

        $tag->translations()->create(['locale' => 'zh-cn', 'name' => '只有中文']);
        $this->assertSame('只有中文', $tag->refresh()->fallbackName());
    }
}
