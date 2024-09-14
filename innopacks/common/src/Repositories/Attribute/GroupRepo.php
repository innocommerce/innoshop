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
use InnoShop\Common\Models\Attribute\Group;
use InnoShop\Common\Repositories\BaseRepo;
use InnoShop\Common\Resources\AttributeGroupSimple;
use Throwable;

class GroupRepo extends BaseRepo
{
    public string $model = Group::class;

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $translations = array_values($data['translations'] ?? []);

        DB::beginTransaction();

        try {
            $data           = $this->handleData($data);
            $attributeGroup = new Group($data);
            $attributeGroup->saveOrFail();

            $attributeGroup->translations()->createMany($translations);
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
        $translations = array_values($data['translations'] ?? []);

        DB::beginTransaction();

        try {
            $data = $this->handleData($data);
            $item->update($data);

            if ($translations) {
                $item->translations()->delete();
                $item->saveOrFail();
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
        return Group::query()->with(['translation']);
    }
}
