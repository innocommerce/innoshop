<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Admin;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InnoShop\Common\Models\Admin;
use InnoShop\Common\Models\BaseModel;

class Token extends BaseModel
{
    protected $table = 'admin_tokens';

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }
}
