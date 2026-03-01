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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsletterSubscriber extends Model
{
    // Status constants
    public const STATUS_ACTIVE = 'active';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    public const STATUS_BOUNCED = 'bounced';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_UNSUBSCRIBED,
        self::STATUS_BOUNCED,
    ];

    // Source constants
    public const SOURCE_FOOTER = 'footer';

    public const SOURCE_POPUP = 'popup';

    public const SOURCE_CHECKOUT = 'checkout';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCES = [
        self::SOURCE_FOOTER,
        self::SOURCE_POPUP,
        self::SOURCE_CHECKOUT,
        self::SOURCE_MANUAL,
    ];

    protected $fillable = [
        'email',
        'name',
        'customer_id',
        'status',
        'source',
        'subscribed_at',
        'unsubscribed_at',
        'notes',
    ];

    protected $casts = [
        'subscribed_at'   => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Get the customer that owns the subscription.
     *
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Check if subscriber is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Subscribe the user.
     *
     * @return void
     */
    public function subscribe(): void
    {
        $this->status          = self::STATUS_ACTIVE;
        $this->subscribed_at   = now();
        $this->unsubscribed_at = null;
        $this->save();
    }

    /**
     * Unsubscribe the user.
     *
     * @return void
     */
    public function unsubscribe(): void
    {
        $this->status          = self::STATUS_UNSUBSCRIBED;
        $this->unsubscribed_at = now();
        $this->save();
    }
}
