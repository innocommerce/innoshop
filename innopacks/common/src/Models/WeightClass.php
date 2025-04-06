<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use InnoShop\Common\Libraries\Weight;
use InnoShop\Common\Traits\HasPackageFactory;

class WeightClass extends BaseModel
{
    use HasPackageFactory;

    protected $table = 'weight_classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'unit',
        'value',
        'position',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'active'   => 'boolean',
        'value'    => 'float',
        'position' => 'integer',
    ];

    /**
     * Get products using this weight class
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'weight_class', 'code');
    }

    /**
     * Convert weight from this unit to another unit
     *
     * @param  float  $value  Weight value
     * @param  string  $toCode  Target weight unit code
     * @return float
     */
    public function convert(float $value, string $toCode): float
    {
        return Weight::getInstance()->convert($value, $this->code, $toCode);
    }

    /**
     * Format weight value with unit
     *
     * @param  float  $value  Weight value
     * @return string
     */
    public function format(float $value): string
    {
        return Weight::getInstance()->format($value, $this->code);
    }
}
