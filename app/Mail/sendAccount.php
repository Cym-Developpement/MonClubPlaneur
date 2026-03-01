<?php

namespace App\Mail;

use App\Models\parametre;
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
        $nomCourt   = parametre::getValue('club-nom_court', 'CVVT');
        $nomComplet = parametre::getValue('club-nom_complet', 'Club de Vol à Voile de Thionville');
        $tresorier  = parametre::getValue('club-tresorier', 'Yann Challet');
        $email      = parametre::getValue('club-email', 'yann@cymdev.com');
        $logo       = parametre::getValue('club-logo', '');

        return $this->subject('Votre compte au ' . $nomCourt)
                    ->attachFromStorage($this->filename)
                    ->view('sendAccount_mail')
                    ->with([
                        'userNameTxt' => $this->userName,
                        'nomCourt'    => $nomCourt,
                        'nomComplet'  => $nomComplet,
                        'tresorier'   => $tresorier,
                        'emailClub'   => $email,
                        'logo'        => $logo,
                    ]);
    }
}
