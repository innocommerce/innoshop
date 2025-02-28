<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InnoChat\Models;

use Illuminate\Database\Eloquent\Model;

class OpenaiLog extends Model
{
    public $timestamps = true;

    protected $table = 'openai_logs';

    protected $fillable = [
        'user_id', 'question', 'answer', 'request_ip', 'user_agent',
    ];

    protected $appends = ['created_format'];

    public function getCreatedFormatAttribute()
    {
        return $this->created_at->format('Y-m-d H:i:s');
    }
}
