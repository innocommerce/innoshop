<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Page;

use InnoShop\Common\Models\BaseModel;

class Translation extends BaseModel
{
    protected $table = 'page_translations';

    protected $fillable = [
        'page_id', 'locale', 'title', 'content', 'template', 'meta_title', 'meta_description', 'meta_keywords',
    ];
}
