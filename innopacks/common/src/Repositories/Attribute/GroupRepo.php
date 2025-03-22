<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Attribute;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Handlers\TranslationHandler;
use InnoShop\Common\Models\Attribute\Group;
use InnoShop\Common\Repositories\BaseRepo;
use InnoShop\Common\Resources\AttributeGroupSimple;
use Throwable;

class GroupRepo extends BaseRepo
{
    public string $model = Group::class;

    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'keyword', 'type' => 'input', 'label' => trans('panel/common.name')],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('panel/common.created_at')],
        ];
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        DB::beginTransaction();

        try {
            $catalogData    = $this->handleData($data);
            $attributeGroup = new Group;
            $attributeGroup->fill($catalogData);
            $attributeGroup->saveOrFail();

            $translations = $this->handleTranslations($data['translations'] ?? []);
            if ($translations) {
                $attributeGroup->translations()->createMany($translations);
            }

            DB::commit();

            return $attributeGroup;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     */
    public function update(mixed $item, $data): mixed
    {
        DB::beginTransaction();

        try {
            $groupData = $this->handleData($data);
            $item->fill($groupData);
            $item->saveOrFail();

            $translations = $this->handleTranslations($data['translations'] ?? []);
            if ($translations) {
                $item->translations()->delete();
                $item->translations()->createMany($translations);
            }

            DB::commit();

            return $item;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param  $requestData
     * @return array
     */
    private function handleData($requestData): array
    {
        return [
            'position' => $requestData['position'] ?? 0,
        ];
    }

    /**
     * Process translations with TranslationHandler
     *
     * @param  array  $translations
     * @return array
     */
    private function handleTranslations(array $translations): array
    {
        if (empty($translations)) {
            return [];
        }

        // Define field mapping for name to description field if needed
        $fieldMap = [
            'name' => [],
        ];

        // Process translations using TranslationHandler
        return TranslationHandler::process($translations, $fieldMap);
    }

    /**
     * @param  array  $filters
     * @return array
     */
    public function getOptions(array $filters = []): array
    {
        return AttributeGroupSimple::collection($this->builder($filters)->get())->jsonSerialize();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Group::query()->with([
            'translation',
            'translations',
        ]);

        $keyword = $filters['keyword'] ?? '';
        if ($keyword) {
            $builder->whereHas('translation', function (Builder $query) use ($keyword) {
                $query->where('name', 'like', "%$keyword%");
            });
        }

        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->where('created_at', '>', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->where('created_at', '<', $createdEnd);
        }

        return $builder;
    }
}
