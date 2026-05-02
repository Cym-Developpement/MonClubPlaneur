<?php

namespace App\Mail;

use App\Models\HelloAssoPendingPayment;
use App\Models\parametre;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HelloAssoPaymentFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public HelloAssoPendingPayment $pending)
    {
    }

    public function build()
    {
        $nomCourt   = parametre::getValue('club-nom_court', 'Club');
        $nomComplet = parametre::getValue('club-nom_complet', '');
        $logo       = parametre::getValue('club-logo', '');
        $adminUrl   = url('/admin/parametres');

        return $this->subject("[{$nomCourt}] Paiement HelloAsso à valider manuellement")
                    ->view('emails.helloasso_payment_failed')
                    ->with([
                        'pending'    => $this->pending,
                        'nomCourt'   => $nomCourt,
                        'nomComplet' => $nomComplet,
                        'logo'       => $logo,
                        'adminUrl'   => $adminUrl,
                    ]);
    }
}
