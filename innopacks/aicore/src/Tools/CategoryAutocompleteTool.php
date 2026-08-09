<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\CategoryRepo;

class CategoryAutocompleteTool extends BaseTool
{
    public function name(): string
    {
        return 'category_autocomplete';
    }

    public function description(): string
    {
        return 'Quick category search by name for autocomplete. Returns compact id+name+parent_id list, useful for finding category IDs.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword' => ['type' => 'string', 'description' => 'Search keyword matched against category name'],
                'limit'   => ['type' => 'integer', 'description' => 'Max results, default 10, max 25'],
            ],
            'required' => ['keyword'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'categories_index';
    }

    public function execute(array $arguments): mixed
    {
        $keyword = (string) ($arguments['keyword'] ?? '');
        $limit   = min(25, max(1, (int) ($arguments['limit'] ?? 10)));

        $categories = CategoryRepo::getInstance()->autocomplete($keyword, $limit);

        return [
            'items' => $categories->map(fn ($c) => [
                'id'        => $c->id,
                'name'      => $c->fallbackName(),
                'parent_id' => $c->parent_id,
            ])->values()->all(),
        ];
    }
}
