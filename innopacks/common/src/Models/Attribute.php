<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InnoShop\Common\Models\Attribute\Group;
use InnoShop\Common\Models\Attribute\Value;
use InnoShop\Common\Traits\Translatable;

class Attribute extends BaseModel
{
    use Translatable;

    protected $table = 'attributes';

    protected $fillable = [
        'category_id', 'attribute_group_id', 'position',
    ];

    /**
     * @return HasMany
     */
    public function values(): HasMany
    {
        return $this->hasMany(Value::class);
    }

    /**
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'attribute_group_id', 'id');
    }
}
