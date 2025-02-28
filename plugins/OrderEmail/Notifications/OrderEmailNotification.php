<?php

namespace Plugin\OrderEmail\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Plugin\OrderEmail\Mail\OrderEmailMail;

class OrderEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $email = null;

    private $view = null;

    private $content = null;

    private $subject = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($email, $view, $subject, $content)
    {
        $this->email   = $email;
        $this->view    = $view;
        $this->content = $content;
        $this->subject = $subject;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $drivers[]  = 'database';
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
     * @return OrderEmailMail
     */
    public function toMail($notifiable)
    {
        return (new OrderEmailMail($this->view, $this->content))->subject($this->subject)->to($this->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [//
        ];
    }

    /**
     * 保存到 DB
     * @return
     */
    public function toDatabase()
    {
        return [];
    }
}
