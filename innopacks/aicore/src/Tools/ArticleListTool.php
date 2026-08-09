<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\ArticleRepo;

class ArticleListTool extends BaseTool
{
    public function name(): string
    {
        return 'article_list';
    }

    public function description(): string
    {
        return 'List blog articles with pagination. Supports keyword search and catalog filter.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword'    => ['type' => 'string', 'description' => 'Search keyword matched against article title or content'],
                'catalog_id' => ['type' => 'integer', 'description' => 'Filter by catalog (category) ID'],
                'active'     => ['type' => 'boolean', 'description' => 'Filter by active status'],
                'page'       => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page'   => ['type' => 'integer', 'description' => 'Items per page, default 10, max 50'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'articles_index';
    }

    public function execute(array $arguments): mixed
    {
        $filters = [
            'page'     => max(1, (int) ($arguments['page'] ?? 1)),
            'per_page' => min(50, max(1, (int) ($arguments['per_page'] ?? 10))),
        ];

        if ($keyword = $arguments['keyword'] ?? '') {
            $filters['keyword'] = $keyword;
        }
        if ($catalogId = $arguments['catalog_id'] ?? 0) {
            $filters['catalog_id'] = (int) $catalogId;
        }
        if (array_key_exists('active', $arguments)) {
            $filters['active'] = (bool) $arguments['active'];
        }

        $paginator = ArticleRepo::getInstance()->list($filters);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($article) => [
                'id'           => $article->id,
                'title'        => $article->translation->title ?? '',
                'catalog_name' => $article->catalog->translation->title ?? '',
                'slug'         => $article->slug,
                'active'       => (bool) $article->active,
                'viewed'       => $article->viewed,
                'created_at'   => (string) $article->created_at,
            ])->values()->all(),
        ];
    }
}
