<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 订单选项值记录模型
 */
class OptionValue extends Model
{
    use HasFactory;

    protected $table = 'order_option_values';

    protected $fillable = [
        'order_item_id',
        'option_id',
        'option_value_id',
        'option_name',
        'option_value_name',
        'price_adjustment',
        'weight_adjustment',
    ];

    protected $casts = [
        'price_adjustment'  => 'decimal:2',
        'weight_adjustment' => 'decimal:2',
    ];

    /**
     * 获取关联的订单项
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'order_item_id');
    }

    /**
     * 获取本地化选项名称
     */
    public function getLocalizedOptionName(?string $locale = null): string
    {
        return $this->option_name;
    }

    /**
     * 获取本地化选项值名称
     */
    public function getLocalizedOptionValueName(?string $locale = null): string
    {
        return $this->option_value_name;
    }

    /**
     * 获取格式化的重量调整
     */
    public function getFormattedWeightAdjustment(): string
    {
        if ($this->weight_adjustment == 0) {
            return '';
        }

        $prefix = $this->weight_adjustment > 0 ? '+' : '';

        return $prefix.$this->weight_adjustment.' kg';
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
