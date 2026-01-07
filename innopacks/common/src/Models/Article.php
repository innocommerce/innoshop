<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InnoShop\Common\Traits\Translatable;

class Article extends BaseModel
{
    use Translatable;

    protected $fillable = [
        'catalog_id', 'slug', 'position', 'viewed', 'author', 'image', 'active',
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
     * Related articles relationship
     * @return HasMany
     */
    public function relatedArticles(): HasMany
    {
        return $this->hasMany(Article\Relation::class, 'article_id');
    }

    /**
     * Related products relationship
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'article_products', 'article_id', 'product_id')
            ->withTimestamps();
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
     * @throws Exception
     */
    public function getUrlAttribute(): string
    {
        if ($this->slug) {
            return front_route('articles.slug_show', ['slug' => $this->slug]);
        }

        return front_route('articles.show', $this);
    }

    /**
     * Get edit URL
     *
     * @return string
     */
    public function getEditUrlAttribute(): string
    {
        return panel_route('articles.edit', $this);
    }

    /**
     * Get article image with fallback logic.
     * Priority: current locale translation image -> main image
     *
     * @return string
     */
    public function getImageAttribute(): string
    {
        // Get the original image value from database (use attributes to avoid recursion)
        $originalImage = $this->attributes['image'] ?? '';

        // Try to get image from current locale translation
        $translationImage = $this->fallbackName('image');

        // Return translation image if exists, otherwise return main image
        return $translationImage ?: $originalImage;
    }
}
