<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FrequentQuestion\Repositories;

use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Repositories\BaseRepo;
use Plugin\FrequentQuestion\Models\FaqCategory;
use Throwable;

class FaqCategoryRepo extends BaseRepo
{
    public static function getCriteria() {}

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $faqCategory = new FaqCategory($data);
        $faqCategory->saveOrFail();

        return $faqCategory;
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function update(mixed $item, $data): mixed
    {
        $item->update($data);

        return $item;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = FaqCategory::query();

        $productID = $filters['product_id'] ?? 0;
        if ($productID) {
            $builder->where('product_id', $productID);
        }

        $articleID = $filters['article_id'] ?? 0;
        if ($articleID) {
            $builder->where('article_id', $articleID);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        return $builder;
    }
}
