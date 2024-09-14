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
