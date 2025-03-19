<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     XING GUI YU <xingguiyu@foxmail.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Cloak\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use InnoShop\Common\Models\BaseModel;

class Cloak extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'target_url',
        'safe_url',
        'is_active',
        'ip_filters',
        'country_filters',
        'user_agent_filters',
        'referrer_filters',
        'detect_bots',
        'one_time_redirect',
        'visits_count',
        'redirects_count',
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'detect_bots'        => 'boolean',
        'one_time_redirect'  => 'boolean',
        'ip_filters'         => 'array',
        'country_filters'    => 'array',
        'user_agent_filters' => 'array',
        'referrer_filters'   => 'array',
        'visits_count'       => 'integer',
        'redirects_count'    => 'integer',
    ];

    public static function boot()
    {
        parent::boot();
    }

    /**
     * Increment visits count
     *
     * @return void
     */
    public function incrementVisits(): void
    {
        $this->increment('visits_count');
    }

    /**
     * Increment redirects count
     *
     * @return void
     */
    public function incrementRedirects(): void
    {
        $this->increment('redirects_count');
    }

    /**
     * Get the active cloaks
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getActive()
    {
        return self::where('is_active', true)->get();
    }
}
