<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\AttributeRepo;

class AttributeListTool extends BaseTool
{
    public function name(): string
    {
        return 'attribute_list';
    }

    public function description(): string
    {
        return 'List product attributes (technical parameters like material, weight, model — NOT product variants/规格) with their values. Supports filtering by attribute group.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'attribute_group_id' => ['type' => 'integer', 'description' => 'Filter by attribute group ID'],
                'keyword'            => ['type' => 'string', 'description' => 'Search keyword matched against attribute name'],
                'page'               => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page'           => ['type' => 'integer', 'description' => 'Items per page, default 20, max 100'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'attributes_index';
    }

    public function execute(array $arguments): mixed
    {
        $repo    = AttributeRepo::getInstance();
        $page    = max(1, (int) ($arguments['page'] ?? 1));
        $perPage = min(100, max(1, (int) ($arguments['per_page'] ?? 20)));

        $filters = [];
        if ($groupId = $arguments['attribute_group_id'] ?? 0) {
            $filters['attribute_group_id'] = (int) $groupId;
        }
        if ($keyword = $arguments['keyword'] ?? '') {
            $filters['keyword'] = $keyword;
        }

        $builder   = $repo->builder($filters);
        $paginator = $builder->paginate($perPage, ['*'], 'page', $page);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($attr) => [
                'id'         => $attr->id,
                'name'       => $attr->translation->name ?? '',
                'group_id'   => $attr->attribute_group_id,
                'group_name' => $attr->group->translation->name ?? '',
                'values'     => $attr->values->map(fn ($v) => [
                    'id'   => $v->id,
                    'name' => $v->translation->name ?? '',
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
