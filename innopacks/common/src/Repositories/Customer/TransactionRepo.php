<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Customer;

use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Customer\Transaction;
use InnoShop\Common\Repositories\BaseRepo;
use Throwable;

class TransactionRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        $options = self::getTypeOptions();

        return [
            ['name' => 'customer_id', 'type' => 'input', 'label' => trans('panel/transaction.customer')],
            ['name' => 'type', 'type' => 'select', 'label' => trans('panel/transaction.type'), 'options' => $options, 'options_key' => 'code', 'options_label' => 'label'],
            ['name'     => 'amount', 'type' => 'range', 'label' => trans('panel/transaction.amount'),
                'start' => ['name' => 'start'],
                'end'   => ['name' => 'end'],
            ],
            ['name'     => 'created_at', 'type' => 'date_range', 'label' => trans('panel/common.created_at'),
                'start' => ['name' => 'start'],
                'end'   => ['name' => 'end'],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function getTypeOptions(): array
    {
        $options = [];
        foreach (Transaction::TYPES as $type) {
            $options[] = [
                'code'  => $type,
                'label' => trans('common/transaction.'.$type),
            ];
        }

        return $options;
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $customerID = $data['customer_id'] ?? 0;
        $customer   = Customer::query()->findOrFail($customerID);

        $data = $this->handleData($data);
        $item = Transaction::query()->create($data);

        $customer->syncBalance();

        return $item;
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function update($item, $data): mixed
    {
        $customerID = $data['customer_id'] ?? 0;
        $customer   = Customer::query()->findOrFail($customerID);

        $data = $this->handleData($data);
        $item->update($data);

        $customer->syncBalance();

        return $item;
    }

    /**
     * @param  $item
     * @return void
     */
    public function destroy($item): void
    {
        $customer = $item->customer;
        $item->delete();
        $customer->syncBalance();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder    = Transaction::query();
        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $type = $filters['type'] ?? '';
        if ($type) {
            $builder->where('type', $type);
        }

        return $builder;
    }

    /**
     * @param  $type
     * @param  int  $customerID
     * @return mixed
     */
    public function getAmountByType($type, int $customerID = 0): mixed
    {
        $filters = [
            'type'        => $type,
            'customer_id' => $customerID,
        ];

        return $this->builder($filters)->sum('amount');
    }

    /**
     * @param  $data
     * @return array
     */
    private function handleData($data): array
    {
        $type = $data['type'];
        if (in_array($type, [Transaction::TYPE_RECHARGE, Transaction::TYPE_REFUND])) {
            $amount = abs($data['amount']);
        } elseif (in_array($type, [Transaction::TYPE_WITHDRAW, Transaction::TYPE_CONSUMPTION])) {
            $amount = abs($data['amount']) * -1;
        } else {
            $amount = $data['amount'];
        }

        return [
            'customer_id' => $data['customer_id'],
            'amount'      => $amount,
            'type'        => $type,
            'comment'     => $data['comment'] ?? '',
        ];
    }
}
