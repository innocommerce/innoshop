<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Category;
use InvalidArgumentException;

class CategoryDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'category_detail';
    }

    public function description(): string
    {
        return 'Get full details of a single category by ID, including SEO metadata, parent, and children.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Category ID'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'categories_index';
    }

    public function execute(array $arguments): mixed
    {
        $category = Category::query()->with([
            'translation',
            'translations',
            'parent.translation',
            'children.translation',
        ])->find((int) ($arguments['id'] ?? 0));

        if (! $category) {
            throw new InvalidArgumentException("Category [{$arguments['id']}] not found.");
        }

        $translation = $category->translation;

        return [
            'id'               => $category->id,
            'name'             => $category->fallbackName(),
            'slug'             => $category->slug,
            'image'            => $category->image,
            'parent_id'        => $category->parent_id,
            'parent_name'      => $category->parent?->fallbackName(),
            'position'         => $category->position,
            'active'           => (bool) $category->active,
            'meta_title'       => $translation->meta_title ?? '',
            'meta_description' => $translation->meta_description ?? '',
            'meta_keywords'    => $translation->meta_keywords ?? '',
            'translations'     => $category->translations->map(fn ($t) => [
                'locale'           => $t->locale,
                'name'             => $t->name ?? '',
                'meta_title'       => $t->meta_title ?? '',
                'meta_description' => $t->meta_description ?? '',
                'meta_keywords'    => $t->meta_keywords ?? '',
            ])->values()->all(),
            'children' => $category->children->map(fn ($child) => [
                'id'     => $child->id,
                'name'   => $child->fallbackName(),
                'slug'   => $child->slug,
                'active' => (bool) $child->active,
            ])->values()->all(),
        ];
    }
}
