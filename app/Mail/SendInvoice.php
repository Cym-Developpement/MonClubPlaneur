<?php

namespace App\Mail;

use App\Models\parametre;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendInvoice extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $invoiceNumber;
    public $pdfPath;

    public function __construct(string $userName, string $invoiceNumber, string $pdfPath)
    {
        $this->userName      = $userName;
        $this->invoiceNumber = $invoiceNumber;
        $this->pdfPath       = $pdfPath;
    }

    public function build()
    {
        $nomCourt   = parametre::getValue('club-nom_court', 'CVVT');
        $nomComplet = parametre::getValue('club-nom_complet', 'Club de Vol à Voile de Thionville');
        $tresorier  = parametre::getValue('club-tresorier', 'Yann Challet');
        $email      = parametre::getValue('club-email', 'yann@cymdev.com');
        $logo       = parametre::getValue('club-logo', '');

        return $this->subject("Facture {$this->invoiceNumber} — {$nomCourt}")
                    ->attachFromStorage($this->pdfPath)
                    ->view('sendInvoice_mail')
                    ->with([
                        'userNameTxt'   => $this->userName,
                        'invoiceNumber' => $this->invoiceNumber,
                        'nomCourt'      => $nomCourt,
                        'nomComplet'    => $nomComplet,
                        'tresorier'     => $tresorier,
                        'emailClub'     => $email,
                        'logo'          => $logo,
                    ]);
    }
}
