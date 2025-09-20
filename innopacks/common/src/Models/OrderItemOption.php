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
use InnoShop\Common\Models\Order\Item as OrderItem;

/**
 * 订单项选项模型
 *
 * @property int $id
 * @property int $order_item_id
 * @property int $option_id
 * @property int $option_value_id
 * @property string $option_name
 * @property string $option_value_name
 * @property float $price_adjustment
 */
class OrderItemOption extends BaseModel
{
    protected $table = 'order_option_values';

    protected $fillable = [
        'order_item_id',
        'option_id',
        'option_value_id',
        'option_name',
        'option_value_name',
        'price_adjustment',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
    ];

    /**
     * 获取所属订单项
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id', 'id');
    }

    /**
     * 获取格式化的选项价格
     */
    public function getPriceFormatAttribute(): string
    {
        return currency_format($this->price_adjustment);
    }

    /**
     * 获取当前语言的选项名称
     */
    public function getOptionNameLocalizedAttribute(): string
    {
        return $this->option_name;
    }

    /**
     * 获取当前语言的选项值名称
     */
    public function getOptionValueNameLocalizedAttribute(): string
    {
        return $this->option_value_name;
    }
}
