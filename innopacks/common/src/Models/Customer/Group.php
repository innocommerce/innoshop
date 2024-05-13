<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Customer;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Customer\Group\Translation;
use InnoShop\Common\Traits\Translatable;

class Group extends BaseModel
{
    use Translatable;

    protected $table = 'customer_groups';

    protected $fillable = [
        'level', 'mini_cost', 'discount_rate',
    ];

    /**
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class, 'customer_group_id', 'id');
    }

    /**
     * @return HasOne
     * @throws \Exception
     */
    public function translation(): HasOne
    {
        return $this->hasOne(Translation::class, 'customer_group_id', 'id')
            ->where('locale', locale_code());
    }
}
