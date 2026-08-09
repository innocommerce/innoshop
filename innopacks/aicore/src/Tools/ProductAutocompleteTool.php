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

class ProductAutocompleteTool extends BaseTool
{
    public function name(): string
    {
        return 'product_autocomplete';
    }

    public function description(): string
    {
        return 'Quick product name search for autocomplete. Returns compact id+name list, useful for finding product IDs before calling product_detail.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword' => ['type' => 'string', 'description' => 'Search keyword matched against product name'],
                'limit'   => ['type' => 'integer', 'description' => 'Max results, default 10, max 25'],
            ],
            'required' => ['keyword'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'products_index';
    }

    public function execute(array $arguments): mixed
    {
        $keyword = (string) ($arguments['keyword'] ?? '');
        $limit   = min(25, max(1, (int) ($arguments['limit'] ?? 10)));

        $products = ProductRepo::getInstance()->autocomplete($keyword, $limit);

        return [
            'items' => $products->map(fn ($p) => [
                'id'    => $p->id,
                'name'  => $p->translation->name ?? '',
                'price' => $p->price,
                'image' => $p->image,
            ])->values()->all(),
        ];
    }
}
