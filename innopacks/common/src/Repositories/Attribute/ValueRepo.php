<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Attribute;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InnoShop\Common\Handlers\TranslationHandler;
use InnoShop\Common\Models\Attribute\Value;
use InnoShop\Common\Repositories\BaseRepo;

class ValueRepo extends BaseRepo
{
    public string $model = Value::class;

    /**
     * @param  $attributeID
     * @param  $translations
     * @return mixed
     */
    public function createAttribute($attributeID, $translations): mixed
    {
        if (empty($attributeID) || empty($translations)) {
            return null;
        }
        $attrValue = Value::query()->create(['attribute_id' => $attributeID]);
        $this->updateTranslations($attrValue, $translations);

        return $attrValue;
    }

    /**
     * @param  $attrValue
     * @param  $translations
     * @return void
     */
    public function updateTranslations($attrValue, $translations): void
    {
        if (is_int($attrValue)) {
            $attrValue = Value::query()->findOrFail($attrValue);
        }

        if (empty($attrValue) || empty($translations)) {
            return;
        }

        // Convert translations from key-value format to array format
        $translationArray = [];
        foreach ($translations as $locale => $name) {
            $translationArray[] = [
                'locale' => $locale,
                'name'   => $name,
            ];
        }

        // Process translations using TranslationHandler
        $processedTranslations = $this->handleTranslations($translationArray);

        if ($processedTranslations) {
            $attrValue->translations()->delete();
            $attrValue->translations()->createMany($processedTranslations);
        }
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

        // Define field mapping if needed
        $fieldMap = [
            'name' => [],
        ];

        // Process translations using TranslationHandler
        return TranslationHandler::process($translations, $fieldMap);
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

        $translation = Value\Translation::query()
            ->where('name', $name)
            ->where('locale', $locale)
            ->first();

        return $translation->value ?? null;
    }

    /**
     * @param  $attributeRow
     * @param  $name
     * @param  string  $locale
     * @return mixed
     * @throws Exception
     */
    public function findOrCreateByName($attributeRow, $name, string $locale = ''): mixed
    {
        $attributeValue = $this->findByName($name, $locale);
        if ($attributeValue) {
            return $attributeValue;
        }

        $data = [];
        foreach (locales() as $locale) {
            $data[$locale->code] = $name;
        }

        return $this->createAttribute($attributeRow->id, $data);
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder     = Value::query()->with(['translation']);
        $attributeID = $filters['attribute_id'] ?? 0;

        if ($attributeID) {
            $builder->where('attribute_id', $attributeID);
        }

        $keyword = $filters['keyword'] ?? '';
        if ($keyword) {
            $builder->whereHas('translation', function (Builder $query) use ($keyword) {
                $query->where('name', 'like', "%$keyword%");
            });
        }

        return $builder;
    }
}
