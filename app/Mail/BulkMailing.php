<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BulkMailing extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $mailSubject,
        public string $body,
        public string $nomCourt,
        public string $nomComplet,
        public string $logo,
        public string $emailClub,
        public string $senderName,
    ) {}

    public function build(): static
    {
        return $this->subject($this->mailSubject)
                    ->replyTo($this->emailClub)
                    ->view('emails.bulk_mailing');
    }
}
