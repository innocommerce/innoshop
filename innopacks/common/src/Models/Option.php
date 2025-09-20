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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 产品选项模型（如：颜色、尺寸、材质等）
 */
class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'position',
        'active',
        'required',
    ];

    protected $casts = [
        'name'        => 'array',
        'description' => 'array',
        'active'      => 'boolean',
        'required'    => 'boolean',
    ];

    /**
     * 获取选项的所有值
     */
    public function optionValues(): HasMany
    {
        return $this->hasMany(OptionValue::class)->orderBy('position');
    }

    /**
     * 获取使用此选项的产品
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_options')
            ->withPivot(['required', 'position'])
            ->withTimestamps();
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
        $locale = locale_code();

        return $this->name[$locale] ?? $this->name['en'] ?? '';
    }

    /**
     * 获取当前语言的名称（Accessor 方式）
     * 使用方式: $option->current_name
     *
     * @return string
     */
    public function getCurrentNameAttribute(): string
    {
        return $this->getLocalizedName();
    }

    /**
     * 获取当前语言的描述（Accessor 方式）
     * 使用方式: $option->current_description
     *
     * @return string
     */
    public function getCurrentDescriptionAttribute(): string
    {
        return $this->getLocalizedDescription();
    }

    /**
     * 获取本地化描述
     */
    public function getLocalizedDescription(?string $locale = null): string
    {
        $locale = locale_code();

        return $this->description[$locale] ?? $this->description['en'] ?? '';
    }

    /**
     * 获取原始名称属性（用于表单编辑）
     *
     * @return array
     */
    public function getRawNameAttribute(): array
    {
        return $this->name ?? [];
    }

    /**
     * 获取原始描述属性（用于表单编辑）
     *
     * @return array
     */
    public function getRawDescriptionAttribute(): array
    {
        return $this->description ?? [];
    }

    /**
     * 作用域：仅活跃的选项
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

    /**
     * 检查选项是否为必需的
     * 直接使用模型的 required 属性
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required ?? false;
    }

    /**
     * 检查选项是否为单选类型
     * 单选类型包括：select 和 radio
     * 多选类型包括：checkbox
     *
     * @return bool
     */
    public function isSingleType(): bool
    {
        return in_array($this->type, ['select', 'radio']);
    }

    /**
     * 检查选项是否为多选类型
     *
     * @return bool
     */
    public function isMultipleType(): bool
    {
        return $this->type === 'checkbox';
    }
}
