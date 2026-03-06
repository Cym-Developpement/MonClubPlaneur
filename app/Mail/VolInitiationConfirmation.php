<?php

namespace App\Mail;

use App\Models\parametre;
use App\Models\VolInitiation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VolInitiationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public VolInitiation $vi)
    {
    }

    public function build()
    {
        $nomCourt   = parametre::getValue('club-nom_court', 'Club');
        $nomComplet = parametre::getValue('club-nom_complet', '');
        $emailClub  = parametre::getValue('club-email', '');
        $logo       = parametre::getValue('club-logo', '');

        return $this->subject('Confirmation de votre bon de vol d\'initiation — ' . $nomCourt)
                    ->view('emails.vi_confirmation')
                    ->with([
                        'vi'         => $this->vi,
                        'nomCourt'   => $nomCourt,
                        'nomComplet' => $nomComplet,
                        'emailClub'  => $emailClub,
                        'logo'       => $logo,
                    ]);
    }
}
