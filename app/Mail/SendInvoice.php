<?php

namespace App\Mail;

use App\Models\parametre;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SendInvoice extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $invoiceNumber;
    public $pdfPath;
    public $invoiceId;

    public function __construct(string $userName, string $invoiceNumber, string $pdfPath, ?int $invoiceId = null)
    {
        $this->userName      = $userName;
        $this->invoiceNumber = $invoiceNumber;
        $this->pdfPath       = $pdfPath;
        $this->invoiceId     = $invoiceId;
    }

    public function build()
    {
        $nomCourt   = parametre::getValue('club-nom_court', 'CVVT');
        $nomComplet = parametre::getValue('club-nom_complet', 'Club de Vol à Voile de Thionville');
        $tresorier  = parametre::getValue('club-tresorier', 'Yann Challet');
        $email      = parametre::getValue('club-email', 'yann@cymdev.com');
        $logo       = parametre::getValue('club-logo', '');

        $trackingUrl = $this->invoiceId
            ? URL::signedRoute('invoice.track.opened', ['invoice' => $this->invoiceId])
            : null;

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
                        'trackingUrl'   => $trackingUrl,
                    ]);
    }
}
