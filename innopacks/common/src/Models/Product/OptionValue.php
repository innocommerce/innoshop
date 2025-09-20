<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Product;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\Product;

/**
 * 产品选项值配置模型（产品的每个选项值加多少钱）
 */
class OptionValue extends Model
{
    use HasFactory;

    /**
     * 指定数据库表名
     */
    protected $table = 'product_option_values';

    protected $fillable = [
        'product_id',
        'option_id',
        'option_value_id',
        'price_adjustment',
        'quantity',
        'subtract_stock',
        'sku',
        'weight_adjustment',
    ];

    protected $casts = [
        'price_adjustment'  => 'decimal:2',
        'weight_adjustment' => 'decimal:2',
        'subtract_stock'    => 'boolean',
    ];

    /**
     * 获取关联的产品
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * 获取关联的选项
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(\InnoShop\Common\Models\Option::class);
    }

    /**
     * 获取关联的选项值
     */
    public function optionValue(): BelongsTo
    {
        return $this->belongsTo(\InnoShop\Common\Models\OptionValue::class);
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
     * 获取完整的SKU（产品SKU + 选项值SKU）
     */
    public function getFullSku(): string
    {
        if (empty($this->sku)) {
            return $this->product->sku ?? '';
        }

        $productSku = $this->product->sku ?? '';

        return $productSku ? $productSku.'-'.$this->sku : $this->sku;
    }

    /**
     * 检查是否有库存
     */
    public function hasStock(): bool
    {
        if (! $this->subtract_stock) {
            return true;
        }

        return $this->quantity > 0;
    }

    /**
     * 减少库存
     */
    public function decreaseStock(int $quantity): bool
    {
        if (! $this->subtract_stock) {
            return true;
        }

        if ($this->quantity < $quantity) {
            return false;
        }

        $this->quantity -= $quantity;

        return $this->save();
    }

    /**
     * 增加库存
     */
    public function increaseStock(int $quantity): bool
    {
        if (! $this->subtract_stock) {
            return true;
        }

        $this->quantity += $quantity;

        return $this->save();
    }
}
