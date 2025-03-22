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
            'slug'     => $data['slug']     ?? null,
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
