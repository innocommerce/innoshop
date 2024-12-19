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
use InnoShop\Common\Models\Page;

class PageRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'title', 'type' => 'input', 'label' => trans('panel/article.title')],
            ['name' => 'slug', 'type' => 'input', 'label' => trans('panel/common.slug')],
        ];
    }

    /**
     * @param  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function list($filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->paginate();
    }

    /**
     * Get page builder.
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Page::query()->with(['translation']);

        $filters = array_merge($this->filters, $filters);

        $slug = $filters['slug'] ?? '';
        if ($slug) {
            $builder->where('slug', 'like', "%$slug%");
        }

        $pageIds = $filters['page_ids'] ?? [];
        if ($pageIds) {
            $builder->whereIn('id', $pageIds);
        }

        $title = $filters['title'] ?? '';
        if ($title) {
            $builder->whereHas('translation', function ($query) use ($title) {
                $query->where('title', 'like', "%$title%");
            });
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        return fire_hook_filter('repo.page.builder', $builder);
    }

    /**
     * @param  $data
     * @return Page
     * @throws Exception|\Throwable
     */
    public function create($data): Page
    {
        DB::beginTransaction();

        try {
            $item = new Page($data);
            $item->saveOrFail();
            $item->translations()->createMany($data['translations']);
            DB::commit();

            return $item;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
     * @throws Exception
     */
    public function update($item, $data): mixed
    {
        DB::beginTransaction();

        try {
            $item->fill($data);
            $item->saveOrFail();
            $item->translations()->delete();
            $item->translations()->createMany($data['translations']);

            DB::commit();

            return $item;
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
     * @param  $keyword
     * @param  int  $limit
     * @return mixed
     */
    public function autocomplete($keyword, int $limit = 10): mixed
    {
        $builder = Page::query()->with(['translation']);
        if ($keyword) {
            $builder->whereHas('translation', function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%");
            });
        }

        return $builder->limit($limit)->get();
    }

    /**
     * Get Page list by IDs.
     *
     * @param  mixed  $PageIDs
     * @return mixed
     */
    public function getListByPageIDs(mixed $PageIDs): mixed
    {
        if (empty($PageIDs)) {
            return [];
        }
        if (is_string($PageIDs)) {
            $PageIDs = explode(',', $PageIDs);
        }

        return Page::query()
            ->with('translation')
            ->whereIn('id', $PageIDs)
            ->orderByRaw('FIELD(id, '.implode(',', $PageIDs).')')
            ->get();
    }

    /**
     * @param  $id
     * @return string
     */
    public function getNameByID($id): string
    {
        return Page::query()->find($id)->description->name ?? '';
    }
}
