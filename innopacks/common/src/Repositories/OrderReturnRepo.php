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
            'quantity'      => $orderItem->quantity,
            'comment'       => $data['comment'] ?? '',
            'status'        => ReturnStateService::CREATED,
        ];
    }
}
