<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Cart;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\CartItem;

/**
 * 购物车选项值记录模型
 */
class OptionValue extends Model
{
    use HasFactory;

    protected $table = 'cart_option_values';

    protected $fillable = [
        'cart_item_id',
        'option_id',
        'option_value_id',
        'option_name',
        'option_value_name',
        'price_adjustment',
    ];

    protected $casts = [
        'option_name'       => 'array',
        'option_value_name' => 'array',
        'price_adjustment'  => 'decimal:2',
    ];

    /**
     * 获取关联的购物车项
     */
    public function cartItem(): BelongsTo
    {
        return $this->belongsTo(CartItem::class);
    }

    /**
     * 获取本地化选项名称
     */
    public function getLocalizedOptionName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $this->option_name[$locale] ?? $this->option_name['en'] ?? '';
    }

    /**
     * 获取本地化选项值名称
     */
    public function getLocalizedOptionValueName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $this->option_value_name[$locale] ?? $this->option_value_name['en'] ?? '';
    }

    /**
     * 获取格式化的价格调整
     */
    public function getFormattedPriceAdjustment(): string
    {
        if ($this->price_adjustment == 0) {
            return '';
        }

        $prefix = $this->price_adjustment > 0 ? '+' : '';

        return $prefix.currency_format($this->price_adjustment);
    }
}
