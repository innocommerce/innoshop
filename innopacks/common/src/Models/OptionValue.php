<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 选项值模型（如：红色、蓝色、S码、M码等）
 */
class OptionValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_id',
        'name',
        'image',
        'position',
        'active',
    ];

    protected $casts = [
        'name'   => 'array',
        'active' => 'boolean',
    ];

    /**
     * 获取所属选项
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }

    /**
     * 获取产品选项值配置
     */
    public function productOptionValues(): HasMany
    {
        return $this->hasMany(\InnoShop\Common\Models\Product\OptionValue::class);
    }

    /**
     * 获取本地化名称
     */
    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $this->name[$locale] ?? $this->name['en'] ?? '';
    }

    /**
     * 获取当前语言的名称（方法调用方式）
     * 使用方式: $optionValue->getCurrentName()
     *
     * @return string
     */
    public function getCurrentName(): string
    {
        return $this->getLocalizedName();
    }

    /**
     * 获取当前语言的名称（Accessor 方式）
     * 使用方式: $optionValue->current_name
     *
     * @return string
     */
    public function getCurrentNameAttribute(): string
    {
        return $this->getLocalizedName();
    }

    /**
     * 获取图片URL
     */
    public function getImageUrl(): string
    {
        if (empty($this->image)) {
            return '';
        }

        return asset($this->image);
    }

    /**
     * 作用域：仅活跃的选项值
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * 作用域：按位置排序
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
