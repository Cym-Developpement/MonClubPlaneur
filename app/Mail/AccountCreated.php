<?php

namespace App\Mail;

use App\Models\parametre;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email de bienvenue envoyé au membre lors de l'ouverture de son compte.
 */
class AccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function build()
    {
        $nomCourt   = parametre::getValue('club-nom_court', 'Club');
        $nomComplet = parametre::getValue('club-nom_complet', '');
        $emailClub  = parametre::getValue('club-email', '');
        $logo       = parametre::getValue('club-logo', '');

        return $this->subject('Votre compte au ' . $nomCourt . ' est ouvert')
                    ->view('emails.account_created')
                    ->with([
                        'userName'    => $this->user->name,
                        'userEmail'   => $this->user->email,
                        'passwordUrl' => route('password.request'),
                        'appUrl'      => url('/'),
                        'nomCourt'    => $nomCourt,
                        'nomComplet'  => $nomComplet,
                        'emailClub'   => $emailClub,
                        'logo'        => $logo,
                    ]);
    }
}
