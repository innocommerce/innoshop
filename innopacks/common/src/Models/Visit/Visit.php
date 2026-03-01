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

class Visit extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'visits';

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
        'locale',
        'first_visited_at',
        'last_visited_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'first_visited_at' => 'datetime',
        'last_visited_at'  => 'datetime',
    ];

    /**
     * Get the customer that owns the visit.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get visit events relationship.
     *
     * @return HasMany
     */
    public function visitEvents(): HasMany
    {
        return $this->hasMany(VisitEvent::class, 'session_id', 'session_id');
    }

    /**
     * Get page views count (from visit_events).
     *
     * @return int
     */
    public function getPageViewsAttribute(): int
    {
        // Remove page_views from attributes to prevent conflicts with database column
        if (array_key_exists('page_views', $this->attributes)) {
            unset($this->attributes['page_views']);
        }

        // Remove from original to prevent dirty check
        if (array_key_exists('page_views', $this->original)) {
            unset($this->original['page_views']);
        }

        if ($this->relationLoaded('visitEvents')) {
            return $this->visitEvents
                ->where('event_type', VisitEvent::TYPE_PRODUCT_VIEW)
                ->count();
        }

        return VisitEvent::where('session_id', $this->session_id)
            ->where('event_type', VisitEvent::TYPE_PRODUCT_VIEW)
            ->count();
    }

    /**
     * Get visit duration in seconds (from visit_events).
     *
     * @return int
     */
    public function getVisitDurationAttribute(): int
    {
        // If visit_duration exists as a database column, ignore it and calculate from events
        if (array_key_exists('visit_duration', $this->attributes)) {
            unset($this->attributes['visit_duration']);
        }

        $events = $this->relationLoaded('visitEvents')
            ? $this->visitEvents
            : VisitEvent::where('session_id', $this->session_id)->orderBy('created_at')->get();

        if ($events->count() < 2) {
            return 0;
        }

        $firstEvent = $events->first();
        $lastEvent  = $events->last();

        return $lastEvent->created_at->diffInSeconds($firstEvent->created_at);
    }

    /**
     * Get conversion event (highest priority).
     *
     * @return string|null
     */
    public function getConversionEventAttribute(): ?string
    {
        $priority = [
            VisitEvent::TYPE_PAYMENT_COMPLETED => 4,
            VisitEvent::TYPE_ORDER_PLACED      => 3,
            VisitEvent::TYPE_CHECKOUT_START    => 2,
            VisitEvent::TYPE_REGISTER          => 1,
        ];

        if ($this->relationLoaded('visitEvents')) {
            $events = $this->visitEvents->whereIn('event_type', array_keys($priority));
        } else {
            $events = VisitEvent::where('session_id', $this->session_id)
                ->whereIn('event_type', array_keys($priority))
                ->get();
        }

        if ($events->isEmpty()) {
            return null;
        }

        $event = $events->sortByDesc(function ($event) use ($priority) {
            return $priority[$event->event_type] ?? 0;
        })->first();

        return $event->event_type;
    }

    /**
     * Get conversion event display name.
     *
     * @return string|null
     */
    public function getConversionEventDisplayAttribute(): ?string
    {
        $eventType = $this->conversion_event;

        if (! $eventType) {
            return null;
        }

        $types = [
            VisitEvent::TYPE_PRODUCT_VIEW      => trans('panel/visit.event_product_view'),
            VisitEvent::TYPE_ADD_TO_CART       => trans('panel/visit.event_add_to_cart'),
            VisitEvent::TYPE_CHECKOUT_START    => trans('panel/visit.event_checkout_start'),
            VisitEvent::TYPE_ORDER_PLACED      => trans('panel/visit.event_order_placed'),
            VisitEvent::TYPE_PAYMENT_COMPLETED => trans('panel/visit.event_payment_completed'),
            VisitEvent::TYPE_REGISTER          => trans('panel/visit.event_register'),
        ];

        return $types[$eventType] ?? $eventType;
    }

    /**
     * Get device type display name.
     *
     * @return string
     */
    public function getDeviceTypeDisplayAttribute(): string
    {
        $types = [
            'desktop' => trans('panel/visit.device_desktop'),
            'mobile'  => trans('panel/visit.device_mobile'),
            'tablet'  => trans('panel/visit.device_tablet'),
        ];

        return $types[$this->device_type] ?? $this->device_type;
    }
}
