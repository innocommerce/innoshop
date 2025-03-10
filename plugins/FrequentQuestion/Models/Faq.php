<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FrequentQuestion\Models;

use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Traits\Translatable;

class Faq extends BaseModel
{
    use Translatable;

    protected $table = 'faqs';

    protected $fillable = ['active'];
}
