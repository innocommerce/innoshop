<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

class CatalogTranslation extends BaseModel
{
    protected $fillable = [
        'title', 'summary', 'locale', 'meta_title', 'meta_description', 'meta_keywords',
    ];
}
