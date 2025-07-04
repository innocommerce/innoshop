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
use Illuminate\Pagination\LengthAwarePaginator;
use InnoShop\Common\Models\Customer\Transaction;
use InnoShop\Common\Models\Customer\Withdrawal;
use InnoShop\Common\Repositories\BaseRepo;

class WithdrawalRepo extends BaseRepo
{
    protected string $model = Withdrawal::class;

    /**
     * Get search criteria for withdrawal listing.
     *
     * @return array[]
     */
    public static function getCriteria(): array
    {
        $statusOptions = [];
        foreach (Withdrawal::STATUSES as $status) {
            $statusOptions[] = [
                'code'  => $status,
                'label' => trans('common/withdrawal.'.$status),
            ];
        }

        $accountTypeOptions = [];
        foreach (Withdrawal::ACCOUNT_TYPES as $type) {
            $accountTypeOptions[] = [
                'code'  => $type,
                'label' => trans('common/withdrawal.'.$type),
            ];
        }

        return [
            ['name' => 'customer_id', 'type' => 'input', 'label' => trans('panel/withdrawal.customer_id')],
            ['name' => 'status', 'type' => 'select', 'label' => trans('panel/withdrawal.status'), 'options' => $statusOptions, 'options_key' => 'code', 'options_label' => 'label'],
            ['name' => 'account_type', 'type' => 'select', 'label' => trans('panel/withdrawal.account_type'), 'options' => $accountTypeOptions, 'options_key' => 'code', 'options_label' => 'label'],
            ['name' => 'amount', 'type' => 'range', 'label' => trans('panel/withdrawal.amount')],
            ['name' => 'created_at', 'type' => 'date_range', 'label' => trans('panel/common.created_at')],
        ];
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)
            ->orderByDesc('id')
            ->paginate(10);
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Withdrawal::query()->with(['customer']);

        $customer_id = $filters['customer_id'] ?? null;
        if ($customer_id) {
            $builder->where('customer_id', $customer_id);
        }

        $status = $filters['status'] ?? null;
        if ($status) {
            $builder->where('status', $status);
        }

        $account_type = $filters['account_type'] ?? null;
        if ($account_type) {
            $builder->where('account_type', $account_type);
        }

        $amount_start = $filters['amount_start'] ?? null;
        if ($amount_start) {
            $builder->where('amount', '>=', $amount_start);
        }

        $amount_end = $filters['amount_end'] ?? null;
        if ($amount_end) {
            $builder->where('amount', '<=', $amount_end);
        }

        $created_at_start = $filters['created_at_start'] ?? null;
        if ($created_at_start) {
            $builder->where('created_at', '>=', $created_at_start.' 00:00:00');
        }

        $created_at_end = $filters['created_at_end'] ?? null;
        if ($created_at_end) {
            $builder->where('created_at', '<=', $created_at_end.' 23:59:59');
        }

        // Keep backward compatibility
        $start_date = $filters['start_date'] ?? null;
        if ($start_date) {
            $builder->where('created_at', '>=', $start_date.' 00:00:00');
        }

        $end_date = $filters['end_date'] ?? null;
        if ($end_date) {
            $builder->where('created_at', '<=', $end_date.' 23:59:59');
        }

        return $builder;
    }

    /**
     * Get customer withdrawal requests
     *
     * @param  int  $customerId
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function getByCustomer(int $customerId, array $filters = []): LengthAwarePaginator
    {
        $filters['customer_id'] = $customerId;

        return $this->list($filters);
    }

    /**
     * Check if customer has pending withdrawal requests
     *
     * @param  int  $customerId
     * @return bool
     */
    public function hasPendingWithdrawal(int $customerId): bool
    {
        return $this->model::query()
            ->where('customer_id', $customerId)
            ->where('status', Withdrawal::STATUS_PENDING)
            ->exists();
    }

    /**
     * Get customer's frozen amount (total amount of pending/approved withdrawal requests)
     *
     * @param  int  $customerId
     * @return float
     */
    public function getFrozenAmount(int $customerId): float
    {
        return (float) $this->model::query()
            ->where('customer_id', $customerId)
            ->whereIn('status', [Withdrawal::STATUS_PENDING, Withdrawal::STATUS_APPROVED])
            ->sum('amount');
    }

    /**
     * Update withdrawal request and handle status change business logic
     *
     * @param  mixed  $item
     * @param  array  $data
     * @return mixed
     * @throws \Throwable
     */
    public function update($item, $data): mixed
    {
        $oldStatus = $item->status;
        $newStatus = $data['status'] ?? $oldStatus;

        // Update withdrawal record
        $item->update($data);

        // If status changes from non-paid to paid, create deduction transaction record
        if ($oldStatus !== Withdrawal::STATUS_PAID && $newStatus === Withdrawal::STATUS_PAID) {
            $this->createWithdrawTransaction($item);
        }

        return $item;
    }

    /**
     * Create withdrawal deduction transaction record
     *
     * @param  Withdrawal  $withdrawal
     * @return void
     * @throws \Throwable
     */
    private function createWithdrawTransaction(Withdrawal $withdrawal): void
    {
        $transactionData = [
            'customer_id' => $withdrawal->customer_id,
            'amount'      => -abs($withdrawal->amount), // Negative amount to deduct balance
            'type'        => Transaction::TYPE_WITHDRAW,
            'comment'     => "Withdrawal successful - Request ID: {$withdrawal->id}",
        ];

        // Create transaction record, TransactionRepo will automatically sync user balance
        \InnoShop\Common\Repositories\Customer\TransactionRepo::getInstance()->create($transactionData);
    }
}
