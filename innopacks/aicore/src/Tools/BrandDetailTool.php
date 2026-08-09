<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Brand;
use InvalidArgumentException;

class BrandDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'brand_detail';
    }

    public function description(): string
    {
        return 'Get full details of a single brand by ID, including logo and SEO metadata.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Brand ID'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'brands_index';
    }

    public function execute(array $arguments): mixed
    {
        $brand = Brand::query()->with(['translation', 'translations'])->find((int) ($arguments['id'] ?? 0));

        if (! $brand) {
            throw new InvalidArgumentException("Brand [{$arguments['id']}] not found.");
        }

        $translation = $brand->translation;

        return [
            'id'               => $brand->id,
            'name'             => $brand->name,
            'slug'             => $brand->slug,
            'first'            => $brand->first ?? '',
            'logo'             => $brand->logo ?? '',
            'position'         => $brand->position,
            'active'           => (bool) $brand->active,
            'meta_title'       => $translation->meta_title ?? '',
            'meta_description' => $translation->meta_description ?? '',
            'meta_keywords'    => $translation->meta_keywords ?? '',
            'translations'     => $brand->translations->map(fn ($t) => [
                'locale'           => $t->locale,
                'name'             => $t->name ?? '',
                'meta_title'       => $t->meta_title ?? '',
                'meta_description' => $t->meta_description ?? '',
                'meta_keywords'    => $t->meta_keywords ?? '',
            ])->values()->all(),
        ];
    }
}
