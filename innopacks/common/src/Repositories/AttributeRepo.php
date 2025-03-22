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
use InnoShop\Common\Handlers\TranslationHandler;
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
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('panel/common.created_at')],
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
     * @param  $name
     * @param  string  $locale
     * @return mixed
     * @throws Exception
     */
    public function findByName($name, string $locale = ''): mixed
    {
        if (empty($locale)) {
            $locale = locale_code();
        }

        $translation = Attribute\Translation::query()->where('name', $name)->where('locale', $locale)->first();

        return $translation->attribute ?? null;
    }

    /**
     * @param  $name
     * @param  string  $locale
     * @return mixed
     * @throws Throwable
     */
    public function findOrCreateByName($name, string $locale = ''): mixed
    {
        $attribute = $this->findByName($name, $locale);
        if ($attribute) {
            return $attribute;
        }

        $data = [];
        foreach (locales() as $locale) {
            $data['translations'][] = [
                'locale' => $locale->code,
                'name'   => $name,
            ];
        }

        return $this->create($data);
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

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $attribute = new Attribute;

        return $this->createOrUpdate($attribute, $data);
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     */
    public function update(mixed $item, $data): mixed
    {
        return $this->createOrUpdate($item, $data);
    }

    /**
     * @param  Attribute  $attribute
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    private function createOrUpdate(Attribute $attribute, $data): mixed
    {
        DB::beginTransaction();

        try {
            $attributeData = $this->handleData($data);
            $attribute->fill($attributeData);
            $attribute->saveOrFail();

            $translations = $this->handleTranslations($data['translations'] ?? []);
            if ($translations) {
                $attribute->translations()->delete();
                $attribute->translations()->createMany($translations);
            }

            DB::commit();

            return $attribute;
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

        // Define field mapping for name to other fields if needed
        $fieldMap = [
            'name' => [],
        ];

        // Process translations using TranslationHandler
        return TranslationHandler::process($translations, $fieldMap);
    }

    /**
     * Get attributes and their values in current language
     *
     * @param  string|null  $locale
     * @return array
     */
    public function getAttributesWithValues(?string $locale = null): array
    {
        if (! $locale) {
            $locale = locale_code();
        }

        // Get all attributes with their translations and values
        $attributes = Attribute::query()
            ->with([
                'translation' => function ($query) use ($locale) {
                    $query->where('locale', $locale);
                },
                'values.translation' => function ($query) use ($locale) {
                    $query->where('locale', $locale);
                },
            ])
            ->get();

        // Format data as three-dimensional array
        $result = [];
        foreach ($attributes as $attribute) {
            $attributeData = [
                'id'     => $attribute->id,
                'name'   => $attribute->translation->name ?? '',
                'values' => [],
            ];

            // Add attribute values
            foreach ($attribute->values as $value) {
                $attributeData['values'][] = [
                    'id'           => $value->id,
                    'attribute_id' => $attribute->id,
                    'name'         => $value->translation->name ?? '',
                ];
            }

            $result[] = $attributeData;
        }

        return $result;
    }
}
