<?php

namespace Plugin\OrderEmail\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Plugin\OrderEmail\Notifications\OrderEmailNotification;

class OrderEmail extends Model
{
    use Notifiable;

    public $table = 'orders';

    /**
     * 订单状态更新通知
     */
    public function notifyAdmin($email, $view, $subject, $content)
    {
        $useQueue = system_setting('use_queue', false);
        if ($useQueue) {
            $this->notify(new OrderEmailNotification($email, $view, $subject, $content));
        } else {
            $this->notifyNow(new OrderEmailNotification($email, $view, $subject, $content));
        }
    }
}
