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
use InnoShop\Common\Models\Attribute\Value;
use InnoShop\Common\Models\BaseModel;

class Attribute extends BaseModel
{
    protected $table = 'product_attributes';

    protected $fillable = [
        'product_id', 'attribute_id', 'attribute_value_id', 'value_text', 'value_number', 'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(\InnoShop\Common\Models\Attribute::class, 'attribute_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(Value::class, 'attribute_value_id', 'id');
    }

    /**
     * Long-text values are stored as a locale=>content JSON map in
     * value_text (product_attributes rows are rebuilt on every save, so a
     * classic translations table keyed by row id cannot survive). Legacy
     * plain strings predate multi-language support and decode to an empty map.
     *
     * @return array
     */
    public function valueTextMap(): array
    {
        $decoded = json_decode((string) $this->value_text, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Resolve the displayable long-text value: current locale -> system
     * default locale -> any available locale -> legacy plain string.
     *
     * @param  string|null  $locale
     * @return string
     */
    public function localizedValueText(?string $locale = null): string
    {
        $map = $this->valueTextMap();
        if (empty($map)) {
            return (string) ($this->value_text ?? '');
        }

        $locale  = $locale ?: locale_code();
        $default = setting_locale_code();

        return $map[$locale] ?? $map[$default] ?? (collect($map)->first(fn ($v) => trim((string) $v) !== '') ?? '');
    }
}
