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

class ReviewListTool extends BaseTool
{
    public function name(): string
    {
        return 'review_list';
    }

    public function description(): string
    {
        return 'List product reviews with pagination. Supports filtering by product ID, rating, and active status.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'product_id' => ['type' => 'integer', 'description' => 'Filter by product ID'],
                'rating'     => ['type' => 'integer', 'description' => 'Filter by rating (1-5)'],
                'active'     => ['type' => 'boolean', 'description' => 'Filter by active status; omit to include both'],
                'page'       => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page'   => ['type' => 'integer', 'description' => 'Items per page, default 10, max 50'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'reviews_index';
    }

    public function execute(array $arguments): mixed
    {
        $query = Review::query()->with(['product.translation', 'customer']);

        if ($productId = $arguments['product_id'] ?? 0) {
            $query->where('product_id', (int) $productId);
        }
        if ($rating = $arguments['rating'] ?? 0) {
            $query->where('rating', (int) $rating);
        }
        if (array_key_exists('active', $arguments)) {
            $query->where('active', (bool) $arguments['active']);
        }

        $page    = max(1, (int) ($arguments['page'] ?? 1));
        $perPage = min(50, max(1, (int) ($arguments['per_page'] ?? 10)));

        $paginator = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($review) => [
                'id'            => $review->id,
                'product_id'    => $review->product_id,
                'product_name'  => $review->product->translation->name ?? '',
                'customer_name' => $review->customer->name ?? '',
                'rating'        => $review->rating,
                'content'       => $review->content,
                'active'        => (bool) $review->active,
                'created_at'    => (string) $review->created_at,
            ])->values()->all(),
        ];
    }
}
