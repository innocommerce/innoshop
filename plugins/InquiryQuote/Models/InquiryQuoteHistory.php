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
use Plugin\InquiryQuote\Services\StateService;

class InquiryQuoteHistory extends BaseModel
{
    protected $table = 'inquiry_quote_histories';

    protected $fillable = [
        'inquiry_quote_id', 'status', 'notify', 'comment',
    ];

    /**
     * @return string
     * @throws \Exception
     */
    public function getStatusFormatAttribute(): string
    {
        $statusCode = $this->status;
        if ($statusCode == null) {
            return '';
        }

        $statusMap = array_column(StateService::getAllStatuses(), 'name', 'status');

        return $statusMap[$statusCode] ?? '';
    }
}
