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

class Region extends BaseModel
{
    protected $table = 'regions';

    protected $fillable = [
        'name', 'description', 'position', 'active',
    ];

    /**
     * @return HasMany
     */
    public function regionStates(): HasMany
    {
        return $this->hasMany(\InnoShop\Common\Models\Region\State::class);
    }
}
