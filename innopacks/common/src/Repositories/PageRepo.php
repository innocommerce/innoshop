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
        $item = new Page;

        return $this->createOrUpdate($item, $data);
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
     * @throws Exception|\Throwable
     */
    public function update($item, $data): mixed
    {
        return $this->createOrUpdate($item, $data);
    }

    /**
     * @param  Page  $page
     * @param  $data
     * @return mixed
     * @throws Exception|\Throwable
     */
    private function createOrUpdate(Page $page, $data): mixed
    {
        DB::beginTransaction();

        try {
            $pageData = $this->handleData($data);
            $page->fill($pageData);
            $page->saveOrFail();

            $translations = $this->handleTranslations($data['translations'] ?? []);
            if ($translations) {
                $page->translations()->delete();
                $page->translations()->createMany($translations);
            }

            DB::commit();

            return $page;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param  $data
     * @return array
     */
    private function handleData($data): array
    {
        return [
            'slug'            => $data['slug']            ?? null,
            'viewed'          => $data['viewed']          ?? 0,
            'position'        => $data['position']        ?? 0,
            'show_breadcrumb' => $data['show_breadcrumb'] ?? false,
            'active'          => (bool) ($data['active'] ?? true),
        ];
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
            'title' => ['content', 'meta_title', 'meta_description', 'meta_keywords'],
        ];

        // Process translations using TranslationHandler
        return TranslationHandler::process($translations, $fieldMap);
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
