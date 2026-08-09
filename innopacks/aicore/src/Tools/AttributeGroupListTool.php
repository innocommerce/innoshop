<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\Attribute\GroupRepo;

class AttributeGroupListTool extends BaseTool
{
    public function name(): string
    {
        return 'attribute_group_list';
    }

    public function description(): string
    {
        return 'List all attribute groups (categories of product parameters like physical specs, technical specs). Use to find group_id for filtering attributes.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => new \stdClass,
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'attributes_index';
    }

    public function execute(array $arguments): mixed
    {
        $repo   = GroupRepo::getInstance();
        $groups = $repo->builder([])->with('translation')->orderBy('position')->get();

        return [
            'items' => $groups->map(fn ($g) => [
                'id'   => $g->id,
                'name' => $g->translation->name ?? $g->name ?? '',
            ])->values()->all(),
        ];
    }
}
