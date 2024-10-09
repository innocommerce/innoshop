<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use InnoShop\Common\Models\Order;
use InnoShop\Front\Mail\OrderUpdateMail;

class OrderUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Order $order;

    private string $fromCode;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Order $order, $fromCode)
    {
        $this->order    = $order;
        $this->fromCode = $fromCode;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        $drivers[] = 'database';

        $mailEngine = system_setting('email_engine');
        if ($mailEngine) {
            $drivers[] = 'mail';
        }

        return $drivers;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return OrderUpdateMail
     */
    public function toMail(mixed $notifiable): OrderUpdateMail
    {
        return (new OrderUpdateMail($this->order, $this->fromCode))
            ->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray(mixed $notifiable): array
    {
        return [];
    }

    /**
     * Save to DB
     *
     * @return Order[]
     */
    public function toDatabase(): array
    {
        return [
            'order' => $this->order,
        ];
    }
}
