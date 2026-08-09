<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\OptionRepo;

class OptionListTool extends BaseTool
{
    public function name(): string
    {
        return 'option_list';
    }

    public function description(): string
    {
        return 'List product options (add-on services like gift wrapping, extended warranty, engraving — NOT product variants/规格) with their values. Options affect price.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword'  => ['type' => 'string', 'description' => 'Search keyword matched against option name'],
                'page'     => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page' => ['type' => 'integer', 'description' => 'Items per page, default 20, max 100'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'options_index';
    }

    public function execute(array $arguments): mixed
    {
        $repo    = OptionRepo::getInstance();
        $page    = max(1, (int) ($arguments['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($arguments['per_page'] ?? 20)));

        $filters = [];
        if ($keyword = $arguments['keyword'] ?? '') {
            $filters['keyword'] = $keyword;
        }

        $builder   = $repo->builder($filters)->with(['optionValues']);
        $paginator = $builder->paginate($perPage, ['*'], 'page', $page);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($opt) => [
                'id'     => $opt->id,
                'name'   => $opt->name,
                'type'   => $opt->type,
                'values' => $opt->optionValues->map(fn ($v) => [
                    'id'   => $v->id,
                    'name' => $v->current_name,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
