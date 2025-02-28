<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Repositories;

use Illuminate\Support\Str;
use InnoShop\Panel\Repositories\BaseRepo;
use Plugin\InquiryQuote\Models\InquiryQuote;

class QuoteFeeRepo extends BaseRepo
{
    const FEE_TYPES = [
        'subtotal', 'shipping', 'tax', 'handling',
    ];

    /**
     * @param  InquiryQuote  $quote
     * @param  array  $items
     * @return void
     */
    public function createFees(InquiryQuote $quote, array $items = []): void
    {
        $quote->fees()->delete();
        foreach (self::FEE_TYPES as $type) {
            if ($type == 'subtotal') {
                $this->createSubtotalFee($quote);

                continue;
            }

            $amount  = $items[$type] ?? 0;
            $feeItem = [
                'code'           => $type,
                'label'          => Str::studly($type),
                'origin_amount'  => $amount,
                'inquiry_amount' => $amount,
            ];
            $quote->fees()->create($feeItem);
        }
    }

    /**
     * @param  InquiryQuote  $quote
     * @return void
     */
    public function updateSubtotalFee(InquiryQuote $quote): void
    {
        $total    = $quote->items->sum('inquiry_subtotal');
        $subtotal = [
            'code'           => 'subtotal',
            'label'          => 'Subtotal',
            'origin_amount'  => $total,
            'inquiry_amount' => $total,
        ];
        $subtotalFee = $quote->fees()->where('code', 'subtotal')->first();
        if ($subtotalFee) {
            $subtotalFee->update($subtotal);
        } else {
            $quote->fees()->create($subtotal);
        }
    }

    /**
     * @param  InquiryQuote  $quote
     * @return void
     */
    private function createSubtotalFee(InquiryQuote $quote): void
    {
        $total    = $quote->items->sum('inquiry_subtotal');
        $subtotal = [
            'code'           => 'subtotal',
            'label'          => 'Subtotal',
            'origin_amount'  => $total,
            'inquiry_amount' => $total,
        ];
        $quote->fees()->create($subtotal);
    }
}
