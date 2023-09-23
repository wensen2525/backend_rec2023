<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $confirmMail;

    public function __construct($confirmMail)
    {
        $this->confirmMail = $confirmMail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.confirm-mail')
            ->with([
                'body' => $this->confirmMail['body'],
                'link' => $this->confirmMail['link'],
            ])
            ->subject($this->confirmMail['subject']);
    }
}
