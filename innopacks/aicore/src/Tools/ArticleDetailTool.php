<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Article;
use InvalidArgumentException;

class ArticleDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'article_detail';
    }

    public function description(): string
    {
        return 'Get full details of a single blog article by ID, including content, SEO metadata, and tags.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Article ID'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'articles_index';
    }

    public function execute(array $arguments): mixed
    {
        $article = Article::query()->with([
            'translation',
            'translations',
            'catalog.translation',
            'tags',
        ])->find((int) ($arguments['id'] ?? 0));

        if (! $article) {
            throw new InvalidArgumentException("Article [{$arguments['id']}] not found.");
        }

        $t = $article->translation;

        return [
            'id'               => $article->id,
            'title'            => $t->title ?? '',
            'slug'             => $article->slug,
            'summary'          => $t->summary ?? '',
            'content'          => $t->content ?? '',
            'catalog_id'       => $article->catalog_id,
            'catalog_name'     => $article->catalog->translation->title ?? '',
            'image'            => $article->image,
            'author'           => $article->author,
            'active'           => (bool) $article->active,
            'viewed'           => $article->viewed,
            'meta_title'       => $t->meta_title ?? '',
            'meta_description' => $t->meta_description ?? '',
            'meta_keywords'    => $t->meta_keywords ?? '',
            'translations'     => $article->translations->map(fn ($item) => [
                'locale'           => $item->locale,
                'title'            => $item->title ?? '',
                'summary'          => $item->summary ?? '',
                'meta_title'       => $item->meta_title ?? '',
                'meta_description' => $item->meta_description ?? '',
                'meta_keywords'    => $item->meta_keywords ?? '',
            ])->values()->all(),
            'tags'       => $article->tags->map(fn ($tag) => $tag->name)->values()->all(),
            'created_at' => (string) $article->created_at,
            'updated_at' => (string) $article->updated_at,
        ];
    }
}
