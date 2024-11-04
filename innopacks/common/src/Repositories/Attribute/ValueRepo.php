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
use Illuminate\Support\Collection;
use InnoShop\Common\Models\Attribute\Value;
use InnoShop\Common\Repositories\BaseRepo;

class ValueRepo extends BaseRepo
{
    public string $model = Value::class;

    /**
     * @param  $attributeID
     * @param  $translations
     * @return void
     */
    public function createAttribute($attributeID, $translations): void
    {
        if (empty($attributeID) || empty($translations)) {
            return;
        }
        $attrValue = Value::query()->create(['attribute_id' => $attributeID]);
        $this->updateTranslations($attrValue, $translations);
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

        $translationList = [];
        foreach ($translations as $locale => $name) {
            $translationList[] = [
                'locale' => $locale,
                'name'   => $name,
            ];
        }

        if ($translationList) {
            $attrValue->translations()->delete();
            $attrValue->translations()->createMany($translationList);
        }
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
