<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Repositories;

use InnoShop\Common\Models\Article;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Tests\TestCase;

class ArticleRepoTest extends TestCase
{
    /**
     * A translation whose submitted image is byte-equal to the DB value is
     * "untouched" — the new main image must propagate into it, mirroring how
     * the panel resubmits every locale's image field on each save.
     */
    public function test_update_propagates_new_main_image_to_untouched_translations(): void
    {
        $article = $this->makeArticle('a.jpg', [
            'en' => ['locale' => 'en', 'title' => 'EN', 'image' => 'a.jpg'],
        ]);

        ArticleRepo::getInstance()->update($article, [
            'slug'         => $article->slug,
            'image'        => 'b.jpg',
            'active'       => true,
            'translations' => ['en' => ['locale' => 'en', 'title' => 'EN', 'image' => 'a.jpg']],
        ]);

        $article->refresh();
        $this->assertSame('b.jpg', $article->getRawOriginal('image'));
        $this->assertSame('b.jpg', $article->translations->firstWhere('locale', 'en')->image);
    }

    /**
     * A translation image that differs from the DB value counts as an explicit
     * per-locale retarget and must survive a main image change.
     */
    public function test_explicit_per_locale_image_survives_main_image_change(): void
    {
        $article = $this->makeArticle('a.jpg', [
            'en' => ['locale' => 'en', 'title' => 'EN', 'image' => 'a.jpg'],
        ]);

        ArticleRepo::getInstance()->update($article, [
            'slug'         => $article->slug,
            'image'        => 'b.jpg',
            'active'       => true,
            'translations' => ['en' => ['locale' => 'en', 'title' => 'EN', 'image' => 'custom.jpg']],
        ]);

        $article->refresh();
        $this->assertSame('b.jpg', $article->getRawOriginal('image'));
        $this->assertSame('custom.jpg', $article->translations->firstWhere('locale', 'en')->image);
    }

    /**
     * REST callers may send image: null to clear the main image. With an empty
     * main image the propagation step must be skipped entirely: no throw, the
     * column actually clears, and explicit per-locale images stay untouched.
     */
    public function test_null_main_image_does_not_throw(): void
    {
        $article = $this->makeArticle('a.jpg', [
            ['locale' => 'en', 'title' => 'EN', 'image' => 'custom.jpg'],
        ]);

        ArticleRepo::getInstance()->update($article, [
            'slug'         => $article->slug,
            'image'        => null,
            'active'       => true,
            'translations' => [['locale' => 'en', 'title' => 'EN', 'image' => 'custom.jpg']],
        ]);

        $article->refresh();
        // handleData() normalizes null to '' via ?? '' — "clear" is an empty column.
        $this->assertSame('', $article->getRawOriginal('image'));
        $this->assertSame('custom.jpg', $article->translations->firstWhere('locale', 'en')->image);
    }

    /**
     * Issue #291 BLOCKER: a REST PATCH that only changes the main image must
     * leave already-saved custom per-locale covers alone. patch() seeds merged
     * translations from the DB, so "submitted == DB value" is true for every
     * untouched field and can never mean "editor retargeted" — the old
     * heuristic silently overwrote each per-locale image with the new main.
     */
    public function test_patch_image_only_preserves_per_locale_images(): void
    {
        // Use two locales that are actually enabled in this environment —
        // TranslationHandler drops translations for disabled locales.
        [$localeA, $localeB] = array_pad(enabled_locale_codes(), 2, null);
        $article             = $this->makeArticle('a.jpg', [
            ['locale' => $localeA, 'title' => 'A', 'image' => 'custom.jpg'],
            ['locale' => $localeB, 'title' => 'B', 'image' => 'custom-b.jpg'],
        ]);

        ArticleRepo::getInstance()->patch($article, ['image' => 'b.jpg']);

        $article->refresh();
        $this->assertSame('b.jpg', $article->getRawOriginal('image'));
        $this->assertSame('custom.jpg', $article->translations->firstWhere('locale', $localeA)->image);
        if ($localeB) {
            $this->assertSame('custom-b.jpg', $article->translations->firstWhere('locale', $localeB)->image);
        }
    }

    /**
     * Same root cause on the full-form path: the panel resubmits every
     * locale's image field on each save, so an unchanged custom per-locale
     * cover always equals the DB value. Only comparison against the PRIOR
     * main image can tell "following the main image" from "explicitly
     * custom" — a long-standing custom cover must survive a main retarget.
     */
    public function test_resave_preserves_unchanged_custom_per_locale_image(): void
    {
        $article = $this->makeArticle('a.jpg', [
            ['locale' => 'en', 'title' => 'EN', 'image' => 'custom.jpg'],
        ]);

        // Second save: main image retargeted, per-locale value resubmitted unchanged.
        // Locale-keyed map — the format the panel form actually submits.
        ArticleRepo::getInstance()->update($article, [
            'slug'         => $article->slug,
            'image'        => 'b.jpg',
            'active'       => true,
            'translations' => ['en' => ['locale' => 'en', 'title' => 'EN', 'image' => 'custom.jpg']],
        ]);

        $article->refresh();
        $this->assertSame('b.jpg', $article->getRawOriginal('image'));
        $this->assertSame('custom.jpg', $article->translations->firstWhere('locale', 'en')->image);
    }

    /**
     * patch() must default the main image from the RAW column, never from the
     * localized $article->image accessor: the accessor returns the current
     * locale's translation cover (falling back to main), so seeding the merge
     * with it silently rewrote the main column to a per-locale cover — and
     * once main equaled a locale cover, the propagation rule classified that
     * locale as "following" and let the next main change clobber it (#291).
     */
    public function test_patch_without_image_keeps_raw_main_image(): void
    {
        $article = $this->makeArticle('a.jpg', [
            ['locale' => 'en', 'title' => 'EN', 'image' => 'custom.jpg'],
        ]);

        ArticleRepo::getInstance()->patch($article, ['active' => true]);

        $article->refresh();
        $this->assertSame('a.jpg', $article->getRawOriginal('image'));
        $this->assertSame('custom.jpg', $article->translations->firstWhere('locale', 'en')->image);
    }

    /**
     * The id list comes from raw request strings ("?ids=1,2,3"). Ids must be
     * int-cast before touching SQL (the old orderByRaw glued them in verbatim
     * — injectable), and the caller's order must survive the round trip
     * without MySQL-only FIELD() so the repo stays driver-portable.
     *
     * NB: the "injection safe" in the test name is the EVIDENCE side, not
     * the defense mechanism. The actual defense is the upstream intval() in
     * getListByArticleIDs() that truncates "999) or (1=1" to the integer
     * 999 — by the time anything reaches SQL the payload is digits only.
     * This test exists to make sure that intval() stays in place; if a
     * future refactor drops it the test will start surfacing raw id=0 rows
     * or fail the order assertion.
     */
    public function test_list_by_article_ids_preserves_order_and_is_injection_safe(): void
    {
        $a    = $this->makeArticle('a.jpg', [['locale' => 'en', 'title' => 'A', 'image' => '']]);
        $b    = $this->makeArticle('b.jpg', [['locale' => 'en', 'title' => 'B', 'image' => '']]);
        $c    = $this->makeArticle('c.jpg', [['locale' => 'en', 'title' => 'C', 'image' => '']]);
        $repo = ArticleRepo::getInstance();

        $ordered = $repo->getListByArticleIDs("{$c->id},{$a->id},{$b->id}");
        $this->assertSame([$c->id, $a->id, $b->id], $ordered->pluck('id')->all());

        // "999) or (1=1" passes through intval() as the integer 999 (no
        // such id), then the whereIn probe returns nothing for that slot —
        // the surviving [$a, $b] must come back in their submitted order.
        // If intval() were ever removed the parentheses would reach
        // whereIn() and the query would 500; this assertion is what catches
        // that regression.
        $safe = $repo->getListByArticleIDs("{$a->id},{$b->id},999) or (1=1");
        $this->assertSame([$a->id, $b->id], $safe->pluck('id')->all());
    }

    /**
     * After the main image was CLEARED, per-locale rows keep their last
     * propagated value (propagation skips empty mains). When a new main is
     * saved while the panel re-echoes that stale value, the echo must count
     * as "following" — otherwise every locale stays pinned at the dead
     * cover forever.
     */
    public function test_following_locales_recover_after_main_image_was_cleared(): void
    {
        $article = $this->makeArticle('m1.jpg', [['locale' => 'en', 'title' => 'EN', 'image' => '']]);
        $repo    = ArticleRepo::getInstance();

        // Locale follows m1, main gets cleared: rows keep m1 (no propagation).
        $repo->update($article, [
            'slug'         => $article->slug,
            'image'        => 'm1.jpg',
            'active'       => true,
            'translations' => ['en' => ['locale' => 'en', 'title' => 'EN', 'image' => 'm1.jpg']],
        ]);
        $repo->update($article, [
            'slug'         => $article->slug,
            'image'        => '',
            'active'       => true,
            'translations' => ['en' => ['locale' => 'en', 'title' => 'EN', 'image' => 'm1.jpg']],
        ]);

        // Retarget to m2 while re-echoing the stale m1 → must follow m2.
        $repo->update($article, [
            'slug'         => $article->slug,
            'image'        => 'm2.jpg',
            'active'       => true,
            'translations' => ['en' => ['locale' => 'en', 'title' => 'EN', 'image' => 'm1.jpg']],
        ]);

        $article->refresh();
        $this->assertSame('m2.jpg', $article->translations->firstWhere('locale', 'en')->image, 'stale echo of a cleared main must follow the new main, not pin the dead value');
    }

    /**
     * @param  string  $mainImage
     * @param  array  $translations  Locale-keyed map (panel style) or list of rows (REST style).
     * @return Article
     */
    private function makeArticle(string $mainImage, array $translations): Article
    {
        return ArticleRepo::getInstance()->create([
            'slug'         => 'cover-test-'.uniqid(),
            'image'        => $mainImage,
            'active'       => true,
            'translations' => $translations,
        ]);
    }
}
