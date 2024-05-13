<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxClass extends BaseModel
{
    protected $table = 'tax_classes';

    protected $fillable = [
        'name', 'description',
    ];

    /**
     * @return HasMany
     */
    public function taxRules(): HasMany
    {
        return $this->hasMany(TaxRule::class);
    }
}
