<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Exception;
use InnoShop\Common\Traits\Translatable;

class Page extends BaseModel
{
    use Translatable;

    protected $fillable = [
        'slug', 'viewed', 'show_breadcrumb', 'active',
    ];

    public $appends = [
        'url',
    ];

    /**
     * Get slug url link.
     *
     * @return string
     * @throws Exception
     */
    public function getUrlAttribute(): string
    {
        if ($this->slug) {
            return front_route('pages.'.$this->slug);
        }

        return front_route('pages.show', $this);
    }
}
