<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLog;
use App\Models\Invoice;

class InvoiceTrackingController extends Controller
{
    public function opened(Invoice $invoice)
    {
        $email = $invoice->user->email ?? '?';
        AuditLog::log("facture {$invoice->invoiceNumber} ouverte par {$email}");

        $pixel = base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');

        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Content-Length', (string) strlen($pixel))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
