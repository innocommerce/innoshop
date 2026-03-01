<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\Order\Item;
use InnoShop\Common\Models\Review;
use Throwable;

class ReviewRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'product', 'type' => 'input', 'label' => trans('panel/review.product')],
            ['name' => 'rating', 'type' => 'input', 'label' => trans('panel/review.rating')],
            ['name' => 'review_content', 'type' => 'input', 'label' => trans('panel/review.review_content')],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('panel/review.created_at')],
        ];
    }

    /**
     * Get search field options for data_search component
     *
     * @return array
     */
    public static function getSearchFieldOptions(): array
    {
        $options = [
            ['value' => '', 'label' => trans('panel/common.all_fields')],
            ['value' => 'content', 'label' => trans('panel/review.review_content')],
            ['value' => 'product', 'label' => trans('panel/review.product')],
        ];

        return fire_hook_filter('common.repo.review.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $ratingOptions = [
            ['value' => '', 'label' => trans('panel/common.all')],
        ];
        for ($i = 1; $i <= 5; $i++) {
            $ratingOptions[] = [
                'value' => (string) $i,
                'label' => $i.' '.trans('panel/review.star'),
            ];
        }

        $filters = [
            [
                'name'    => 'active',
                'label'   => trans('panel/common.status'),
                'type'    => 'button',
                'options' => [
                    ['value' => '', 'label' => trans('panel/common.all')],
                    ['value' => '1', 'label' => trans('panel/common.active_yes')],
                    ['value' => '0', 'label' => trans('panel/common.active_no')],
                ],
            ],
            [
                'name'    => 'rating',
                'label'   => trans('panel/review.rating'),
                'type'    => 'button',
                'options' => $ratingOptions,
            ],
        ];

        return fire_hook_filter('common.repo.review.filter_button_options', $filters);
    }

    /**
     * @param  $productID
     * @param  $customerID
     * @return bool
     */
    public static function productReviewed($customerID, $productID): bool
    {
        if (empty($customerID) || empty($productID)) {
            return false;
        }

        return Review::query()
            ->where('customer_id', $customerID)
            ->where('product_id', $productID)
            ->exists();
    }

    /**
     * @param  $orderItemID
     * @param  $customerID
     * @return bool
     */
    public static function orderReviewed($customerID, $orderItemID): bool
    {
        if (empty($customerID) || empty($orderItemID)) {
            return false;
        }

        return Review::query()
            ->where('customer_id', $customerID)
            ->where('order_item_id', $orderItemID)
            ->exists();
    }

    /**
     * @param  $product
     * @return LengthAwarePaginator
     */
    public function getListByProduct($product, $limit = 10, $page = 1): LengthAwarePaginator
    {
        if (is_object($product)) {
            $productID = $product->id;
        } else {
            $productID = (int) $product;
        }

        $filters = [
            'product_id' => $productID,
            'active'     => true,
        ];

        return $this->builder($filters)->paginate($limit, ['*'], 'page', $page);
    }

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
        $builder = Review::query()->with([
            'customer',
            'product',
            'orderItem',
        ]);

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

        $content = $filters['content'] ?? ($filters['review_content'] ?? '');
        if ($content) {
            $builder->where('content', 'like', "%$content%");
        }

        $rating = $filters['rating'] ?? '';
        if ($rating) {
            $builder->where('rating', $rating);
        }

        $product = $filters['product'] ?? '';
        if ($product) {
            $builder->whereHas('product.translation', function (Builder $query) use ($product) {
                $query->where('name', 'like', "%$product%");
            });
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->where('created_at', '>', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->where('created_at', '<', $createdEnd);
        }

        // Handle new search filters (keyword + search_field)
        $keyword     = $filters['keyword'] ?? '';
        $searchField = $filters['search_field'] ?? '';
        if ($keyword && $searchField) {
            if ($searchField === 'product') {
                $builder->whereHas('product.translation', function (Builder $query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%");
                });
            } else {
                $builder->where($searchField, 'like', "%{$keyword}%");
            }
        } elseif ($keyword) {
            // Search across all searchable fields
            $builder->where(function ($query) use ($keyword) {
                $query->where('content', 'like', "%{$keyword}%")
                    ->orWhereHas('product.translation', function (Builder $q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Handle date range filter
        $dateFilter = $filters['date_filter'] ?? '';
        $startDate  = $filters['start_date'] ?? '';
        $endDate    = $filters['end_date'] ?? '';

        if ($dateFilter === 'today') {
            $builder->whereDate('created_at', today());
        } elseif ($dateFilter === 'this_week') {
            $builder->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateFilter === 'this_month') {
            $builder->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($dateFilter === 'custom' && $startDate && $endDate) {
            $builder->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
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
            'product_id'    => $requestData['product_id'] ?? ($orderItem->product_id ?? 0),
            'order_item_id' => $orderItemID,
            'rating'        => $requestData['rating'] ?? 0,
            'content'       => $requestData['content'] ?? '',
            'like'          => 0,
            'dislike'       => 0,
            'active'        => true,
        ];
    }
}
