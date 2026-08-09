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
use InnoShop\Common\Repositories\ArticleRepo;
use InvalidArgumentException;

class ArticleUpdateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'article_update';
    }

    public function description(): string
    {
        return '⚠️ WRITE: Update an existing blog article using PATCH semantics. Supports title, summary, content, SEO meta fields (meta_title/meta_description/meta_keywords), catalog, image, and per-locale translations.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id'               => ['type' => 'integer', 'description' => 'Article ID (required)'],
                'title'            => ['type' => 'string', 'description' => 'Article title (default locale)'],
                'summary'          => ['type' => 'string', 'description' => 'Short summary (default locale)'],
                'content'          => ['type' => 'string', 'description' => 'Full content HTML (default locale)'],
                'meta_title'       => ['type' => 'string', 'description' => 'SEO meta title (default locale)'],
                'meta_description' => ['type' => 'string', 'description' => 'SEO meta description (default locale)'],
                'meta_keywords'    => ['type' => 'string', 'description' => 'SEO meta keywords, comma separated (default locale)'],
                'catalog_id'       => ['type' => 'integer', 'description' => 'Catalog (category) ID'],
                'slug'             => ['type' => 'string', 'description' => 'URL slug'],
                'image'            => ['type' => 'string', 'description' => 'Cover image URL'],
                'author'           => ['type' => 'string', 'description' => 'Author name'],
                'position'         => ['type' => 'integer', 'description' => 'Display position'],
                'active'           => ['type' => 'boolean', 'description' => 'Active status'],
                'translations'     => self::translationsSchema([
                    'title'            => 'Article title in this locale. Required when adding a new locale.',
                    'summary'          => 'Short summary in this locale',
                    'content'          => 'Full content HTML in this locale',
                    'meta_title'       => 'SEO meta title in this locale',
                    'meta_description' => 'SEO meta description in this locale',
                    'meta_keywords'    => 'SEO meta keywords, comma separated, in this locale',
                ], 'Per-locale overrides keyed by locale code (multilingual), e.g. {"zh-cn": {"title": "标题", "meta_title": "SEO 标题"}}. Each locale key maps to an entry with the fields below. Adding a new locale requires title. Same-locale entries override top-level fields.'),
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'articles_update';
    }

    public function execute(array $arguments): mixed
    {
        $article = Article::query()->find((int) ($arguments['id'] ?? 0));
        if (! $article) {
            throw new InvalidArgumentException("Article [{$arguments['id']}] not found.");
        }

        $data = [];
        foreach (['catalog_id', 'slug', 'image', 'author', 'position', 'active'] as $key) {
            if (array_key_exists($key, $arguments)) {
                $data[$key] = $arguments[$key];
            }
        }

        // Default-locale translation fields (title + SEO TDK)
        $fields = [];
        foreach (['title', 'summary', 'content', ...self::META_FIELDS] as $key) {
            if (array_key_exists($key, $arguments)) {
                $fields[$key] = (string) $arguments[$key];
            }
        }

        $translations = [];
        if ($fields) {
            $translations[locale_code()] = $fields;
        }
        if ($extra = $arguments['translations'] ?? []) {
            // New locale rows require a title (NOT NULL column); existing locales accept partial updates.
            $existingLocales = $article->translations()->pluck('locale')->all();
            $allowed         = ['title', 'summary', 'content', ...self::META_FIELDS];
            foreach ((array) $extra as $loc => $locFields) {
                if (! is_array($locFields)) {
                    continue;
                }
                $entry = array_intersect_key($locFields, array_flip($allowed));
                if ($entry && (in_array($loc, $existingLocales) || ! empty($entry['title']))) {
                    $translations[$loc] = array_merge($translations[$loc] ?? [], $entry);
                }
            }
        }
        if ($translations) {
            $data['translations'] = $translations;
        }

        if (empty($data)) {
            throw new InvalidArgumentException('No fields to update.');
        }

        ArticleRepo::getInstance()->patch($article, $data);

        $article->refresh()->load(['translation', 'translations', 'catalog.translation', 'tags']);

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
            'updated_at' => (string) $article->updated_at,
        ];
    }
}
