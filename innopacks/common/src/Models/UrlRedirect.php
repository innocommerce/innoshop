<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Permanent 301 redirect rule.
 *
 * Rows are written two ways: automatically when a slug-bearing entity is
 * renamed (entity_type/entity_id filled, old_path like `product-demo-a`),
 * or manually for deleted content via `redirects:add` / the future panel
 * UI (entity columns null). Paths are stored locale-less so one rule serves
 * every locale prefix; new_path may also be `/` (home) or an absolute URL.
 */
class UrlRedirect extends Model
{
    protected $table = 'url_redirects';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'old_path',
        'new_path',
        'hits',
    ];

    protected $casts = [
        'entity_id' => 'integer',
        'hits'      => 'integer',
    ];
}
