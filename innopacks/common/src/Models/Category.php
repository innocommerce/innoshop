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
use Illuminate\Database\Eloquent\Relations\HasMany;
use InnoShop\Common\Traits\HasPackageFactory;
use InnoShop\Common\Traits\Translatable;

class Category extends BaseModel
{
    use HasPackageFactory, Translatable;

    protected $fillable = ['parent_id', 'slug', 'position', 'image', 'active'];

    public $appends = [
        'url',
    ];

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id');
    }

    /**
     * Get slug url link.
     *
     * @return string
     * @throws \Exception
     */
    public function getUrlAttribute(): string
    {
        if ($this->slug) {
            return front_route('categories.slug_show', ['slug' => $this->slug]);
        }

        return front_route('categories.show', $this);
    }
}
