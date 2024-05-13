<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Forgotten extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private string $code;

    private string $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(string $code, string $email)
    {
        $this->code  = $code;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->view('mails.forgotten', [
            'code'     => $this->code,
            'is_admin' => true,
            'email'    => $this->email,
        ]);
    }
}
