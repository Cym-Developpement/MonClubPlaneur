<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'idUser', 'type', 'invoiceNumber', 'sequence', 'relatedInvoiceId',
        'periodStart', 'periodEnd', 'totalAmount', 'emittedAt', 'pdfPath', 'pdfHash',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'idUser');
    }

    public function transactions()
    {
        return $this->hasMany(transaction::class, 'invoiceId');
    }

    /** Avoir qui annule cette facture, s'il existe */
    public function avoir()
    {
        return $this->hasOne(Invoice::class, 'relatedInvoiceId');
    }

    /** Facture annulée par cet avoir */
    public function relatedInvoice()
    {
        return $this->belongsTo(Invoice::class, 'relatedInvoiceId');
    }

    public function isCancelled(): bool
    {
        return $this->avoir()->exists();
    }

    public static function buildNumber(int $seq, string $type = 'facture', ?int $periodEnd = null): string
    {
        $format = $type === 'avoir'
            ? parametre::getValue('invoice-format-avoir', 'AVYYYYMM-{ID}')
            : parametre::getValue('invoice-format', 'FYYYYMM-{ID}');

        $ts = $periodEnd ?? time();

        return str_replace(
            ['YYYY', 'MM', '{ID}'],
            [date('Y', $ts), date('m', $ts), str_pad($seq, 4, '0', STR_PAD_LEFT)],
            $format
        );
    }
}
