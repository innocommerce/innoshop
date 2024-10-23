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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Attribute;
use Throwable;

class AttributeRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'keyword', 'type' => 'input', 'label' => trans('panel/common.name')],
            ['name'     => 'created_at', 'type' => 'date_range', 'label' => trans('panel/common.created_at'),
                'start' => ['name' => 'start'],
                'end'   => ['name' => 'end'],
            ],
        ];
    }

    /**
     * @param  array  $filters
     * @return Collection
     */
    public function all(array $filters = []): Collection
    {
        return $this->builder($filters)->get();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Attribute::query()->with([
            'translation',
            'translations',
            'values.translations',
            'group.translations',
        ]);

        $keyword = $filters['keyword'] ?? '';
        if ($keyword) {
            $builder->whereHas('translation', function (Builder $query) use ($keyword) {
                $query->where('name', 'like', "%$keyword%");
            });
        }

        $start = $filters['start'] ?? '';
        if ($start) {
            $builder->where('created_at', '>', $start);
        }

        $end = $filters['end'] ?? '';
        if ($end) {
            $builder->where('created_at', '<', $end);
        }

        return $builder;
    }

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
            $data      = $this->handleData($data);
            $attribute = new Attribute($data);
            $attribute->saveOrFail();

            $attribute->translations()->createMany($translations);
            DB::commit();

            return $attribute;
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param  mixed  $item
     * @throws Exception
     */
    public function destroy(mixed $item): void
    {
        DB::beginTransaction();

        try {
            if (is_int($item)) {
                $item = Attribute::query()->find($item);
            }
            if ($item) {
                foreach ($item->values as $value) {
                    $value->translations()->delete();
                }
                $item->values()->delete();
                $item->productAttributes()->delete();
                $item->translations()->delete();
                $item->delete();
            }
            DB::commit();
        } catch (Exception $e) {
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
            'category_id'        => $requestData['category_id']        ?? 0,
            'attribute_group_id' => $requestData['attribute_group_id'] ?? 0,
            'position'           => $requestData['position']           ?? 0,
        ];
    }
}
