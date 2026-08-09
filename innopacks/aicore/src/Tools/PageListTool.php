<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\PageRepo;

class PageListTool extends BaseTool
{
    public function name(): string
    {
        return 'page_list';
    }

    public function description(): string
    {
        return 'List static pages (e.g. about-us, privacy-policy) with keyword search and active filter.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword'  => ['type' => 'string', 'description' => 'Search keyword matched against page title'],
                'active'   => ['type' => 'boolean', 'description' => 'Filter by active status'],
                'page'     => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page' => ['type' => 'integer', 'description' => 'Items per page, default 20, max 100'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'pages_index';
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

        $paginator = PageRepo::getInstance()->list($filters);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($page) => [
                'id'     => $page->id,
                'title'  => $page->translation->title ?? '',
                'slug'   => $page->slug,
                'active' => (bool) $page->active,
                'viewed' => $page->viewed,
            ])->values()->all(),
        ];
    }
}
