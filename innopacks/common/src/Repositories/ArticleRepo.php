<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\Article;

class ArticleRepo extends BaseRepo
{
    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     * @throws \Exception
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

        return fire_hook_filter('repo.article.builder', $builder);
    }

    /**
     * @param  $data
     * @return Article
     * @throws \Exception|\Throwable
     */
    public function create($data): Article
    {
        $item = new Article($data);
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
        $item->fill($data);
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
}
