<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\ProductRepo;
use InvalidArgumentException;

class ProductDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'product_detail';
    }

    public function description(): string
    {
        return 'Get full product details by ID: brand, categories, variants (规格/spec dimensions like color-size), SKUs with prices and stock, attributes (属性/parameters like material, weight), SEO TDK metadata, and translations (all locales: name/summary/meta fields).';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Product ID'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'products_index';
    }

    public function execute(array $arguments): mixed
    {
        $id = (int) ($arguments['id'] ?? 0);

        $product = ProductRepo::getInstance()->builder([])
            ->with([
                'brand',
                'translations',
                'categories.translation',
                'variants.translation',
                'variants.values.translation',
                'productAttributes.attribute.translation',
                'productAttributes.attribute.group.translation',
                'productAttributes.attributeValue.translation',
            ])
            ->find($id);

        if (! $product) {
            throw new InvalidArgumentException("Product [{$id}] not found.");
        }

        // Variant dimensions with available values
        $variants = $product->variants->map(fn ($v) => [
            'id'       => $v->id,
            'name'     => $v->translation->name ?? '',
            'position' => $v->position,
            'values'   => $v->values->map(fn ($val) => [
                'id'    => $val->id,
                'name'  => $val->translation->name ?? '',
                'image' => $val->image ?? '',
            ])->values()->all(),
        ])->values()->all();

        // SKUs with variant labels
        $skus = $product->skus->map(fn ($sku) => [
            'id'            => $sku->id,
            'code'          => $sku->code,
            'model'         => $sku->model,
            'price'         => (float) $sku->price,
            'origin_price'  => (float) ($sku->origin_price ?? 0),
            'quantity'      => (int) $sku->quantity,
            'is_default'    => (bool) $sku->is_default,
            'variant_label' => $sku->variant_label,
            'locale_labels' => $sku->getLocaleLabels(),
        ])->values()->all();

        // Attributes with group
        $attributes = $product->productAttributes->map(fn ($pa) => [
            'id'       => $pa->attribute_id,
            'name'     => $pa->attribute->translation->name ?? '',
            'group'    => $pa->attribute->group->translation->name ?? '',
            'group_id' => $pa->attribute->attribute_group_id,
            'value'    => $pa->attributeValue->translation->name ?? '',
        ])->values()->all();

        // Categories
        $categories = $product->categories->map(fn ($c) => [
            'id'   => $c->id,
            'name' => $c->translation->name ?? '',
            'slug' => $c->slug,
        ])->values()->all();

        return [
            'id'               => $product->id,
            'name'             => $product->translation->name ?? '',
            'slug'             => $product->slug,
            'url'              => $product->url,
            'price'            => (float) $product->price,
            'origin_price'     => (float) ($product->origin_price ?? 0),
            'quantity'         => (int) $product->quantity,
            'images'           => $product->images ?? [],
            'active'           => (bool) $product->active,
            'summary'          => $product->translation->summary ?? '',
            'meta_title'       => $product->translation->meta_title ?? '',
            'meta_description' => $product->translation->meta_description ?? '',
            'meta_keywords'    => $product->translation->meta_keywords ?? '',
            'translations'     => $product->translations->map(fn ($t) => [
                'locale'           => $t->locale,
                'name'             => $t->name ?? '',
                'summary'          => $t->summary ?? '',
                'content'          => $t->content ?? '',
                'selling_point'    => $t->selling_point ?? '',
                'meta_title'       => $t->meta_title ?? '',
                'meta_description' => $t->meta_description ?? '',
                'meta_keywords'    => $t->meta_keywords ?? '',
            ])->values()->all(),
            'weight'       => (float) $product->weight,
            'weight_class' => $product->weight_class ?? '',
            'brand'        => $product->brand->name ?? '',
            'brand_id'     => $product->brand_id,
            'categories'   => $categories,
            'is_multiple'  => $product->isMultiple(),
            'variants'     => $variants,
            'skus'         => $skus,
            'attributes'   => $attributes,
        ];
    }
}
