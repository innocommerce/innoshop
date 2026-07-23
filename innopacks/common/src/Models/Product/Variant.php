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
use InnoShop\Common\Models\Product\Variant\Value;
use InnoShop\Common\Traits\Translatable;

/**
 * Product variant dimension (e.g. Color, Size).
 *
 * Normalized replacement for the legacy JSON entries stored in
 * products.variables. Each product owns an ordered list of variants;
 * each variant owns an ordered list of Values.
 */
class Variant extends BaseModel
{
    use Translatable;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id', 'position', 'is_image',
    ];

    protected $casts = [
        'is_image' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function values(): HasMany
    {
        return $this->hasMany(Value::class, 'variant_id')->orderBy('position');
    }
}
