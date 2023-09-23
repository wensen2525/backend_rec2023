<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nim;
    public $password;
    public $name;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($nim, $password, $name)
    {
        $this->nim = $nim;
        $this->password = $password;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.register-mail')
            ->subject('New Account Created');
    }
}
