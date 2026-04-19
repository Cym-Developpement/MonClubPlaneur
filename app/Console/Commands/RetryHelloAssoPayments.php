<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HelloAssoPendingPayment;
use App\Models\transaction;
use App\Models\User;
use App\Services\HelloAssoService;
use Illuminate\Support\Facades\Log;

class RetryHelloAssoPayments extends Command
{
    protected $signature = 'helloasso:retry-payments';
    protected $description = 'Retry pending HelloAsso payments that failed API verification';

    public function handle()
    {
        $helloAssoService = app(HelloAssoService::class);

        $pendingPayments = HelloAssoPendingPayment::where('status', 'pending')
            ->where('attempts', '<', 5)
            ->get();

        if ($pendingPayments->isEmpty()) {
            $this->info('No pending payments to retry.');
            return 0;
        }

        $this->info("Found {$pendingPayments->count()} pending payment(s) to retry.");

        foreach ($pendingPayments as $pending) {
            $this->processRetry($pending, $helloAssoService);
        }

        return 0;
    }

    private function processRetry(HelloAssoPendingPayment $pending, HelloAssoService $helloAssoService): void
    {
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
        $verifiedPayment = $helloAssoService->verifyPayment($pending->payment_id, $pending->order_id);

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
}
