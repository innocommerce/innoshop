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
}
