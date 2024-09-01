<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewListItem extends JsonResource
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
        $customer = $this->customer;

        return [
            'id'              => $this->id,
            'customer_id'     => $customer->id,
            'product_id'      => $this->product_id,
            'order_item_id'   => $this->order_item_id,
            'customer_name'   => $customer->name,
            'customer_avatar' => image_resize($customer->avatar),
            'rating'          => $this->rating,
            'title'           => $this->title,
            'content'         => $this->content,
            'like'            => $this->like,
            'dislike'         => $this->dislike,
            'active'          => $this->active,
            'created_at'      => $this->created_at,
        ];
    }
}
