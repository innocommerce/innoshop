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
use InnoShop\Common\Models\Tag;
use Throwable;

class TagRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('panel/tag.name')],
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
            ['value' => 'name', 'label' => trans('panel/tag.name')],
            ['value' => 'slug', 'label' => trans('panel/common.slug')],
        ];

        return fire_hook_filter('common.repo.tag.search_field_options', $options);
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

        return fire_hook_filter('common.repo.tag.filter_button_options', $filters);
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
     * @param  $name
     * @return Builder[]|Collection
     */
    public function searchByName($name): Collection|array
    {
        $filters = [
            'name' => $name,
        ];

        return $this->builder($filters)->limit(10)->get();
    }

    /**
     * Get brand list by IDs.
     *
     * @param  mixed  $TagIDs
     * @return mixed
     */
    public function getListByTagIDs(mixed $TagIDs): mixed
    {
        if (empty($TagIDs)) {
            return [];
        }
        if (is_string($TagIDs)) {
            $TagIDs = explode(',', $TagIDs);
        }

        return Tag::query()->with('translations')
            ->whereIn('id', $TagIDs)
            ->orderByRaw('FIELD(id, '.implode(',', $TagIDs).')')
            ->get();
    }

    /**
     * Get query builder.
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Tag::query()->with(['translation']);

        $slug = $filters['slug'] ?? '';
        if ($slug) {
            $builder->where('slug', 'like', "%$slug%");
        }

        $tagIds = $filters['tag_ids'] ?? [];
        if ($tagIds) {
            $builder->whereIn('id', $tagIds);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->whereHas('translation', function ($query) use ($name) {
                $query->where('name', 'like', "%$name%");
            });
        }

        // Handle new search filters (keyword + search_field)
        $keyword     = $filters['keyword'] ?? '';
        $searchField = $filters['search_field'] ?? '';
        if ($keyword && $searchField) {
            if ($searchField === 'name') {
                $builder->whereHas('translation', function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                });
            } else {
                $builder->where($searchField, 'like', "%{$keyword}%");
            }
        } elseif ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('slug', 'like', "%{$keyword}%")
                    ->orWhereHas('translation', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        return fire_hook_filter('repo.tag.builder', $builder);
    }

    /**
     * @param  $data
     * @return Tag
     * @throws Exception|Throwable
     */
    public function create($data): Tag
    {
        $item = new Tag;

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
     * @throws Exception|Throwable
     */
    public function patch(Tag $tag, array $data): mixed
    {
        $tag->loadMissing(['translations']);

        $merged = [
            'slug'         => $tag->slug,
            'position'     => $tag->position,
            'active'       => $tag->active,
            'translations' => [],
        ];

        foreach ($tag->translations as $translation) {
            $merged['translations'][$translation->locale] = $translation->only($translation->getFillable());
        }

        foreach (['slug', 'position', 'active'] as $key) {
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

        return $this->update($tag, $merged);
    }

    /**
     * @param  Tag  $tag
     * @param  $data
     * @return mixed
     * @throws Exception|Throwable
     */
    private function createOrUpdate(Tag $tag, $data): mixed
    {
        DB::beginTransaction();

        try {
            $tagData = $this->handleData($data);
            $tag->fill($tagData);
            $tag->saveOrFail();

            $translations = $this->handleTranslations($data['translations'] ?? []);
            if ($translations) {
                $tag->translations()->delete();
                $tag->translations()->createMany($translations);
            }

            DB::commit();

            return $tag;
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
            'slug'     => $data['slug'] ?? null,
            'position' => $data['position'] ?? 0,
            'active'   => (bool) ($data['active'] ?? true),
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

        // Define field mapping for name to description field
        $fieldMap = [
            'name' => ['description'],
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
}
