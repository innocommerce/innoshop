<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Product\Variant\Value;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Product\Variant\Value;

class Translation extends BaseModel
{
    protected $table = 'product_variant_value_translations';

    protected $fillable = [
        'value_id', 'locale', 'name',
    ];

    /**
     * @return BelongsTo
     */
    public function value(): BelongsTo
    {
        return $this->belongsTo(Value::class, 'value_id');
    }
}
