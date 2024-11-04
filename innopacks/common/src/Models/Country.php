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

class Country extends BaseModel
{
    protected $table = 'countries';

    protected $fillable = [
        'name', 'code', 'continent', 'position', 'active',
    ];

    public function states(): HasMany
    {
        return $this->hasMany(State::class, 'country_id', 'id');
    }
}
