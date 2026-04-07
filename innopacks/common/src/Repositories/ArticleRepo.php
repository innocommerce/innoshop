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
        return $this->builder($filters)->orderByDesc('id')->paginate();
    }

    /**
     * @param  int  $limit
     * @return mixed
     */
    public function getLatestArticles(int $limit = 4): mixed
    {
        return $this->withActive()->builder()->orderByDesc('id')->limit($limit)->get();
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
            'catalog_id'          => $article->catalog_id,
            'slug'                => $article->slug,
            'position'            => $article->position,
            'viewed'              => $article->viewed,
            'author'              => $article->author,
            'image'               => $article->image,
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
                $merged['translations'][$locale] = array_merge(
                    $merged['translations'][$locale] ?? ['locale' => $locale],
                    $fields
                );
            }
        }

        return $this->update($article, $merged);
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
            $articleData = $this->handleData($data);
            $article->fill($articleData);
            $article->saveOrFail();

            $translations = $this->handleTranslations($data['translations'] ?? []);
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
     * @param  $translations
     * @return array
     * @throws Exception
     */
    private function handleTranslations($translations): array
    {
        if (empty($translations)) {
            return [];
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

        return Article::query()
            ->with('translation')
            ->whereIn('id', $ArticleIDs)
            ->orderByRaw('FIELD(id, '.implode(',', $ArticleIDs).')')
            ->get();
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
