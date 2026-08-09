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

class CategoryListTool extends BaseTool
{
    public function name(): string
    {
        return 'category_list';
    }

    public function description(): string
    {
        return 'List product categories with hierarchy (parent/children). Supports keyword search and active filter.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword'   => ['type' => 'string', 'description' => 'Search keyword matched against category name'],
                'active'    => ['type' => 'boolean', 'description' => 'Filter by active status; omit to include both'],
                'parent_id' => ['type' => 'integer', 'description' => 'Filter by parent category ID (0 for root only)'],
                'page'      => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page'  => ['type' => 'integer', 'description' => 'Items per page, default 20, max 100'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'categories_index';
    }

    public function execute(array $arguments): mixed
    {
        $filters = [
            'page'     => max(1, (int) ($arguments['page'] ?? 1)),
            'per_page' => min(100, max(1, (int) ($arguments['per_page'] ?? 20))),
        ];

        if ($keyword = $arguments['keyword'] ?? '') {
            $filters['keyword'] = $keyword;
        }
        if (array_key_exists('active', $arguments)) {
            $filters['active'] = (bool) $arguments['active'];
        }
        if (array_key_exists('parent_id', $arguments)) {
            $filters['parent_id'] = (int) $arguments['parent_id'];
        }

        $paginator = CategoryRepo::getInstance()->builder($filters)->paginate($filters['per_page']);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($category) => [
                'id'             => $category->id,
                'name'           => $category->fallbackName(),
                'slug'           => $category->slug,
                'parent_id'      => $category->parent_id,
                'position'       => $category->position,
                'active'         => (bool) $category->active,
                'children_count' => $category->children()->count(),
            ])->values()->all(),
        ];
    }
}
