<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Services;

use Plugin\InquiryQuote\Models\InquiryQuote;
use Plugin\InquiryQuote\Repositories\QuoteFeeRepo;
use Plugin\InquiryQuote\Repositories\QuoteRepo;
use Throwable;

class SplittingService
{
    private InquiryQuote $quote;

    /**
     * @param  InquiryQuote  $quote
     */
    public function __construct(InquiryQuote $quote)
    {
        $this->quote = $quote;
    }

    /**
     * @param  InquiryQuote  $quote
     * @return self
     */
    public static function getInstance(InquiryQuote $quote): self
    {
        return new self($quote);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function split(): void
    {
        $quote = $this->quote;
        if ($quote->based != 'seller') {
            return;
        }

        $groupedQuoteItems = $quote->items->groupBy('seller_id');
        if ($groupedQuoteItems->count() == 1) {
            $quote->seller_id = $quote->items->first()->seller_id;
            $quote->save();

            return;
        }

        foreach ($groupedQuoteItems as $sellerID => $quoteItems) {
            $this->createQuoteFromParent($sellerID, $quoteItems);
        }

        $freshQuote = $quote->fresh();
        $freshQuote->syncTotal();
        QuoteFeeRepo::getInstance()->updateSubtotalFee($freshQuote);

        $quote->parent_id = $quote->id;
        $quote->save();
    }

    /**
     * @param  $sellerID
     * @param  $quoteItems
     * @return void
     * @throws Throwable
     */
    private function createQuoteFromParent($sellerID, $quoteItems): void
    {
        $firstQuoteItem = $quoteItems->first();
        $quoteData      = [
            'parent_id'            => $this->quote->id,
            'customer_id'          => $firstQuoteItem->customer_id,
            'admin_id'             => 0,
            'seller_id'            => $sellerID,
            'status'               => StateService::CUSTOMER_UPDATED,
            'based'                => 'seller',
            'shipping_address_id'  => $this->quote->shipping_address_id,
            'shipping_method_code' => $this->quote->shipping_method_code,
            'comment'              => $this->quote->comment,
        ];
        $newQuote = QuoteRepo::getInstance()->create($quoteData);
        $this->copyFeesFromParent($newQuote);

        foreach ($quoteItems as $quoteItem) {
            $newQuoteItem                   = $quoteItem->replicate();
            $newQuoteItem->inquiry_quote_id = $newQuote->id;
            $newQuoteItem->save();
        }

        $newQuote = $newQuote->fresh();
        QuoteFeeRepo::getInstance()->updateSubtotalFee($newQuote);
        $newQuote->syncTotal();
    }

    /**
     * @param  $newQuote
     * @return void
     */
    private function copyFeesFromParent($newQuote): void
    {
        $parentFees = $this->quote->fees;
        foreach ($parentFees as $fee) {
            $feeItem = [
                'code'           => $fee->code,
                'label'          => $fee->label,
                'origin_amount'  => $fee->origin_amount,
                'inquiry_amount' => $fee->inquiry_amount,
            ];
            $newQuote->fees()->create($feeItem);
        }
    }

    /**
     * @return void
     */
    private function clearParent(): void
    {
        $this->quote->fees()->delete();
        $this->quote->delete();
    }
}
