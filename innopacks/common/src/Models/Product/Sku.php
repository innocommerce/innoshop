<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Product;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Product;

class Sku extends BaseModel
{
    protected $table = 'product_skus';

    protected $fillable = [
        'product_id', 'product_image_id', 'model', 'code', 'price', 'origin_price', 'quantity', 'is_default', 'position',
        'variants',
    ];

    protected $casts = [
        'variants' => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'product_image_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function variants(): HasMany
    {
        return $this->hasMany(SkuVariant::class, 'product_sku_id');
    }

    /**
     * @return array
     */
    public function getLocaleLabels(): array
    {
        $labels    = [];
        $variables = $this->product->variables;
        if (empty($variables) || empty($this->variants)) {
            return [];
        }

        $localeCode = front_locale_code();
        foreach ($this->variants as $key => $value) {
            $labels[] = [
                'name'  => $variables[$key]['name'][$localeCode],
                'value' => $variables[$key]['values'][$value]['name'][$localeCode],
            ];
        }

        return $labels;
    }

    /**
     * @return string
     */
    public function getPriceFormatAttribute(): string
    {
        return currency_format($this->price);
    }

    /**
     * @return string
     */
    public function getOriginPriceFormatAttribute(): string
    {
        return currency_format($this->origin_price);
    }

    /**
     * @return string
     */
    public function getImagePathAttribute(): string
    {
        return $this->image->path ?? '';
    }

    /**
     * @return string
     */
    public function getVariantLabelAttribute(): string
    {
        $vLabel = '';
        $labels = $this->getLocaleLabels();
        if (empty($labels)) {
            return '';
        }

        foreach ($labels as $label) {
            $vLabel .= $label['name'].':'.$label['value'].'; ';
        }

        return $vLabel;
    }
}
