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
use Plugin\FrequentQuestion\Models\Faq;
use Throwable;

class FaqRepo extends BaseRepo
{
    public static function getCriteria() {}

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $faq = new Faq($data);
        $faq->saveOrFail();

        $faq->translations()->createMany($data['translations']);

        return $faq;
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
        $item->translations()->delete();
        $item->translations()->createMany($data['translations']);

        return $item;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Faq::query();

        $faqCategoryID = $filters['faq_category_id'] ?? 0;
        if ($faqCategoryID) {
            $builder->where('faq_category_id', $faqCategoryID);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        return $builder;
    }
}
