<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use InnoShop\Common\Repositories\BaseRepo;
use InnoShop\Seller\Repositories\CartItemRepo;
use Plugin\InquiryQuote\Models\InquiryQuote;
use Plugin\InquiryQuote\Resources\QuoteFeeSimple;
use Plugin\InquiryQuote\Resources\QuoteSimple;
use Plugin\InquiryQuote\Services\StateService;
use Spatie\Permission\Models\Role;
use Throwable;

class QuoteRepo extends BaseRepo
{
    protected string $model = InquiryQuote::class;

    /**
     * @return array[]
     * @throws Exception
     */
    public static function getCriteria(): array
    {
        $statuses = StateService::getAllStatuses();

        return [
            ['name' => 'number', 'type' => 'input', 'label' => trans('panel/order.number')],
            ['name' => 'customer_name', 'type' => 'input', 'label' => trans('panel/order.customer_name')],
            ['name' => 'email', 'type' => 'input', 'label' => trans('panel/order.email')],
            ['name' => 'telephone', 'type' => 'input', 'label' => trans('panel/order.telephone')],
            ['name' => 'shipping_method_name', 'type' => 'input', 'label' => trans('panel/order.shipping_method_name')],
            ['name' => 'billing_method_name', 'type' => 'input', 'label' => trans('panel/order.billing_method_name')],
            ['name' => 'status', 'type' => 'select', 'label' => trans('panel/order.status'), 'options' => $statuses, 'options_key' => 'status', 'options_label' => 'name'],
            ['name'     => 'total', 'type' => 'range', 'label' => trans('panel/order.total'),
                'start' => ['name' => 'start'],
                'end'   => ['name' => 'end'],
            ],
            ['name'     => 'created_at', 'type' => 'date_range', 'label' => trans('panel/order.created_at'),
                'start' => ['name' => 'start'],
                'end'   => ['name' => 'end'],
            ],
        ];
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->orderByDesc('id')->paginate();
    }

    /**
     * @param  int  $customerID
     * @return mixed
     */
    public function getLatestByCustomerID(int $customerID): mixed
    {
        if (empty($customerID)) {
            return null;
        }

        $filters = [
            'customer_id' => $customerID,
            'status'      => StateService::CUSTOMER_CREATED,
        ];

        return $this->builder($filters)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Exception|Throwable
     */
    public function create($data): mixed
    {
        $customerID = $data['customer_id'];
        $quoteData  = $this->handleData($data);
        if (empty($customerID)) {
            throw new Exception('Invalid Customer ID');
        }

        $quote = new InquiryQuote($quoteData);
        $quote->saveOrFail();

        return $quote;
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Exception|Throwable
     */
    public function findOrCreate($data): mixed
    {
        $customerID = $data['customer_id'];
        $quoteData  = $this->handleData($data);
        if (empty($customerID)) {
            throw new Exception('Invalid Customer ID');
        }

        $quote = $this->getLatestByCustomerID($customerID);
        if (empty($quote)) {
            $quote = new InquiryQuote($quoteData);
        } else {
            $quote->fill($quoteData);
        }
        $quote->saveOrFail();

        QuoteFeeRepo::getInstance()->createFees($quote);

        return $quote;
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function update(mixed $item, $data): mixed
    {
        $based = $data['based'] ?? '';
        if (empty($based)) {
            throw new Exception('Empty Based, should be seller or salesman');
        } elseif (! in_array($based, ['seller', 'salesman'])) {
            throw new Exception('Based should be seller or salesman');
        }

        $quoteData = [
            'shipping_address_id'  => $data['shipping_address_id'],
            'shipping_method_code' => $data['shipping_method_code'],
            'based'                => $based,
            'comment'              => $data['comment'] ?? '',
        ];
        $item->update($quoteData);

        $inquiries = $data['inquiries'] ?? [];
        if ($inquiries) {
            foreach ($inquiries as $inquiryItem) {
                $inquiryItem['customer_id'] = $item->customer_id;
                InquiryRepo::getInstance()->createWithQuote($item, $inquiryItem);
            }
        }

        $fees = $data['fees'] ?? [];
        if ($fees) {
            QuoteRepo::getInstance()->updateFees($item, $fees);
        }

        QuoteFeeRepo::getInstance()->updateSubtotalFee($item);

        return $item;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = InquiryQuote::query();
        $builder->with('items');

        $number = $filters['number'] ?? '';
        if ($number) {
            $builder->where('number', $number);
        }

        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $status = $filters['status'] ?? '';
        if ($status) {
            $builder->where('status', $status);
        }

        if (is_admin()) {
            $salesRoleID  = plugin_setting('inquiry_quote', 'salesman_role_id');
            $salesRole    = Role::query()->find($salesRoleID);
            $currentAdmin = current_admin();
            if ($currentAdmin->hasRole($salesRole)) {
                $builder->where('admin_id', $currentAdmin->id);
            }
        }

        if (is_seller()) {
            $sellerID = current_seller()->id ?? 0;
            if ($sellerID) {
                $builder->where('seller_id', $sellerID);
            }
        }

        return $builder;
    }

    /**
     * @param  InquiryQuote  $quote
     * @param  $items
     * @return void
     * @throws Exception|Throwable
     */
    public function updateFees(InquiryQuote $quote, $items): void
    {
        if (empty($items)) {
            return;
        }

        QuoteFeeRepo::getInstance()->createFees($quote, $items);
        $this->updateAdminID($quote);
    }

    /**
     * @param  InquiryQuote  $quote
     * @return void
     * @throws Throwable
     */
    public function updateAdminID(InquiryQuote $quote): void
    {
        $quote->admin_id = $quote->customer->admin_id;
        $quote->saveOrFail();
    }

    /**
     * @param  InquiryQuote|null  $quote
     * @return array
     * @throws Exception
     */
    public function getDetails(?InquiryQuote $quote): array
    {
        if (empty($quote)) {
            return [];
        }

        if ($quote->fees()->count() == 0) {
            QuoteFeeRepo::getInstance()->createFees($quote);
        }

        $quote->syncTotal();
        $allInquiryItems = $quote->items()->get();
        $quantityTotal   = $allInquiryItems->sum('quantity');
        $inquirySubtotal = $allInquiryItems->sum('inquiry_subtotal');

        return [
            'quote'           => (new QuoteSimple($quote))->jsonSerialize(),
            'quantity'        => $quantityTotal,
            'quantity_format' => $quantityTotal <= 99 ? $quantityTotal : '99+',
            'inquiry_list'    => InquiryRepo::getInstance()->handleSellerInquiryList($allInquiryItems),
            'subtotal'        => $inquirySubtotal,
            'subtotal_format' => currency_format($inquirySubtotal),
            'quote_fees'      => (QuoteFeeSimple::collection($quote->fees))->jsonSerialize(),
        ];
    }

    /**
     * @param  InquiryQuote  $quote
     * @return void
     * @throws Throwable
     */
    public function addCart(InquiryQuote $quote): void
    {
        foreach ($quote->items as $quoteItem) {
            $cartItem = [
                'customer_id' => $quote->customer_id,
                'sku_id'      => $quoteItem->productSku->id,
                'quantity'    => $quoteItem->quantity,
            ];
            CartItemRepo::getInstance()->create($cartItem);
        }
    }

    /**
     * @param  $data
     * @return array
     * @throws Exception
     */
    private function handleData($data): array
    {
        $number = $data['number'] ?? '';
        if (empty($number)) {
            $number = $this->generateQuoteNumber();
        }

        $salesmanID = plugin_setting('inquiry_quote', 'salesman_admin_id');
        if (empty($salesmanID)) {
            throw new Exception('Empty salesman admin ID');
        }

        return [
            'parent_id'            => $data['parent_id'] ?? 0,
            'admin_id'             => $data['admin_id']  ?? $salesmanID,
            'seller_id'            => $data['seller_id'] ?? 0,
            'number'               => $number,
            'based'                => $data['based'] ?? '',
            'customer_id'          => $data['customer_id'],
            'shipping_address_id'  => $data['shipping_address_id']  ?? 0,
            'shipping_method_code' => $data['shipping_method_code'] ?? '',
            'total'                => 0,
            'status'               => $data['status'] ?? StateService::CUSTOMER_CREATED,
            'comment'              => '',
        ];
    }

    /**
     * Generate order number.
     *
     * @return string
     */
    private function generateQuoteNumber(): string
    {
        $number = date('Ymd').rand(10000, 99999);
        if (! $this->builder(['number' => $number])->exists()) {
            return $number;
        }

        return $this->generateQuoteNumber();
    }
}
