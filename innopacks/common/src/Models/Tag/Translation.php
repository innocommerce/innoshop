<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Tag;

use InnoShop\Common\Models\BaseModel;

class Translation extends BaseModel
{
    protected $table = 'tag_translations';

    protected $fillable = [
        'tag_id', 'locale', 'name',
    ];
}
