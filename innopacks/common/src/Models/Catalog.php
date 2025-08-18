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
use Illuminate\Database\Eloquent\Relations\HasMany;
use InnoShop\Common\Traits\Translatable;

class Catalog extends BaseModel
{
    use Translatable;

    protected $fillable = [
        'parent_id', 'slug', 'position', 'active',
    ];

    /**
     * Model validation rules to prevent circular references
     */
    protected static function booted(): void
    {
        static::saving(function ($catalog) {
            // Check parent cannot be itself
            if ($catalog->parent_id && $catalog->parent_id == $catalog->id) {
                throw new \Exception(trans('panel/common.category_parent_self'));
            }

            // Check for circular references
            if ($catalog->parent_id && $catalog->parent_id > 0) {
                $visited       = [$catalog->id];
                $currentParent = self::find($catalog->parent_id);

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

    public $appends = [
        'url',
    ];

    /**
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Catalog::class, 'parent_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Catalog::class, 'parent_id', 'id');
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
            return front_route('catalogs.slug_show', ['slug' => $this->slug]);
        }

        return front_route('catalogs.show', $this);
    }
}
