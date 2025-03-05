<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ReviewImporter\Repositories;

use InnoShop\Common\Models\Review;
use InnoShop\Panel\Repositories\BaseRepo;

class ReviewRepo extends BaseRepo
{
    /**
     * @param  $items
     * @return void
     */
    public function import($items): void
    {
        if (empty($items)) {
            return;
        }

        $reviews = [];
        foreach ($items as $item) {
            $reviews[] = [
                'customer_id'   => $item['customer_id'],
                'product_id'    => $item['product_id'],
                'order_item_id' => (int) $item['order_item_id'] ?? 0,
                'rating'        => $item['rating'],
                'content'       => $item['content'],
                'like'          => (bool) ($item['like'] ?? rand(50, 500)),
                'dislike'       => (bool) ($item['dislike'] ?? rand(10, 50)),
                'active'        => (bool) $item['active'],
                'created_at'    => $item['created_at'] ?? now(),
                'updated_at'    => $item['updated_at'] ?? now(),
            ];
        }
        Review::query()->insert($reviews);
    }
}
