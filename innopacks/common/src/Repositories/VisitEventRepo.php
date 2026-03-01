<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use InnoShop\Common\Models\Visit\VisitEvent;

class VisitEventRepo extends BaseRepo
{
    /**
     * Model class name
     *
     * @var string
     */
    protected string $model = VisitEvent::class;

    /**
     * Get conversion funnel data based on events
     *
     * @param  array  $filters
     * @return array
     */
    public function getConversionFunnel(array $filters = []): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate   = $filters['end_date'] ?? Carbon::now();

        $query = $this->modelQuery()
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Count unique sessions for each event type
        $productViews = (clone $query)
            ->where('event_type', VisitEvent::TYPE_PRODUCT_VIEW)
            ->distinct('session_id')
            ->count('session_id');

        $addToCarts = (clone $query)
            ->where('event_type', VisitEvent::TYPE_ADD_TO_CART)
            ->distinct('session_id')
            ->count('session_id');

        $checkoutStarts = (clone $query)
            ->where('event_type', VisitEvent::TYPE_CHECKOUT_START)
            ->distinct('session_id')
            ->count('session_id');

        $orderPlaced = (clone $query)
            ->where('event_type', VisitEvent::TYPE_ORDER_PLACED)
            ->distinct('session_id')
            ->count('session_id');

        $paymentCompleted = (clone $query)
            ->where('event_type', VisitEvent::TYPE_PAYMENT_COMPLETED)
            ->distinct('session_id')
            ->count('session_id');

        $register = (clone $query)
            ->where('event_type', VisitEvent::TYPE_REGISTER)
            ->distinct('session_id')
            ->count('session_id');

        // Calculate conversion rates
        return [
            'product_views'     => $productViews,
            'add_to_carts'      => $addToCarts,
            'checkout_starts'   => $checkoutStarts,
            'order_placed'      => $orderPlaced,
            'payment_completed' => $paymentCompleted,
            'register'          => $register,
            'conversion_rates'  => [
                'product_to_cart'    => $productViews > 0 ? round(($addToCarts / $productViews) * 100, 2) : 0,
                'cart_to_checkout'   => $addToCarts > 0 ? round(($checkoutStarts / $addToCarts) * 100, 2) : 0,
                'checkout_to_order'  => $checkoutStarts > 0 ? round(($orderPlaced / $checkoutStarts) * 100, 2) : 0,
                'order_to_payment'   => $orderPlaced > 0 ? round(($paymentCompleted / $orderPlaced) * 100, 2) : 0,
                'overall_conversion' => $productViews > 0 ? round(($paymentCompleted / $productViews) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Get events by type
     *
     * @param  string  $eventType
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function getEventsByType(string $eventType, array $filters = []): LengthAwarePaginator
    {
        $query = $this->builder($filters)
            ->where('event_type', $eventType)
            ->orderByDesc('created_at');

        return $query->paginate();
    }

    /**
     * Build query builder
     *
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $query = $this->modelQuery();

        if (isset($filters['session_id'])) {
            $query->where('session_id', $filters['session_id']);
        }

        if (isset($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (isset($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['start_date']) && ! empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date'].' 00:00:00');
        }

        if (isset($filters['end_date']) && ! empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date'].' 23:59:59');
        }

        return $query;
    }
}
