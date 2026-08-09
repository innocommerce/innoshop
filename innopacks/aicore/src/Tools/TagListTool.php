<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\TagRepo;

class TagListTool extends BaseTool
{
    public function name(): string
    {
        return 'tag_list';
    }

    public function description(): string
    {
        return 'List article tags with keyword search. Data volume is typically small, returned in full.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword'  => ['type' => 'string', 'description' => 'Search keyword matched against tag name'],
                'page'     => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page' => ['type' => 'integer', 'description' => 'Items per page, default 50, max 100'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'tags_index';
    }

    public function execute(array $arguments): mixed
    {
        $filters = [
            'page'     => max(1, (int) ($arguments['page'] ?? 1)),
            'per_page' => min(100, max(1, (int) ($arguments['per_page'] ?? 50))),
        ];

        if ($keyword = $arguments['keyword'] ?? '') {
            $filters['keyword'] = $keyword;
        }

        $paginator = TagRepo::getInstance()->list($filters);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($tag) => [
                'id'       => $tag->id,
                'name'     => $tag->name,
                'slug'     => $tag->slug,
                'position' => $tag->position,
            ])->values()->all(),
        ];
    }
}
