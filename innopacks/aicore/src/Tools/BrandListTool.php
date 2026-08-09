<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\BrandRepo;

class BrandListTool extends BaseTool
{
    public function name(): string
    {
        return 'brand_list';
    }

    public function description(): string
    {
        return 'List product brands with pagination. Supports keyword search on brand name.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword'  => ['type' => 'string', 'description' => 'Search keyword matched against brand name'],
                'active'   => ['type' => 'boolean', 'description' => 'Filter by active status; omit to include both'],
                'page'     => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page' => ['type' => 'integer', 'description' => 'Items per page, default 20, max 100'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'brands_index';
    }

    public function execute(array $arguments): mixed
    {
        $repo    = BrandRepo::getInstance();
        $page    = max(1, (int) ($arguments['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($arguments['per_page'] ?? 20)));

        $filters = ['per_page' => $perPage, 'page' => $page];
        if ($keyword = $arguments['keyword'] ?? '') {
            $filters['keyword'] = $keyword;
        }
        if (isset($arguments['active'])) {
            $filters['active'] = (bool) $arguments['active'];
        }

        $builder   = $repo->builder($filters);
        $paginator = $builder->paginate($perPage, ['*'], 'page', $page);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($brand) => [
                'id'     => $brand->id,
                'name'   => $brand->name,
                'slug'   => $brand->slug,
                'logo'   => $brand->logo ?? '',
                'active' => (bool) $brand->active,
            ])->values()->all(),
        ];
    }
}
