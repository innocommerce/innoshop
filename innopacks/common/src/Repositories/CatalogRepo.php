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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Handlers\TranslationHandler;
use InnoShop\Common\Models\Catalog;
use Throwable;

class CatalogRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'title', 'type' => 'input', 'label' => trans('panel/catalog.title')],
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
     * @return Collection
     */
    public function getTopCatalogs(): Collection
    {
        $filters = [
            'parent_id' => 0,
        ];

        return $this->withActive()->builder($filters)->get();
    }

    /**
     * @param  $title
     * @return Builder[]|Collection
     */
    public function searchByTitle($title): Collection|array
    {
        $filters = [
            'title' => $title,
        ];

        return $this->builder($filters)->limit(10)->get();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $filters = array_merge($this->filters, $filters);
        $builder = Catalog::query()->with([
            'translation',
            'parent.translation',
            'children.translation',
        ]);

        $slug = $filters['slug'] ?? '';
        if ($slug) {
            $builder->where('slug', 'like', "%$slug%");
        }

        $catalogIds = $filters['catalog_ids'] ?? [];
        if ($catalogIds) {
            $builder->whereIn('id', $catalogIds);
        }

        if (isset($filters['parent_id'])) {
            $parentID = (int) $filters['parent_id'];
            if ($parentID == 0) {
                $builder->where(function (Builder $query) {
                    $query->where('parent_id', 0)->orWhereNull('parent_id');
                });
            } else {
                $builder->where('parent_id', $parentID);
            }
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $excludeId = $filters['exclude_id'] ?? null;
        if ($excludeId) {
            $builder->where('id', '!=', $excludeId);
        }

        $title = $filters['title'] ?? '';
        if ($title) {
            $builder->whereHas('translation', function ($query) use ($title) {
                $query->where('title', 'like', "%$title%");
            });
        }

        return fire_hook_filter('repo.catalog.builder', $builder);
    }

    /**
     * @param  $data
     * @return Catalog
     * @throws Exception|Throwable
     */
    public function create($data): Catalog
    {
        $item = new Catalog;

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
     * @param  Catalog  $catalog
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    private function createOrUpdate(Catalog $catalog, $data): mixed
    {

        DB::beginTransaction();

        try {
            $catalogData = $this->handleData($data);
            $catalog->fill($catalogData);
            $catalog->saveOrFail();

            $translations = $this->handleTranslations($data['translations'] ?? []);
            if ($translations) {
                $catalog->translations()->delete();
                $catalog->translations()->createMany($translations);
            }

            DB::commit();

            return $catalog;
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
        $builder = Catalog::query()->with(['translation']);
        if ($keyword) {
            $builder->whereHas('translation', function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%");
            });
        }

        return $builder->limit($limit)->get();
    }

    /**
     * Get catalog list by IDs.
     *
     * @param  mixed  $CatalogIDs
     * @return mixed
     */
    public function getListByCatalogIDs(mixed $CatalogIDs): mixed
    {
        if (empty($CatalogIDs)) {
            return [];
        }
        if (is_string($CatalogIDs)) {
            $CatalogIDs = explode(',', $CatalogIDs);
        }

        return Catalog::query()
            ->with('translation')
            ->whereIn('id', $CatalogIDs)
            ->orderByRaw('FIELD(id, '.implode(',', $CatalogIDs).')')
            ->get();
    }

    /**
     * @param  $data
     * @return array
     */
    private function handleData($data): array
    {
        return [
            'parent_id' => $data['parent_id'] ?? 0,
            'slug'      => $data['slug'] ?? null,
            'position'  => $data['position'] ?? 0,
            'active'    => (bool) $data['active'],
        ];
    }

    /**
     * Process the translations data with consistent rules
     *
     * Uses TranslationHandler to:
     * - Apply auto-fill from default language when enabled
     * - Map title field to meta fields when enabled
     * - Filter out disabled locales
     *
     * @param  $translations
     * @return array
     * @throws Exception
     */
    private function handleTranslations($translations): array
    {
        if (empty($translations)) {
            return [];
        }

        // Define field mapping for title to TDK fields
        $fieldMap = [
            'title' => ['meta_title', 'meta_description', 'meta_keywords'],
        ];

        // Process translations using TranslationHandler
        return TranslationHandler::process($translations, $fieldMap);
    }

    /**
     * Get hierarchical catalogs with breadcrumb-style display
     *
     * @param  array  $filters
     * @return array
     */
    public function getHierarchicalCatalogs(array $filters = []): array
    {
        $catalogs     = $this->all($filters);
        $hierarchical = [];

        // Build hierarchy starting from root catalogs (parent_id = 0 or null)
        $rootCatalogs = $catalogs->filter(function ($catalog) {
            return empty($catalog->parent_id);
        })->sortBy('position');

        foreach ($rootCatalogs as $catalog) {
            $this->buildHierarchy($catalog, $catalogs, $hierarchical, '', 0);
        }

        return $hierarchical;
    }

    /**
     * Recursively build hierarchy with breadcrumb paths
     *
     * @param  $catalog
     * @param  $allCatalogs
     * @param  &$result
     * @param  string  $breadcrumb
     * @param  int  $level
     * @return void
     */
    private function buildHierarchy($catalog, $allCatalogs, &$result, string $breadcrumb = '', int $level = 0): void
    {
        // Build breadcrumb path
        $currentBreadcrumb = $breadcrumb ? $breadcrumb.' > '.$catalog->translation->title : $catalog->translation->title;

        $result[] = [
            'id'        => $catalog->id,
            'title'     => $currentBreadcrumb,
            'level'     => $level,
            'parent_id' => $catalog->parent_id,
        ];

        // Find and process children
        $children = $allCatalogs->filter(function ($item) use ($catalog) {
            return $item->parent_id == $catalog->id;
        })->sortBy('position');

        foreach ($children as $child) {
            $this->buildHierarchy($child, $allCatalogs, $result, $currentBreadcrumb, $level + 1);
        }
    }

    /**
     * @param  $id
     * @return string
     */
    public function getNameByID($id): string
    {
        return Catalog::query()->find($id)->description->name ?? '';
    }
}
