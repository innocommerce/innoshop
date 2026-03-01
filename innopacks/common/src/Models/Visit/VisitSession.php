<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models\Visit;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InnoShop\Common\Models\BaseModel;
use InnoShop\Common\Models\Customer;

class VisitSession extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'visit_sessions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'session_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'customer_id',
        'ip_address',
        'user_agent',
        'country_code',
        'country_name',
        'city',
        'referrer',
        'device_type',
        'browser',
        'os',
        'first_visit_at',
        'last_visit_at',
        'page_views',
        'visit_duration',
        'conversion_event',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'first_visit_at' => 'datetime',
        'last_visit_at'  => 'datetime',
        'page_views'     => 'integer',
        'visit_duration' => 'integer',
    ];

    /**
     * Get the customer that owns the visit session.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the visits for the session.
     *
     * @return HasMany
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class, 'session_id', 'session_id');
    }

    /**
     * Check if session has conversion event.
     *
     * @return bool
     */
    public function hasConversion(): bool
    {
        return ! empty($this->conversion_event);
    }

    /**
     * Get conversion event display name.
     *
     * @return string
     */
    public function getConversionEventDisplayAttribute(): string
    {
        $events = [
            'register' => trans('panel/visit.conversion_register'),
            'checkout' => trans('panel/visit.conversion_checkout'),
            'order'    => trans('panel/visit.conversion_order'),
            'payment'  => trans('panel/visit.conversion_payment'),
        ];

        return $events[$this->conversion_event] ?? $this->conversion_event;
    }
}
