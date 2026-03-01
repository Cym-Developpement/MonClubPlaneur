<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class sendAccount extends Mailable
{
    use Queueable, SerializesModels;
    public $userName;
    public $fileName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($userName, $filename)
    {
        $this->userName = $userName;
        $this->filename = $filename;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Votre compte au CVVT')
                    ->attachFromStorage($this->filename)
                    ->view('sendAccount_mail')
                    ->with([
                        'userNameTxt' => $this->userName
                    ]);
    }
}
