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
use Illuminate\Support\Facades\DB;
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
            ['name' => 'email', 'type' => 'input', 'label' => trans('common/base.email')],
            ['name' => 'type', 'type' => 'select', 'label' => trans('panel/transaction.type'), 'options' => $options, 'options_key' => 'code', 'options_label' => 'label'],
            ['name' => 'amount', 'type' => 'range', 'label' => trans('panel/transaction.amount')],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('common/base.created_at')],
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
            ['value' => 'email', 'label' => trans('common/base.email')],
            ['value' => 'comment', 'label' => trans('panel/transaction.comment')],
        ];

        return fire_hook_filter('common.repo.transaction.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $typeOptions = [
            ['value' => '', 'label' => trans('panel/common.all')],
        ];
        foreach (Transaction::TYPES as $type) {
            $typeOptions[] = [
                'value' => $type,
                'label' => trans('common/transaction.'.$type),
            ];
        }

        $filters = [
            [
                'name'    => 'type',
                'label'   => trans('panel/transaction.type'),
                'type'    => 'button',
                'options' => $typeOptions,
            ],
        ];

        return fire_hook_filter('common.repo.transaction.filter_button_options', $filters);
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
        // The customer row lock serializes concurrent ledger writes: the
        // balance check, the insert and the re-sync must commit atomically,
        // otherwise parallel redemptions can drive the wallet below zero.
        return DB::transaction(function () use ($data) {
            $customer = Customer::query()->whereKey($data['customer_id'] ?? 0)->lockForUpdate()->firstOrFail();

            $data = $this->handleData($data, $customer);
            $item = Transaction::query()->create($data);

            $customer->syncBalance();

            return $item;
        });
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function update($item, $data): mixed
    {
        return DB::transaction(function () use ($item, $data) {
            $customer = Customer::query()->whereKey($data['customer_id'] ?? 0)->lockForUpdate()->firstOrFail();

            $data = $this->handleData($data, $customer);
            $item->update($data);

            $customer->syncBalance();

            return $item;
        });
    }

    /**
     * @param  $item
     * @return void
     */
    public function destroy($item): void
    {
        DB::transaction(function () use ($item) {
            $customer = Customer::query()->whereKey($item->customer_id)->lockForUpdate()->firstOrFail();
            $item->delete();
            $customer->syncBalance();
        });
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

        $email = $filters['email'] ?? '';
        if ($email) {
            $builder->whereHas('customer', function ($query) use ($email) {
                $query->where('email', 'like', '%'.$email.'%');
            });
        }

        $type = $filters['type'] ?? '';
        if ($type) {
            $builder->where('type', $type);
        }

        $amountStart = $filters['amount_start'] ?? '';
        if ($amountStart) {
            $builder->where('amount', '>', $amountStart);
        }

        $amountEnd = $filters['amount_end'] ?? '';
        if ($amountEnd) {
            $builder->where('amount', '<', $amountEnd);
        }

        $createdStart = $filters['created_at_start'] ?? '';
        if ($createdStart) {
            $builder->where('created_at', '>', $createdStart);
        }

        $createdEnd = $filters['created_at_end'] ?? '';
        if ($createdEnd) {
            $builder->where('created_at', '<', $createdEnd);
        }

        $builder = fire_hook_filter('repo.transaction.builder', $builder);

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
     * @throws Throwable
     */
    private function handleData($data, Customer $customer): array
    {
        $type = $data['type'];
        if (in_array($type, [Transaction::TYPE_RECHARGE, Transaction::TYPE_REFUND])) {
            $amount = abs($data['amount']);
        } elseif (in_array($type, [Transaction::TYPE_WITHDRAW, Transaction::TYPE_CONSUMPTION])) {
            $amount = abs($data['amount']) * -1;
        } else {
            $amount = $data['amount'];
        }

        // Caller holds a lockForUpdate() on this row; recompute from the
        // ledger so the guard sees every committed transaction.
        $customer->syncBalance();
        $balance = $customer->balance + $amount;

        // Defense in depth: never persist a ledger entry that drives the
        // wallet below zero (blocks forged checkout balance redemptions).
        if ($balance < 0) {
            throw new \Exception(trans('common/transaction.insufficient_balance'));
        }

        return [
            'customer_id' => $data['customer_id'],
            'amount'      => $amount,
            'type'        => $type,
            'comment'     => $data['comment'] ?? '',
            'balance'     => $balance,
        ];
    }
}
