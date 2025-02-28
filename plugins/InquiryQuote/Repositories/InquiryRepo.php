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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use InnoShop\Common\Repositories\BaseRepo;
use InnoShop\Common\Repositories\Product\SkuRepo;
use Plugin\InquiryQuote\Models\InquiryQuote;
use Plugin\InquiryQuote\Models\InquiryQuoteItem;
use Plugin\InquiryQuote\Resources\InquiryListItem;
use Throwable;

class InquiryRepo extends BaseRepo
{
    protected string $model = InquiryQuoteItem::class;

    /**
     * @param  $data
     * @return mixed
     * @throws Exception
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $inquiryData = $this->handleData($data);

        $inquiryQuote = QuoteRepo::getInstance()->findOrCreate($data);

        $inquiryData['inquiry_quote_id'] = $inquiryQuote->id;

        $filters = [
            'inquiry_quote_id' => $inquiryQuote->id,
            'customer_id'      => $data['customer_id'],
            'sku_code'         => $data['sku_code'],
        ];
        $inquiry = $this->builder($filters)->first();
        if (empty($inquiry)) {
            $inquiry = new InquiryQuoteItem($inquiryData);
            $inquiry->saveOrFail();
        } else {
            $inquiry->update($inquiryData);
            $inquiry->increment('quantity', $inquiryData['quantity']);
        }

        return $inquiry;
    }

    /**
     * @param  InquiryQuote  $quote
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function createWithQuote(InquiryQuote $quote, $data): mixed
    {
        $inquiryData = $this->handleData($data);

        $inquiryData['inquiry_quote_id'] = $quote->id;

        $filters = [
            'inquiry_quote_id' => $quote->id,
            'customer_id'      => $data['customer_id'],
            'sku_code'         => $data['sku_code'],
        ];
        $inquiry = $this->builder($filters)->first();

        if (empty($inquiry)) {
            $inquiry = new InquiryQuoteItem($inquiryData);
        } else {
            $inquiry->fill($inquiryData);
        }
        $inquiry->saveOrFail();

        return $inquiry;
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     */
    public function update(mixed $item, $data): mixed
    {
        $inquiry = parent::update($item, $data);
        QuoteFeeRepo::getInstance()->updateSubtotalFee($item->quote);

        return $inquiry;
    }

    /**
     * @param  $filters
     * @return Builder
     */
    public function builder($filters = []): Builder
    {
        $builder = InquiryQuoteItem::query()->with([
            'product.translation',
            'productSku.image',
        ]);

        $inquiryQuoteID = $filters['inquiry_quote_id'] ?? 0;
        if ($inquiryQuoteID) {
            $builder->where('inquiry_quote_id', $inquiryQuoteID);
        }

        $customerID = $filters['customer_id'] ?? 0;
        if ($customerID) {
            $builder->where('customer_id', $customerID);
        }

        $skuCode = $filters['sku_code'] ?? '';
        if ($skuCode) {
            $builder->where('sku_code', $skuCode);
        }

        return $builder;
    }

    /**
     * @param  Collection  $inquiryItems
     * @return array
     */
    public function handleSellerInquiryList(Collection $inquiryItems): array
    {
        //$this->updateSellerID($inquiryItems);
        $inquiryList = InquiryListItem::collection($inquiryItems)->jsonSerialize();
        if (! seller_enabled()) {
            return $inquiryList;
        }

        $result = [];
        foreach ($inquiryList as $inquiry) {
            if (! isset($result[$inquiry['seller_id']])) {
                $result[$inquiry['seller_id']]['seller'] = $inquiry['seller'];
            }
            unset($inquiry['seller']);
            $result[$inquiry['seller_id']]['inquiries'][] = $inquiry;
        }

        return array_values($result);
    }

    /**
     * @param  $inquiryItems
     * @return void
     */
    private function updateSellerID($inquiryItems): void
    {
        foreach ($inquiryItems as $inquiryItem) {
            $inquiryItem->seller_id = $inquiryItem->product->seller_id ?? 0;
            $inquiryItem->save();
        }
    }

    /**
     * @param  $data
     * @return array
     * @throws Exception
     */
    private function handleData($data): array
    {
        $sku = SkuRepo::getInstance()->getSkuByCode($data['sku_code']);
        if (empty($sku)) {
            throw new Exception('Invalid sku code');
        }

        $product = $sku->product;
        if (empty($product)) {
            throw new Exception('Empty product');
        }

        return [
            'inquiry_quote_id' => 0,
            'customer_id'      => $data['customer_id'],
            'product_id'       => $product->id,
            'seller_id'        => $product->seller_id ?? 0,
            'sku_code'         => $data['sku_code'],
            'quantity'         => $data['quantity'],
            'origin_price'     => $sku->price,
            'inquiry_price'    => $data['inquiry_price'] ?? $sku->price,
        ];
    }
}
