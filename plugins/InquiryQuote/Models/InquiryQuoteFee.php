<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Models;

use InnoShop\Common\Models\BaseModel;

class InquiryQuoteFee extends BaseModel
{
    protected $table = 'inquiry_quote_fees';

    protected $fillable = [
        'inquiry_quote_id', 'code', 'label', 'origin_amount', 'inquiry_amount',
    ];
}
