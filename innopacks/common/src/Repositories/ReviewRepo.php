<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\Order\Item;
use InnoShop\Common\Models\Review;
use Throwable;

class ReviewRepo extends BaseRepo
{
    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $data   = $this->handleData($data);
        $review = null;

        if ($data['customer_id'] && $data['order_item_id']) {
            $filters = [
                'customer_id'   => $data['customer_id'],
                'order_item_id' => $data['order_item_id'],
            ];
            $review = $this->builder($filters)->first();
        }

        if ($review) {
            return $review;
        }

        $review = new Review($data);
        $review->saveOrFail();

        return $review;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Review::query();

        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $productID = $filters['product_id'] ?? 0;
        if ($productID) {
            $builder->where('product_id', $productID);
        }

        $orderItemID = $filters['order_item_id'] ?? 0;
        if ($orderItemID) {
            $builder->where('order_item_id', $orderItemID);
        }

        return $builder;
    }

    /**
     * @param  $requestData
     * @return array
     */
    private function handleData($requestData): array
    {
        $orderItemID = $requestData['order_item_id'] ?? 0;
        if ($orderItemID) {
            $orderItem = Item::query()->findOrFail($orderItemID);
        }

        return [
            'customer_id'   => $requestData['customer_id'] ?? 0,
            'product_id'    => $requestData['product_id']  ?? ($orderItem->product_id ?? 0),
            'order_item_id' => $orderItemID,
            'rating'        => $requestData['rating']  ?? 0,
            'title'         => $requestData['title']   ?? '',
            'content'       => $requestData['content'] ?? '',
            'like'          => 0,
            'dislike'       => 0,
            'active'        => true,
        ];
    }
}
