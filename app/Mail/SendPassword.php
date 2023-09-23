<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $ticketNumber;
    public $name;

    public function __construct($ticketNumber, $name)
    {
        $this->name = $name;
        $this->ticketNumber = $ticketNumber;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.send-password')
            ->subject('Forgot your password?');
    }
}
