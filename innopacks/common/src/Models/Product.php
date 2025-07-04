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
use Illuminate\Database\Eloquent\Relations\HasOne;
use InnoShop\Common\Models\Customer\Favorite;
use InnoShop\Common\Models\Order\Item;
use InnoShop\Common\Models\Product\Relation;
use InnoShop\Common\Models\Product\Sku;
use InnoShop\Common\Models\Product\Video;
use InnoShop\Common\Traits\HasPackageFactory;
use InnoShop\Common\Traits\Replicate;
use InnoShop\Common\Traits\Translatable;

class Product extends BaseModel
{
    use HasPackageFactory, Replicate, Translatable;

    public $timestamps = true;

    const TYPE_NORMAL = 'normal';

    const TYPE_BUNDLE = 'bundle';

    protected $fillable = [
        'type', 'brand_id', 'images', 'price', 'tax_class_id', 'spu_code', 'slug', 'is_virtual', 'variables', 'position',
        'spu_code', 'active', 'weight', 'weight_class', 'sales', 'viewed',
    ];

    protected $casts = [
        'variables'  => 'array',
        'images'     => 'array',
        'active'     => 'boolean',
        'is_virtual' => 'boolean',
    ];

    protected $appends = ['image'];

    /**
     * @return BelongsTo
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * @return HasOne
     */
    public function masterSku(): HasOne
    {
        return $this->hasOne(Sku::class)->where('is_default', 1);
    }

    /**
     * Since the attribute is defined within the Laravel core,
     * Please see https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php#L52
     * Consequently, the name of the relation is referred to as productAttributes.
     * @return HasMany
     */
    public function productAttributes(): HasMany
    {
        return $this->hasMany(\InnoShop\Common\Models\Product\Attribute::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function relations(): HasMany
    {
        return $this->hasMany(Relation::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function skus(): HasMany
    {
        return $this->hasMany(Sku::class, 'product_id');
    }

    /**
     * @return BelongsTo
     */
    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    /**
     * @return BelongsTo
     */
    public function weightClass(): BelongsTo
    {
        return $this->belongsTo(WeightClass::class, 'weight_class', 'code');
    }

    /**
     * @return HasMany
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(Item::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    /**
     * Bundle relation
     * @return HasMany
     */
    public function bundles(): HasMany
    {
        return $this->hasMany(Product\Bundle::class, 'product_id');
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id');
    }

    /**
     * @return BelongsToMany
     */
    public function relationProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_relations', 'product_id', 'relation_id');
    }

    /**
     * @return BelongsToMany
     */
    public function favCustomers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_favorites', 'product_id', 'customer_id');
    }

    /**
     * @return mixed
     */
    public function totalQuantity(): int
    {
        return (int) $this->skus->sum('quantity');
    }

    /**
     * @param  int  $customerId
     * @return mixed
     */
    public function hasFavorite(int $customerId = 0): mixed
    {
        if (empty($customerId)) {
            $customerId = current_customer_id();
        }
        if (empty($customerId)) {
            return false;
        }

        return $this->favorites->contains(function ($item) use ($customerId) {
            return $item->customer_id === $customerId;
        });
    }

    /**
     * @return array
     */
    public function groupedAttributes(): array
    {
        $this->loadMissing([
            'productAttributes.attribute.translation',
            'productAttributes.attribute.group.translation',
            'productAttributes.attributeValue.translation',
        ]);
        $attributes = [];
        foreach ($this->productAttributes as $productAttribute) {
            $attribute = $productAttribute->attribute;
            $groupID   = $attribute->attribute_group_id;
            if (! isset($attributes[$groupID]['attribute_group_name'])) {
                $attributes[$groupID]['attribute_group_name'] = $attribute->group->translation->name ?? 'default';
            }
            $attributes[$groupID]['attributes'][] = [
                'attribute'       => $attribute->translation->name,
                'attribute_value' => $productAttribute->attributeValue->translation->name,
            ];
        }

        return $attributes;
    }

    /**
     * Check product has many multiple variants.
     *
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->variables || $this->skus->count() > 1;
    }

    /**
     * @return string
     */
    public function getImageAttribute(): string
    {
        $images = $this->images ?? [];

        return $images[0] ?? '';
    }

    /**
     * Get URL
     *
     * @return string
     * @throws Exception
     */
    public function getUrlAttribute(): string
    {
        if ($this->slug) {
            return front_route('products.slug_show', ['slug' => $this->slug]);
        }

        return front_route('products.show', $this);
    }

    /**
     * Get edit URL
     *
     * @return string
     */
    public function getEditUrlAttribute(): string
    {
        return panel_route('products.edit', $this);
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
}
