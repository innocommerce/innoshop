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
 * 产品选项关联模型
 */
class Option extends Model
{
    use HasFactory;

    protected $table = 'product_options';

    protected $fillable = [
        'product_id',
        'option_id',
        'position',
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
     * 作用域：仅必需的选项
     */
    public function scopeRequired($query)
    {
        return $query->where('required', true);
    }

    /**
     * 作用域：按位置排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
