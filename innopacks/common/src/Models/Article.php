<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InnoShop\Common\Traits\Translatable;

class Article extends BaseModel
{
    use Translatable;

    protected $fillable = [
        'catalog_id', 'slug', 'position', 'viewed', 'author', 'active',
    ];

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(Catalog::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tags', 'article_id', 'tag_id');
    }

    /**
     * Get tag names.
     *
     * @return mixed
     */
    public function getTagNamesAttribute(): mixed
    {
        return $this->tags->pluck('translation.name')->implode(', ');
    }

    /**
     * Get slug url link.
     *
     * @return string
     */
    public function getUrlAttribute(): string
    {
        if ($this->slug) {
            return front_route('articles.slug_show', ['slug' => $this->slug]);
        }

        return front_route('articles.show', $this);
    }
}
