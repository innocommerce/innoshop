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

    const TYPE_SELECT = 'select';

    const TYPE_INPUT = 'input';

    const TYPE_NUMBER = 'number';

    const TYPE_TEXTAREA = 'textarea';

    const TYPE_RICHTEXT = 'richtext';

    const TYPE_DATE = 'date';

    protected $fillable = [
        'category_id', 'attribute_group_id', 'position', 'type', 'is_filterable',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
    ];

    /**
     * @param  $query
     * @return mixed
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Free-form value types store the raw value on product_attributes
     * instead of referencing a preset attribute_value_id.
     *
     * @return bool
     */
    public function isFreeInput(): bool
    {
        return in_array($this->type, [self::TYPE_INPUT, self::TYPE_NUMBER, self::TYPE_TEXTAREA, self::TYPE_RICHTEXT, self::TYPE_DATE], true);
    }

    /**
     * Long-text types hold long-form content edited in a dedicated product
     * tab instead of the specification table.
     *
     * @return array
     */
    public static function longTextTypes(): array
    {
        return [self::TYPE_TEXTAREA, self::TYPE_RICHTEXT];
    }

    /**
     * @return bool
     */
    public function isLongText(): bool
    {
        return in_array($this->type, self::longTextTypes(), true);
    }

    /**
     * @return HasMany
     */
    public function values(): HasMany
    {
        return $this->hasMany(Value::class);
    }

    /**
     * @return HasMany
     */
    public function productAttributes(): HasMany
    {
        return $this->hasMany(Product\Attribute::class, 'attribute_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'attribute_group_id', 'id');
    }
}
