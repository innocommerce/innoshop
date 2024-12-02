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

        return fire_hook_filter('repo.article.builder', $builder);
    }

    /**
     * @param  $data
     * @return Article
     * @throws Exception|Throwable
     */
    public function create($data): Article
    {
        $item = new Article($this->handleData($data));
        $item->saveOrFail();

        $translations = array_values($data['translations']);
        $item->translations()->createMany($translations);

        $tagIds = $data['tag_ids'] ?? [];
        $item->tags()->sync($tagIds);

        return $item;
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
     */
    public function update($item, $data): mixed
    {
        $item->fill($this->handleData($data));
        $item->saveOrFail();

        $translations = array_values($data['translations']);
        if ($translations) {
            $item->translations()->delete();
            $item->translations()->createMany($translations);
        }

        $tagIds = $data['tag_ids'] ?? [];
        $item->tags()->sync($tagIds);

        return $item;
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
            'slug'       => $data['slug']       ?? null,
            'position'   => $data['position']   ?? 0,
            'viewed'     => $data['viewed']     ?? 0,
            'author'     => $data['author']     ?? '',
            'active'     => (bool) $data['active'],
        ];
    }

    /**
     * @param  $keyword
     * @param  int  $limit
     * @return mixed
     */
    public function autocomplete($keyword, int $limit = 10): mixed
    {
        $builder = Article::query()->with(['translation']);
        if ($keyword) {
            $builder->whereHas('translation', function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%");
            });
        }

        return $builder->limit($limit)->get();
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
}
