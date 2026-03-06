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
     * @param string $userName
     * @param string $filename
     * @param int    $balanceCts  Solde en centimes (négatif = débiteur)
     * @param string $userEmail   Email de l'utilisateur (pour le lien de paiement)
     */
    public function __construct($userName, $filename, public int $balanceCts = 0, public string $userEmail = '')
    {
        $this->userName = $userName;
        $this->filename = $filename;
    }

    public function build()
    {
        $nomCourt   = parametre::getValue('club-nom_court', 'CVVT');
        $nomComplet = parametre::getValue('club-nom_complet', 'Club de Vol à Voile de Thionville');
        $tresorier  = parametre::getValue('club-tresorier', 'Yann Challet');
        $email      = parametre::getValue('club-email', 'yann@cymdev.com');
        $logo       = parametre::getValue('club-logo', '');

        // Lien de paiement si le solde est négatif
        $paymentUrl = null;
        if ($this->balanceCts < 0 && $this->userEmail) {
            $amount = (int) ceil(abs($this->balanceCts) / 100);
            $paymentUrl = config('app.url') . '/cb?mode=paiement&amount=' . $amount . '&email=' . urlencode($this->userEmail);
        }

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
                        'balanceCts'  => $this->balanceCts,
                        'paymentUrl'  => $paymentUrl,
                    ]);
    }
}
