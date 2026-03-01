<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use InnoShop\Common\Models\Visit\VisitEvent;

class EventTrackingService
{
    /**
     * Track an event
     *
     * @param  string  $eventType
     * @param  array  $eventData
     * @param  Request|null  $request
     * @param  int|null  $customerId
     * @param  string|null  $source  'web' or 'api'
     * @return VisitEvent|null
     */
    public function track(string $eventType, array $eventData = [], ?Request $request = null, ?int $customerId = null, ?string $source = null): ?VisitEvent
    {
        try {
            $request = $request ?? request();

            // Detect source if not provided
            if ($source === null && $request) {
                $source = $this->detectSource($request);
            }

            $source = $source ?? 'web';

            // Add source to event data for tracking
            $eventData['source'] = $source;

            $sessionId = Session::getId();

            if (empty($sessionId)) {
                return null;
            }

            // Get customer ID from request if not provided
            if ($customerId === null && $request) {
                $customerId = current_customer()?->id;
            }

            // Get IP address
            $ipAddress = $request ? $this->getClientIp($request) : null;

            // Create event record (visits and visit_events are independent, linked only by session_id)
            $event = VisitEvent::create([
                'session_id'  => $sessionId,
                'event_type'  => $eventType,
                'event_data'  => $eventData,
                'customer_id' => $customerId,
                'ip_address'  => $ipAddress,
                'page_url'    => $request ? $request->fullUrl() : null,
                'referrer'    => $request ? $request->header('referer') : null,
            ]);

            return $event;
        } catch (Exception $e) {
            Log::error('EventTrackingService: Failed to track event', [
                'event_type' => $eventType,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Detect request source (web or api)
     *
     * @param  Request  $request
     * @return string
     */
    private function detectSource(Request $request): string
    {
        $path = $request->path();

        // Check if it's an API route
        if (str_starts_with($path, 'api/') || $request->is('api/*')) {
            return 'api';
        }

        return 'web';
    }

    /**
     * Track product view event
     *
     * @param  int  $productId
     * @param  Request|null  $request
     * @return VisitEvent|null
     */
    public function trackProductView(int $productId, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_PRODUCT_VIEW, [
            'product_id' => $productId,
        ], $request);
    }

    /**
     * Track add to cart event
     *
     * @param  int  $productId
     * @param  int  $quantity
     * @param  float|null  $price
     * @param  Request|null  $request
     * @return VisitEvent|null
     */
    public function trackAddToCart(int $productId, int $quantity = 1, ?float $price = null, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_ADD_TO_CART, [
            'product_id' => $productId,
            'quantity'   => $quantity,
            'price'      => $price,
        ], $request);
    }

    /**
     * Track checkout start event
     *
     * @param  Request|null  $request
     * @return VisitEvent|null
     */
    public function trackCheckoutStart(?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_CHECKOUT_START, [], $request);
    }

    /**
     * Track order placed event
     *
     * @param  int  $orderId
     * @param  string  $orderNumber
     * @param  float  $total
     * @param  Request|null  $request
     * @return VisitEvent|null
     */
    public function trackOrderPlaced(int $orderId, string $orderNumber, float $total, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_ORDER_PLACED, [
            'order_id'     => $orderId,
            'order_number' => $orderNumber,
            'total'        => $total,
        ], $request);
    }

    /**
     * Track payment completed event
     *
     * @param  int  $orderId
     * @param  string  $orderNumber
     * @param  float  $amount
     * @param  Request|null  $request
     * @return VisitEvent|null
     */
    public function trackPaymentCompleted(int $orderId, string $orderNumber, float $amount, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_PAYMENT_COMPLETED, [
            'order_id'     => $orderId,
            'order_number' => $orderNumber,
            'amount'       => $amount,
        ], $request);
    }

    /**
     * Track register event
     *
     * @param  int  $customerId
     * @param  Request|null  $request
     * @return VisitEvent|null
     */
    public function trackRegister(int $customerId, ?Request $request = null): ?VisitEvent
    {
        return $this->track(VisitEvent::TYPE_REGISTER, [
            'customer_id' => $customerId,
        ], $request, $customerId);
    }

    /**
     * Get client IP address
     *
     * @param  Request  $request
     * @return string
     */
    private function getClientIp(Request $request): string
    {
        $ip = $request->ip();

        // Handle IPv6 mapped IPv4 addresses
        if (str_starts_with($ip, '::ffff:')) {
            $ip = substr($ip, 7);
        }

        return $ip;
    }
}
