<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Visit;

use InnoShop\Common\Models\BaseModel;

class VisitDaily extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'visit_daily';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'date';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'pv',
        'uv',
        'ip',
        'new_visitors',
        'bounces',
        'avg_duration',
        'desktop_pv',
        'mobile_pv',
        'tablet_pv',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get total PV across all devices.
     *
     * @return int
     */
    public function getTotalPvAttribute(): int
    {
        return $this->desktop_pv + $this->mobile_pv + $this->tablet_pv;
    }
}
