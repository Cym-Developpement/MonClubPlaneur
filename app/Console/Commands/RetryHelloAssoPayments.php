<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\HelloAssoPaymentFailedMail;
use App\Models\HelloAssoPendingPayment;
use App\Models\parametre;
use App\Models\transaction;
use App\Models\User;
use App\Services\HelloAssoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RetryHelloAssoPayments extends Command
{
    protected $signature = 'helloasso:retry-payments';
    protected $description = 'Retry pending HelloAsso payments that failed API verification';

    public function handle()
    {
        $helloAssoService = app(HelloAssoService::class);

        $countsByStatus = HelloAssoPendingPayment::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        Log::info('HelloAsso retry: démarrage', [
            'counts_by_status' => $countsByStatus,
        ]);
        $this->info('Statuts en base : ' . json_encode($countsByStatus, JSON_UNESCAPED_UNICODE));

        $excluded = HelloAssoPendingPayment::where('status', 'pending')
            ->where('attempts', '>=', 5)
            ->get(['id', 'payment_id', 'order_id', 'attempts', 'created_at', 'last_attempt_at', 'error_message']);

        if ($excluded->isNotEmpty()) {
            Log::warning('HelloAsso retry: paiements pending exclus (attempts >= 5)', [
                'count' => $excluded->count(),
                'items' => $excluded->toArray(),
            ]);
            $this->warn("{$excluded->count()} paiement(s) pending ignoré(s) car attempts >= 5.");
            foreach ($excluded as $p) {
                $this->warn("  - #{$p->id} payment={$p->payment_id} order={$p->order_id} attempts={$p->attempts} created={$p->created_at}");
            }
        }

        $pendingPayments = HelloAssoPendingPayment::where('status', 'pending')
            ->where('attempts', '<', 5)
            ->get();

        if ($pendingPayments->isEmpty()) {
            $this->info('Aucun paiement à retraiter.');
            return 0;
        }

        $this->info("{$pendingPayments->count()} paiement(s) à retraiter.");

        foreach ($pendingPayments as $pending) {
            $this->processRetry($pending, $helloAssoService);
        }

        return 0;
    }

    private function processRetry(HelloAssoPendingPayment $pending, HelloAssoService $helloAssoService): void
    {
        Log::info('HelloAsso retry: traitement paiement', [
            'id'              => $pending->id,
            'payment_id'      => $pending->payment_id,
            'order_id'        => $pending->order_id,
            'amount'          => $pending->amount,
            'payer_email'     => $pending->payer_email,
            'attempts'        => $pending->attempts,
            'created_at'      => (string) $pending->created_at,
            'last_attempt_at' => (string) $pending->last_attempt_at,
            'error_message'   => $pending->error_message,
        ]);
        $this->line("→ Traitement #{$pending->id} payment={$pending->payment_id} (tentatives actuelles : {$pending->attempts})");

        $pending->attempts++;
        $pending->last_attempt_at = now();

        // Check for duplicate before processing
        if (transaction::where('observation', 'LIKE', '%paiement : ' . $pending->payment_id . '%')->exists()) {
            $pending->status = 'processed';
            $pending->processed_at = now();
            $pending->save();
            Log::info('HelloAsso retry: paiement déjà crédité, marqué comme traité', [
                'payment_id' => $pending->payment_id,
            ]);
            $this->info("Payment {$pending->payment_id} already processed (duplicate).");
            return;
        }

        // Try API verification
        $this->line("  Appel API HelloAsso verifyPayment({$pending->payment_id}, {$pending->order_id})…");
        $verifiedPayment = $helloAssoService->verifyPayment($pending->payment_id, $pending->order_id);

        Log::info('HelloAsso retry: réponse verifyPayment', [
            'payment_id' => $pending->payment_id,
            'success'    => $verifiedPayment !== null,
            'state'      => $verifiedPayment['state'] ?? null,
            'order_id_returned' => $verifiedPayment['order']['id'] ?? null,
        ]);

        if ($verifiedPayment) {
            $this->creditPayment($pending, $verifiedPayment);
            return;
        }

        // API still failing
        $pending->error_message = 'API verification failed on attempt ' . $pending->attempts;

        if ($pending->attempts >= 5) {
            $pending->status = 'failed';
            $pending->save();
            Log::error('HelloAsso retry: paiement en échec définitif après 5 tentatives', [
                'payment_id' => $pending->payment_id,
                'order_id' => $pending->order_id,
                'amount' => $pending->amount,
                'payer_email' => $pending->payer_email,
            ]);
            $this->error("Payment {$pending->payment_id} FAILED after 5 attempts.");
            $this->notifyAdmin($pending);
        } else {
            $pending->save();
            Log::warning('HelloAsso retry: échec tentative ' . $pending->attempts, [
                'payment_id' => $pending->payment_id,
            ]);
            $this->warn("Payment {$pending->payment_id} retry #{$pending->attempts} failed.");
        }
    }

    private function creditPayment(HelloAssoPendingPayment $pending, array $paymentData): void
    {
        $state = $paymentData['state'] ?? 'Unknown';

        if ($state !== 'Authorized') {
            $pending->error_message = "Payment state is '{$state}', not Authorized";
            $pending->save();
            Log::warning('HelloAsso retry: paiement non autorisé', [
                'payment_id' => $pending->payment_id,
                'state' => $state,
            ]);
            $this->warn("Payment {$pending->payment_id} state is '{$state}', skipping.");
            return;
        }

        $user = User::where('email', $pending->payer_email)->first();
        if (!$user) {
            $pending->error_message = 'User not found: ' . $pending->payer_email;
            $pending->save();
            Log::error('HelloAsso retry: utilisateur non trouvé', [
                'payment_id' => $pending->payment_id,
                'payer_email' => $pending->payer_email,
            ]);
            $this->error("Payment {$pending->payment_id}: user not found ({$pending->payer_email}).");
            return;
        }

        $amount = $paymentData['amount'] ?? $pending->amount;
        $paymentId = $pending->payment_id;
        $orderId = $pending->order_id;
        $installmentNumber = $paymentData['installmentNumber'] ?? $pending->installment_number;

        $description = $installmentNumber == 1
            ? 'CB Paiement initial - HelloAsso'
            : "CB Échéance {$installmentNumber} - HelloAsso";
        $observation = 'paiement : ' . $paymentId . ' / Commande : ' . $orderId;

        transaction::add($user->id, $amount, $description, $observation);

        $pending->status = 'processed';
        $pending->processed_at = now();
        $pending->error_message = null;
        $pending->save();

        Log::info('HelloAsso retry: paiement crédité avec succès', [
            'payment_id' => $paymentId,
            'user_id' => $user->id,
            'amount' => $amount,
        ]);
        $this->info("Payment {$paymentId} credited to user {$user->id} ({$amount} cts).");
    }

    private function notifyAdmin(HelloAssoPendingPayment $pending): void
    {
        $adminEmail = parametre::getValue('club-email', '');
        if (! $adminEmail) {
            Log::warning('HelloAsso retry: notif admin non envoyée (club-email vide)', [
                'payment_id' => $pending->payment_id,
            ]);
            return;
        }

        try {
            Mail::to($adminEmail)->send(new HelloAssoPaymentFailedMail($pending));
            Log::info('HelloAsso retry: notif admin envoyée', [
                'payment_id' => $pending->payment_id,
                'to'         => $adminEmail,
            ]);
            $this->info("  → Notification envoyée à {$adminEmail}");
        } catch (\Throwable $e) {
            Log::error('HelloAsso retry: échec envoi notif admin', [
                'payment_id' => $pending->payment_id,
                'to'         => $adminEmail,
                'error'      => $e->getMessage(),
            ]);
            $this->warn("  → Échec envoi notification : {$e->getMessage()}");
        }
    }
}
