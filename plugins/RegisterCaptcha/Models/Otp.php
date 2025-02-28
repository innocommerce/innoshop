<?php

namespace Plugin\RegisterCaptcha\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Plugin\RegisterCaptcha\Notifications\OTPEmailNotification;

class Otp extends Model
{
    use Notifiable;

    public $table = 'register_email_otp';

    /**
     * 订单状态更新通知
     */
    public function notifyAdmin($email, $view, $subject, $content)
    {
        $useQueue = system_setting('use_queue', false);
        if ($useQueue) {
            $this->notify(new OTPEmailNotification($email, $view, $subject, $content));
        } else {
            $this->notifyNow(new OTPEmailNotification($email, $view, $subject, $content));
        }
    }
}
