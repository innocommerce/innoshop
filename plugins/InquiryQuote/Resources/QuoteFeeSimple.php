<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteFeeSimple extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @throws \Exception
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'code'                  => $this->code,
            'label'                 => $this->label,
            'origin_amount'         => $this->origin_amount,
            'origin_amount_format'  => currency_format($this->origin_amount),
            'inquiry_amount'        => $this->inquiry_amount,
            'inquiry_amount_format' => currency_format($this->inquiry_amount),
        ];
    }
}
