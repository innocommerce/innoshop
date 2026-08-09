<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Locale;

class LocaleListTool extends BaseTool
{
    public function name(): string
    {
        return 'locale_list';
    }

    public function description(): string
    {
        return 'List all system locales with code, name, image, and active status.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'active' => ['type' => 'boolean', 'description' => 'Filter by active status; omit to include both'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'locales_index';
    }

    public function execute(array $arguments): mixed
    {
        $query = Locale::query();

        if (array_key_exists('active', $arguments)) {
            $query->where('active', (bool) $arguments['active']);
        }

        $locales = $query->orderBy('position')->orderBy('code')->get();

        return [
            'total' => $locales->count(),
            'items' => $locales->map(fn ($l) => [
                'id'      => $l->id,
                'name'    => $l->name,
                'code'    => $l->code,
                'image'   => $l->image,
                'active'  => (bool) $l->active,
                'default' => (bool) $l->is_default,
            ])->values()->all(),
        ];
    }
}
