<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TOEFLPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    protected $toeflMail;

    public function __construct($toeflMail)
    {
        $this->toeflMail = $toeflMail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.toefl_payment_mail')
        ->with([
            'body' => $this->toeflMail['body'],
            'receipt' => $this->toeflMail['receipt'],
            'guidebook' => $this->toeflMail['guidebook'],
            'confirmationForm' => $this->toeflMail['confirmationForm'],
            'lineGroup' => $this->toeflMail['lineGroup'],
        ])
        ->subject($this->toeflMail['subject']);
    }
}
