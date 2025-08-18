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
     * Model validation rules to prevent circular references
     */
    protected static function booted(): void
    {
        static::saving(function ($category) {
            // Check parent cannot be itself
            if ($category->parent_id && $category->parent_id == $category->id) {
                throw new \Exception(trans('panel/common.category_parent_self'));
            }

            // Check for circular references
            if ($category->parent_id && $category->parent_id > 0) {
                $visited       = [$category->id];
                $currentParent = self::find($category->parent_id);

                while ($currentParent) {
                    if (in_array($currentParent->id, $visited)) {
                        throw new \Exception(trans('panel/common.category_circular_reference'));
                    }
                    $visited[]     = $currentParent->id;
                    $currentParent = $currentParent->parent;
                }
            }
        });
    }

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
     * @throws Exception
     */
    public function getUrlAttribute(): string
    {
        if ($this->slug) {
            return front_route('categories.slug_show', ['slug' => $this->slug]);
        }

        return front_route('categories.show', $this);
    }

    /**
     * Get edit URL
     *
     * @return string
     */
    public function getEditUrlAttribute(): string
    {
        return panel_route('categories.edit', $this);
    }

    /**
     * Get URL
     *
     * @return string
     * @throws Exception
     */
    public function getImageUrlAttribute(): string
    {
        return $this->getImageUrl();
    }

    /**
     * @param  int  $with
     * @param  int  $height
     * @return string
     * @throws Exception
     */
    public function getImageUrl(int $with = 600, int $height = 600): string
    {
        return image_resize($this->image ?? '', $with, $height);
    }

    /**
     * Get product count
     *
     * @return int
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }
}
