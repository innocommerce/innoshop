<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Product\Variant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Product\Variant;

class Translation extends BaseModel
{
    protected $table = 'product_variant_translations';

    protected $fillable = [
        'variant_id', 'locale', 'name',
    ];

    /**
     * @return BelongsTo
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }
}
