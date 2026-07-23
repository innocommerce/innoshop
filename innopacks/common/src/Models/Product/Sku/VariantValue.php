<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Product\Sku;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use InnoShop\Common\Models\Product\Variant;
use InnoShop\Common\Models\Product\Variant\Value;

/**
 * Pivot linking a SKU to the specific variant value it picks per dimension.
 *
 * Replaces the legacy product_skus.variants JSON index array. The
 * (sku_id, variant_id) UNIQUE constraint enforces at the DB layer that a SKU
 * can pick exactly one value per dimension — the guarantee the JSON design
 * could not provide.
 */
class VariantValue extends Pivot
{
    protected $table = 'product_sku_variant_values';

    public $timestamps = true;

    protected $fillable = [
        'sku_id', 'variant_id', 'value_id',
    ];

    /**
     * @return BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

    /**
     * @return BelongsTo
     */
    public function value(): BelongsTo
    {
        return $this->belongsTo(Value::class, 'value_id');
    }
}
