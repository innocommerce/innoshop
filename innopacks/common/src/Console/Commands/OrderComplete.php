<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Console\Commands;

use Illuminate\Console\Command;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Services\StateMachineService;

class OrderComplete extends Command
{
    protected $signature = 'order:complete';

    protected $description = 'Automatic Order Completion After Timeout';

    public function handle(): void
    {
        $this->completeOrders();
    }

    /**
     * Complete orders that have been shipped but not completed after a certain period
     */
    private function completeOrders(): void
    {
        $autoDays = (int) system_setting('order_auto_complete_days', 7);
        $deadline = now()->subDays($autoDays);

        $orders = Order::query()
            ->where('status', StateMachineService::SHIPPED)
            ->whereHas('shipments', function ($query) use ($deadline) {
                $query->where('created_at', '<', $deadline);
            })
            ->get();

        $this->info("Found {$orders->count()} orders to be completed automatically");

        foreach ($orders as $order) {
            try {
                $comment = trans('common/order.auto_complete_message', [], $order->locale);

                StateMachineService::getInstance($order)->changeStatus(StateMachineService::COMPLETED, $comment);

                $this->info("Order {$order->number} has been completed automatically");
            } catch (\Exception $e) {
                $this->error("Failed to complete order {$order->number}: {$e->getMessage()}");
            }
        }
    }
}
