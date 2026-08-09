<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Review;
use InvalidArgumentException;

class ReviewDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'review_detail';
    }

    public function description(): string
    {
        return 'Get full details of a single review by ID, including product and customer info.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Review ID'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'reviews_index';
    }

    public function execute(array $arguments): mixed
    {
        $review = Review::query()->with(['product.translation', 'customer', 'orderItem'])->find((int) ($arguments['id'] ?? 0));
        if (! $review) {
            throw new InvalidArgumentException("Review [{$arguments['id']}] not found.");
        }

        return [
            'id'            => $review->id,
            'product_id'    => $review->product_id,
            'product_name'  => $review->product->translation->name ?? '',
            'customer_id'   => $review->customer_id,
            'customer_name' => $review->customer->name ?? '',
            'order_item_id' => $review->order_item_id,
            'rating'        => $review->rating,
            'content'       => $review->content,
            'active'        => (bool) $review->active,
            'created_at'    => (string) $review->created_at,
            'updated_at'    => (string) $review->updated_at,
        ];
    }
}
