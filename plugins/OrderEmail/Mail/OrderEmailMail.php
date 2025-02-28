<?php

namespace Plugin\OrderEmail\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderEmailMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $view;

    public $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($view, $content)
    {
        $this->view    = $view;
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view($this->view, ['content' => $this->content]);
    }
}
