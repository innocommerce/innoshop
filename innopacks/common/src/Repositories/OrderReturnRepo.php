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
use InnoShop\Common\Models\Order;
use InnoShop\Common\Models\Order\Item;
use InnoShop\Common\Models\OrderReturn;
use InnoShop\Common\Services\ReturnStateService;
use Throwable;

class OrderReturnRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'number', 'type' => 'input', 'label' => trans('common/rma.return_number')],
            ['name' => 'order_number', 'type' => 'input', 'label' => trans('common/rma.order_reference')],
            ['name' => 'customer_name', 'type' => 'input', 'label' => trans('common/rma.customer_name')],
            ['name' => 'customer_email', 'type' => 'input', 'label' => trans('common/rma.customer_email')],
            ['name' => 'product_name', 'type' => 'input', 'label' => trans('common/rma.product_name')],
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
            ['value' => 'number', 'label' => trans('common/rma.return_number')],
            ['value' => 'order_number', 'label' => trans('common/rma.order_reference')],
            ['value' => 'product_name', 'label' => trans('common/rma.product_name')],
        ];

        return fire_hook_filter('common.repo.order_return.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $statuses      = ReturnStateService::getAllStatuses();
        $statusOptions = [
            ['value' => '', 'label' => trans('panel/common.all')],
        ];
        foreach ($statuses as $status) {
            $statusOptions[] = [
                'value' => $status['status'],
                'label' => $status['name'],
            ];
        }

        $filters = [
            [
                'name'    => 'status',
                'label'   => trans('front/return.status'),
                'type'    => 'button',
                'options' => $statusOptions,
            ],
        ];

        return fire_hook_filter('common.repo.order_return.filter_button_options', $filters);
    }

    protected string $model = OrderReturn::class;

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $returnData = $this->handleData($data);
        $returnItem = new OrderReturn($returnData);
        $returnItem->saveOrFail();

        return $returnItem;
    }

    /**
     * @param  $filters
     * @return Builder
     */
    public function builder($filters = []): Builder
    {
        $builder = OrderReturn::query();
        $number  = $filters['number'] ?? 0;
        if ($number) {
            $builder->where('number', 'like', "%$number%");
        }

        $orderNumber = $filters['order_number'] ?? '';
        if ($orderNumber) {
            $builder->where('order_number', 'like', "%$orderNumber%");
        }

        $productName = $filters['product_name'] ?? '';
        if ($productName) {
            $builder->where('product_name', 'like', "%$productName%");
        }

        $customerName = $filters['customer_name'] ?? '';
        if ($customerName) {
            $builder->whereHas('customer', function ($query) use ($customerName) {
                $query->where('name', 'like', "%$customerName%");
            });
        }

        $customerEmail = $filters['customer_email'] ?? '';
        if ($customerEmail) {
            $builder->whereHas('customer', function ($query) use ($customerEmail) {
                $query->where('email', 'like', "%$customerEmail%");
            });
        }

        $status = $filters['status'] ?? '';
        if ($status) {
            $builder->where('status', $status);
        }

        // Handle new search filters (keyword + search_field)
        $keyword     = $filters['keyword'] ?? '';
        $searchField = $filters['search_field'] ?? '';
        if ($keyword && $searchField) {
            $builder->where($searchField, 'like', "%{$keyword}%");
        } elseif ($keyword) {
            // Search across all searchable fields
            $builder->where(function ($query) use ($keyword) {
                $query->where('number', 'like', "%{$keyword}%")
                    ->orWhere('order_number', 'like', "%{$keyword}%")
                    ->orWhere('product_name', 'like', "%{$keyword}%");
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

        $builder = fire_hook_filter('repo.order_return.builder', $builder);

        return $builder;
    }

    /**
     * Generate order return number.
     *
     * @return string
     */
    private function generateReturnNumber(): string
    {
        $number = 'RMA-'.date('Ymd').rand(10000, 99999);
        if (! $this->builder(['number' => $number])->exists()) {
            return $number;
        }

        return $this->generateReturnNumber();
    }

    /**
     * @param  $data
     * @return array
     */
    private function handleData($data): array
    {
        $orderItemID = $data['order_item_id'];
        $orderItem   = Item::query()->findOrFail($orderItemID);
        $originOrder = $orderItem->order;

        return [
            'customer_id'   => $data['customer_id'],
            'order_id'      => $orderItem->order_id,
            'order_item_id' => $orderItemID,
            'product_id'    => $orderItem->product_id,
            'number'        => $this->generateReturnNumber(),
            'order_number'  => $originOrder->number,
            'product_name'  => $orderItem->name,
            'product_sku'   => $orderItem->product_sku,
            'opened'        => $data['opened'] ?? true,
            'quantity'      => $data['quantity'] ?? 1,
            'comment'       => $data['comment'] ?? '',
            'status'        => ReturnStateService::CREATED,
        ];
    }
}
