<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Handlers\TranslationHandler;
use InnoShop\Common\Models\Article;
use Throwable;

class ArticleRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'title', 'type' => 'input', 'label' => trans('panel/article.title')],
            ['name' => 'catalog', 'type' => 'input', 'label' => trans('panel/article.catalog')],
            ['name' => 'slug', 'type' => 'input', 'label' => trans('panel/common.slug')],
        ];
    }

    /**
     * Get search field options for data_search component
     *
     * @return array
     */
    public static function getSearchFieldOptions(): array
    {
        $options = [
            ['value' => '', 'label' => trans('panel/common.all_fields')],
            ['value' => 'title', 'label' => trans('panel/article.title')],
            ['value' => 'slug', 'label' => trans('panel/common.slug')],
        ];

        return fire_hook_filter('common.repo.article.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $filters = [
            [
                'name'    => 'active',
                'label'   => trans('panel/common.status'),
                'type'    => 'button',
                'options' => [
                    ['value' => '', 'label' => trans('panel/common.all')],
                    ['value' => '1', 'label' => trans('panel/common.active_yes')],
                    ['value' => '0', 'label' => trans('panel/common.active_no')],
                ],
            ],
        ];

        return fire_hook_filter('common.repo.article.filter_button_options', $filters);
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $paginator = $this->builder($filters)->orderByDesc('id')->paginate();
        media_cover_preload($paginator->getCollection());

        return $paginator;
    }

    /**
     * @param  int  $limit
     * @return mixed
     */
    public function getLatestArticles(int $limit = 4): mixed
    {
        $articles = $this->withActive()->builder()->orderByDesc('id')->limit($limit)->get();
        media_cover_preload($articles);

        return $articles;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Article::query()->with([
            'translation',
            'catalog.translation',
            'tags.translation',
            'relatedArticles.relatedArticle.translation',
            'products.translation',
        ]);

        $slug = $filters['slug'] ?? '';
        if ($slug) {
            $builder->where('slug', 'like', "%$slug%");
        }

        $catalogId = $filters['catalog_id'] ?? '';
        if ($catalogId) {
            $builder->where('catalog_id', $catalogId);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $catalog = $filters['catalog'] ?? '';
        if ($catalog) {
            $builder->whereHas('catalog.translation', function (Builder $query) use ($catalog) {
                $query->where('title', 'like', "%$catalog%");
            });
        }

        $tagId = $filters['tag_id'] ?? 0;
        if ($tagId) {
            $builder->whereHas('tags', function (Builder $query) use ($tagId) {
                if (is_array($tagId)) {
                    $query->whereIn('tag_id', $tagId);
                } else {
                    $query->where('tag_id', $tagId);
                }
            });
        }

        $title = $filters['title'] ?? '';
        if ($title) {
            $builder->whereHas('translation', function (Builder $query) use ($title) {
                $query->where('title', 'like', "%$title%");
            });
        }

        // Handle new search filters (keyword + search_field)
        $keyword     = $filters['keyword'] ?? '';
        $searchField = $filters['search_field'] ?? '';
        if ($keyword && $searchField) {
            if ($searchField === 'title') {
                $builder->whereHas('translation', function (Builder $query) use ($keyword) {
                    $query->where('title', 'like', "%{$keyword}%");
                });
            } else {
                $builder->where($searchField, 'like', "%{$keyword}%");
            }
        } elseif ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('slug', 'like', "%{$keyword}%")
                    ->orWhereHas('translation', function (Builder $q) use ($keyword) {
                        $q->where('title', 'like', "%{$keyword}%");
                    });
            });
        }

        // Handle date range filter
        $dateFilter = $filters['date_filter'] ?? '';
        $startDate  = $filters['start_date'] ?? '';
        $endDate    = $filters['end_date'] ?? '';

        if ($dateFilter === 'today') {
            $builder->whereDate('created_at', today());
        } elseif ($dateFilter === 'this_week') {
            $builder->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateFilter === 'this_month') {
            $builder->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($dateFilter === 'custom' && $startDate && $endDate) {
            $builder->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
        }

        return fire_hook_filter('repo.article.builder', $builder);
    }

    /**
     * @param  $data
     * @return Article
     * @throws Exception|Throwable
     */
    public function create($data): Article
    {
        $item = new Article;

        return $this->createOrUpdate($item, $data);
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
     * @throws Exception|Throwable
     */
    public function update($item, $data): mixed
    {
        return $this->createOrUpdate($item, $data);
    }

    /**
     * Partial update for REST PATCH: merge validated fields onto current state, then run the same pipeline as update().
     *
     * @param  array<string, mixed>  $data  Typically $request->validated()
     *
     * @throws Throwable
     */
    public function patch(Article $article, array $data): mixed
    {
        $article->loadMissing(['translations', 'tags', 'relatedArticles', 'products']);

        $merged = [
            'catalog_id' => $article->catalog_id,
            'slug'       => $article->slug,
            'position'   => $article->position,
            'viewed'     => $article->viewed,
            'author'     => $article->author,
            // RAW column value: $article->image is the localized accessor
            // (current locale's translation cover, falling back to main) —
            // seeding the merge with it would silently rewrite the main
            // column to a per-locale cover on image-less PATCHes.
            'image'               => $article->getRawOriginal('image'),
            'active'              => $article->active,
            'tag_ids'             => $article->tags->pluck('id')->all(),
            'related_article_ids' => $article->relatedArticles->pluck('relation_id')->filter()->all(),
            'product_ids'         => $article->products->pluck('id')->all(),
            'translations'        => [],
        ];

        foreach ($article->translations as $translation) {
            $merged['translations'][$translation->locale] = $translation->only($translation->getFillable());
        }

        if (array_key_exists('related_articles', $data)) {
            $merged['related_article_ids'] = array_values(array_filter(array_column($data['related_articles'] ?? [], 'related_id')));
            unset($data['related_articles']);
        }
        if (array_key_exists('article_products', $data)) {
            $merged['product_ids'] = array_values(array_filter(array_column($data['article_products'] ?? [], 'product_id')));
            unset($data['article_products']);
        }

        $scalarKeys = ['catalog_id', 'slug', 'position', 'viewed', 'author', 'image', 'active', 'tag_ids', 'related_article_ids', 'product_ids'];
        foreach ($scalarKeys as $key) {
            if (array_key_exists($key, $data)) {
                $merged[$key] = $data[$key];
            }
        }

        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $locale => $fields) {
                if (! is_array($fields)) {
                    continue;
                }
                $isNewLocale = ! isset($merged['translations'][$locale]);
                if ($isNewLocale && $fields === []) {
                    // Patching nothing for an unknown locale must not clone a full fallback row.
                    continue;
                }
                $merged['translations'][$locale] = array_merge(
                    $merged['translations'][$locale] ?? ['locale' => $locale],
                    $fields
                );
                // A brand-new locale row must carry title (the only NOT NULL
                // column): prefer the title being written in this same request,
                // then the stored fallback, so a meta-only patch stays valid
                // instead of violating the article_translations.title
                // constraint. empty() deliberately treats '' / '0' as missing.
                if ($isNewLocale && empty($merged['translations'][$locale]['title'])) {
                    $merged['translations'][$locale]['title'] = $this->newLocaleTitle($merged['translations'], $article);
                }
            }
        }

        return $this->update($article, $merged);
    }

    /**
     * Resolve the title for a brand-new locale row: prefer the title being
     * written in the same request (default locale), then the stored fallback.
     *
     * @param  array  $translations  Merged translations keyed by locale
     * @param  Article  $article
     * @return string
     */
    private function newLocaleTitle(array $translations, Article $article): string
    {
        $defaultTitle = $translations[setting_locale_code()]['title'] ?? null;
        if (! empty($defaultTitle)) {
            return $defaultTitle;
        }

        return $article->fallbackName('title');
    }

    /**
     * @param  Article  $article
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    private function createOrUpdate(Article $article, $data): mixed
    {
        DB::beginTransaction();

        try {
            // Capture the pre-save main image BEFORE fill()/saveOrFail() —
            // after a successful save the raw original is synced to the new
            // value, and handleTranslations() needs the old one to tell
            // "following the main image" from "explicitly per-locale". The
            // per-locale prior images serve the same purpose when the main
            // was CLEARED (no prior main to echo against).
            $priorMainImage    = (string) ($article->getRawOriginal('image') ?? '');
            $priorLocaleImages = $article->translations()->pluck('image', 'locale')->all();

            $articleData = $this->handleData($data);
            $article->fill($articleData);
            $article->saveOrFail();

            $translations = $this->handleTranslations(
                $data['translations'] ?? [],
                $articleData['image'],
                $priorMainImage,
                $priorLocaleImages
            );
            if ($translations) {
                $article->translations()->delete();
                $article->translations()->createMany($translations);
            }

            $tagIds = $data['tag_ids'] ?? [];
            $article->tags()->sync($tagIds);

            // Handle related articles
            $this->handleRelatedArticles($article, $data);

            // Handle related products
            $this->handleRelatedProducts($article, $data);

            DB::commit();

            return $article;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param  $item
     * @return void
     */
    public function destroy($item): void
    {
        $item->translations()->delete();
        $item->delete();
    }

    /**
     * @param  $data
     * @return array
     */
    private function handleData($data): array
    {
        return [
            'catalog_id' => $data['catalog_id'] ?? 0,
            'slug'       => $data['slug'] ?? null,
            'position'   => $data['position'] ?? 0,
            'viewed'     => $data['viewed'] ?? 0,
            'author'     => $data['author'] ?? '',
            'image'      => $data['image'] ?? '',
            'active'     => (bool) $data['active'],
        ];
    }

    /**
     * Handle related articles
     * @param  Article  $article
     * @param  array  $data
     * @return void
     */
    private function handleRelatedArticles(Article $article, array $data): void
    {
        // Delete existing relations
        $article->relatedArticles()->delete();

        // Handle related_article_ids array format
        if (isset($data['related_article_ids'])) {
            foreach ($data['related_article_ids'] as $articleId) {
                if ($articleId && $articleId != $article->id) {
                    $article->relatedArticles()->create([
                        'relation_id' => $articleId,
                    ]);
                }
            }
        }
    }

    /**
     * Handle related products
     * @param  Article  $article
     * @param  array  $data
     * @return void
     */
    private function handleRelatedProducts(Article $article, array $data): void
    {
        $productIds = [];

        // Handle product_ids array format
        if (isset($data['product_ids'])) {
            foreach ($data['product_ids'] as $productId) {
                if ($productId) {
                    $productIds[] = $productId;
                }
            }
        }

        $article->products()->sync($productIds);
    }

    /**
     * Merge per-locale translation fields, then apply the image propagation
     * rule: when the save carries a non-empty main image, a translation whose
     * submitted image is absent/null/empty — or still byte-equal to the PRIOR
     * main image — is "following" and adopts the new main image; any other
     * value counts as an explicit per-locale retarget and is kept.
     *
     * Comparing against the prior main image (rather than the per-locale DB
     * value) is what keeps PATCH and panel resaves safe: patch() seeds merged
     * translations from the DB and the panel form resubmits every locale's
     * image field on each save, so "submitted == DB value" holds for every
     * untouched field and carries no intent — a long-standing custom cover
     * would be indistinguishable from a following one and silently wiped.
     *
     * @param  $translations
     * @param  string  $mainImage  Resolved main articles.image value for this save.
     * @param  string  $priorMainImage  articles.image value before this save ('' on create).
     * @param  array  $priorLocaleImages  Per-locale image values before this save (locale => image).
     * @return array
     * @throws Exception
     */
    private function handleTranslations($translations, string $mainImage = '', string $priorMainImage = '', array $priorLocaleImages = []): array
    {
        if (empty($translations)) {
            return [];
        }

        if ($mainImage !== '') {
            foreach ($translations as $locale => $fields) {
                if (! is_array($fields)) {
                    continue;
                }

                $submitted = $fields['image'] ?? null;

                // "Following" = no key, null, empty string, or still equal to
                // the main image as it was BEFORE this save. In every case the
                // editor did not retarget a per-locale cover, so the new main
                // image wins. When the main was CLEARED there is no prior main
                // to echo against — "untouched" then falls back to the row's
                // OWN prior value: a stale panel echo must follow the new main
                // instead of pinning the dead cover forever, while a genuinely
                // retyped value still counts as explicit.
                $following = ($submitted === null)
                    || $submitted === ''
                    || ($priorMainImage !== '' && $submitted === $priorMainImage)
                    || ($priorMainImage === ''
                        && array_key_exists($locale, $priorLocaleImages)
                        && $submitted === (string) ($priorLocaleImages[$locale] ?? ''));

                if ($following) {
                    $translations[$locale]['image'] = $mainImage;
                }
            }
        }

        // Define field mapping for title to other fields
        $fieldMap = [
            'title' => ['content', 'summary', 'meta_title', 'meta_description', 'meta_keywords'],
        ];

        // Process translations using TranslationHandler
        return TranslationHandler::process($translations, $fieldMap);
    }

    /**
     * @param  $keyword
     * @param  int  $limit
     * @return mixed
     */
    public function autocomplete($keyword, int $limit = 10): mixed
    {
        $keyword = trim((string) $keyword);
        $builder = Article::query()->with(['translation']);
        if ($keyword !== '') {
            $builder->whereHas('translation', function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%");
            });
        }

        return $builder->orderByDesc('id')->limit($limit)->get();
    }

    /**
     * Get Article list by IDs.
     *
     * @param  mixed  $ArticleIDs
     * @return mixed
     */
    public function getListByArticleIDs(mixed $ArticleIDs): mixed
    {
        if (empty($ArticleIDs)) {
            return [];
        }
        if (is_string($ArticleIDs)) {
            $ArticleIDs = explode(',', $ArticleIDs);
        }

        // Callers hand over raw request strings ("?ids=1,2,3") — int-cast
        // every id before it can reach SQL.
        $ArticleIDs = array_map('intval', (array) $ArticleIDs);

        $articles = Article::query()
            ->with('translation')
            ->whereIn('id', $ArticleIDs)
            ->get();

        // Restore the caller's order in PHP: FIELD() is MySQL-only (breaks
        // SQLite tests and other drivers), and a raw id list has no place in
        // ORDER BY anyway.
        $rank = array_flip($ArticleIDs);

        $ordered = $articles->sortBy(fn (Article $article) => $rank[$article->id] ?? PHP_INT_MAX)->values();
        media_cover_preload($ordered);

        return $ordered;
    }

    /**
     * 获取文章的相关文章
     * @param  Article  $article
     * @param  int  $limit
     * @return mixed
     */
    public function getRelatedArticles(Article $article, int $limit = 5): mixed
    {
        // 获取相关文章（通过关联表）
        $relatedArticles = $article->relatedArticles()->with('relatedArticle.translation')
            ->whereHas('relatedArticle', function ($query) {
                $query->where('active', true);
            })
            ->limit($limit)
            ->get()
            ->pluck('relatedArticle');

        // 如果相关文章不足，按分类获取更多
        if ($relatedArticles->count() < $limit && $article->catalog_id) {
            $additionalArticles = $this->builder([
                'active'     => true,
                'catalog_id' => $article->catalog_id,
            ])
                ->where('id', '!=', $article->id)
                ->whereNotIn('id', $relatedArticles->pluck('id'))
                ->limit($limit - $relatedArticles->count())
                ->get();

            $relatedArticles = $relatedArticles->merge($additionalArticles);
        }
        media_cover_preload($relatedArticles);

        return $relatedArticles;
    }

    /**
     * 获取文章的相关商品
     * @param  Article  $article
     * @param  int  $limit
     * @return mixed
     */
    public function getRelatedProducts(Article $article, int $limit = 8): mixed
    {
        return $article->products()->where('active', true)
            ->with(['translation', 'masterSku'])
            ->limit($limit)
            ->get();
    }
}
