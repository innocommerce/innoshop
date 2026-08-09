<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\ProductRepo;

class ProductListTool extends BaseTool
{
    public function name(): string
    {
        return 'product_list';
    }

    public function description(): string
    {
        return 'List store products with pagination. Supports keyword search on product name.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword'  => ['type' => 'string', 'description' => 'Search keyword matched against product name'],
                'active'   => ['type' => 'boolean', 'description' => 'Filter by active status; omit to include both active and inactive'],
                'page'     => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page' => ['type' => 'integer', 'description' => 'Items per page, default 10, max 50'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'products_index';
    }

    public function execute(array $arguments): mixed
    {
        $filters = [
            'keyword'  => $arguments['keyword'] ?? '',
            'page'     => max(1, (int) ($arguments['page'] ?? 1)),
            'per_page' => min(50, max(1, (int) ($arguments['per_page'] ?? 10))),
        ];
        if (array_key_exists('active', $arguments)) {
            $filters['active'] = (bool) $arguments['active'];
        }

        $paginator = ProductRepo::getInstance()->list($filters);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($product) => [
                'id'     => $product->id,
                'name'   => $product->translation->name ?? '',
                'url'    => $product->url,
                'price'  => $product->price,
                'skus'   => $product->skus->count(),
                'stock'  => $product->skus->sum('quantity'),
                'active' => (bool) $product->active,
            ])->values()->all(),
        ];
    }
}
