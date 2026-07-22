<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Product;

use InnoShop\Common\Models\BaseModel;

class VariantTemplate extends BaseModel
{
    protected $table = 'variant_templates';

    protected $fillable = [
        'name',
        'variables',
        'sku_matrix',
    ];

    protected $casts = [
        'variables'  => 'array',
        'sku_matrix' => 'array',
    ];
}
