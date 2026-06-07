<?php

namespace App\Mail;

use App\Models\parametre;
use App\Models\ProductPurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProductPurchaseAdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ProductPurchase $purchase)
    {
    }

    public function build()
    {
        $nomCourt = parametre::getValue('club-nom_court', 'Club');
        $adminUrl = config('app.url') . '/admin/produits';

        return $this->subject('[' . $nomCourt . '] Nouvel achat en ligne : ' . $this->purchase->product_title)
                    ->view('emails.product_admin_notification')
                    ->with([
                        'purchase' => $this->purchase,
                        'nomCourt' => $nomCourt,
                        'adminUrl' => $adminUrl,
                    ]);
    }
}
