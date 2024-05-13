<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

class Brand extends BaseModel
{
    protected $table = 'brands';

    protected $fillable = [
        'name', 'slug', 'first', 'logo', 'position', 'active',
    ];

    public $appends = [
        'url',
    ];

    /**
     * Get slug url link.
     *
     * @return string
     * @throws \Exception
     */
    public function getUrlAttribute(): string
    {
        if ($this->slug) {
            return front_route('brands.slug_show', ['slug' => $this->slug]);
        }

        return front_route('brands.show', $this);
    }
}
