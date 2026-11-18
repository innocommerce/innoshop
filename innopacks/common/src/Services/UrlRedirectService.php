<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Eloquent\Model;
use InnoShop\Common\Middleware\HandleUrlRedirect;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Models\Brand;
use InnoShop\Common\Models\Catalog;
use InnoShop\Common\Models\Category;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Tag;
use InnoShop\Common\Models\UrlRedirect;

/**
 * Core SEO redirect mechanism (factory #157).
 *
 * Stores permanent 301 rules so links Google collected for demo/renamed
 * content keep working. Thin core on purpose: rules are written here, the
 * management UI (bulk import, regex, 404 log) belongs to a future plugin.
 *
 * Semantics
 *  - paths are locale-less canonical (`product-demo-a`); the request's
 *    locale prefix is stripped before lookup and re-applied on the target,
 *  - renaming a→b then b→c collapses to single hops: a→c and b→c,
 *  - renaming back to a live-again path deletes the rule FROM that path and
 *    re-points stale rows at the reclaimed home,
 *  - manual rules that would ping-pong (x→y, then y→x) are rejected.
 */
class UrlRedirectService
{
    /**
     * Slug-bearing entities wired to the redirect recorder: model class
     * => canonical path prefix (matches the `product-{slug}` route shape).
     */
    public const SLUG_PATH_PREFIXES = [
        Product::class  => 'product',
        Article::class  => 'article',
        Category::class => 'category',
        Catalog::class  => 'catalog',
        Page::class     => 'page',
        Brand::class    => 'brand',
        Tag::class      => 'tag',
    ];

    private const MAX_CHAIN_HOPS = 10;

    /**
     * Wire slug-change recording onto every mapped entity and prepend the
     * 404-intercepting middleware to the GLOBAL stack. Mirrors the static
     * registration pattern of NotificationEventSubscriber.
     *
     * Global, not the front route group, on purpose: Google's stale URLs
     * usually 404 at the ROUTER level (no matching route), which never
     * reaches route-group middleware. The global stack sees both router
     * and controller 404s; panel/admin/api are excluded inside the
     * middleware itself.
     */
    public static function register(): void
    {
        foreach (self::SLUG_PATH_PREFIXES as $modelClass => $prefix) {
            $modelClass::updating(function (Model $model) {
                self::recordSlugChange($model);
            });
        }

        app(Kernel::class)->prependMiddleware(HandleUrlRedirect::class);
    }

    /**
     * Record a redirect when an entity's slug is about to change.
     * Called from the `updating` model event, so getOriginal() still holds
     * the pre-save slug. Creating, unchanged slugs and cleared slugs are
     * all no-ops.
     */
    public static function recordSlugChange(Model $model): void
    {
        $prefix = self::SLUG_PATH_PREFIXES[$model::class] ?? null;
        if ($prefix === null) {
            return;
        }

        $oldSlug = $model->getOriginal('slug');
        $newSlug = $model->getAttributes()['slug'] ?? null;
        if (empty($oldSlug) || empty($newSlug) || $oldSlug === $newSlug) {
            return;
        }

        self::upsert($prefix.'-'.$oldSlug, $prefix.'-'.$newSlug, $prefix, $model->getKey());
    }

    /**
     * Write (or update) a redirect rule, chasing and collapsing chains so
     * every historical path reaches the live target in a single 301.
     *
     * @param  string  $oldPath  e.g. `product-demo-a`, `deleted-page`, full URL accepted as target
     * @param  string  $newPath  internal path, `/` for home, or absolute external URL
     * @param  string|null  $entityType  entity context; null marks a manual rule
     */
    public static function upsert(string $oldPath, string $newPath, ?string $entityType = null, ?int $entityId = null): bool
    {
        $old = self::canonicalize($oldPath);
        $new = self::canonicalize($newPath);
        // new === '' is the home rule (`redirects:add gone-page /`), allowed.
        if ($old === '' || $old === $new) {
            return false;
        }

        $terminal = $new;
        for ($i = 0; $i < self::MAX_CHAIN_HOPS; $i++) {
            $next = UrlRedirect::query()->where('old_path', $terminal)->value('new_path');
            if ($next === null || $next === $terminal) {
                break;
            }
            $terminal = $next;
            if ($terminal === $old) {
                break;
            }
        }

        // Writing a path that loops back onto itself: either the slug came
        // back home (entity rename, keep the history valid) or a manual rule
        // would ping-pong visitors forever (reject).
        if ($terminal === $old) {
            if ($entityType === null) {
                return false;
            }

            // The reclaimed path is live again: no rule may point away from it.
            UrlRedirect::query()->where('old_path', $new)->delete();
            // Rows aimed at the vacated slug follow the entity to its new home.
            UrlRedirect::query()->where('new_path', $old)->update(['new_path' => $new]);
            UrlRedirect::query()->updateOrCreate(
                ['old_path' => $old],
                ['new_path' => $new, 'entity_type' => $entityType, 'entity_id' => $entityId]
            );

            return true;
        }

        UrlRedirect::query()->updateOrCreate(
            ['old_path' => $old],
            ['new_path' => $terminal, 'entity_type' => $entityType, 'entity_id' => $entityId]
        );

        // Backward collapse: rules pointing at the just-moved path jump ahead.
        UrlRedirect::query()->where('new_path', $old)->update(['new_path' => $terminal]);

        return true;
    }

    /**
     * Resolve a request path to its redirect target, preserving the locale
     * context. Returns the target ready for redirect()->to(): internal paths
     * re-prefixed, external URLs verbatim. Null when nothing matches.
     */
    public static function resolve(string $path): ?string
    {
        [$localePrefix, $canonical] = self::splitLocalePrefix($path);
        if ($canonical === '') {
            return null;
        }

        $row = UrlRedirect::query()->where('old_path', $canonical)->first();
        if ($row === null) {
            return null;
        }

        $row->increment('hits');

        $target = $row->new_path;
        if (self::isExternal($target)) {
            return $target;
        }

        if ($target === '' || $target === '/') {
            return $localePrefix ?? '/';
        }

        return $localePrefix !== null ? $localePrefix.'/'.$target : $target;
    }

    /**
     * Split a request path into [locale prefix, canonical path]. Only a
     * leading segment matching an enabled locale code counts as prefix.
     *
     * @return array{0: ?string, 1: string}
     */
    public static function splitLocalePrefix(string $path): array
    {
        $trimmed = self::canonicalize($path);
        if ($trimmed === '' || ! str_contains($trimmed, '/')) {
            return [null, $trimmed];
        }

        [$head, $rest] = explode('/', $trimmed, 2);
        if (in_array($head, enabled_locale_codes(), true)) {
            return [$head, self::canonicalize($rest)];
        }

        return [null, $trimmed];
    }

    /**
     * Normalize a path for storage/lookup: strip whitespace and edge
     * slashes. The root collapses to '' (the "home" rule stores '' as
     * new_path; resolve() maps it back to `/`).
     */
    private static function canonicalize(string $path): string
    {
        return trim($path, "/ \t\n\r");
    }

    private static function isExternal(string $path): bool
    {
        return (bool) preg_match('#^https?://#i', $path);
    }
}
