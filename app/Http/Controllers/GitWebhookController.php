<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GitWebhookController extends Controller
{
    public function update(Request $request)
    {
        // Vérification de la signature GitHub
        $secret = config('services.github.webhook_secret');

        if ($secret) {
            $signature = $request->header('X-Hub-Signature-256');

            if (! $signature) {
                return response()->json(['error' => 'Signature manquante'], 401);
            }

            $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

            if (! hash_equals($expected, $signature)) {
                return response()->json(['error' => 'Signature invalide'], 401);
            }
        }

        // On accepte uniquement les push
        $event = $request->header('X-GitHub-Event', 'push');
        if ($event !== 'push') {
            return response()->json(['status' => 'ignored', 'event' => $event]);
        }

        // Lancement de la mise à jour en arrière-plan
        $artisan = base_path('artisan');
        $log     = storage_path('logs/update.log');

        $php = PHP_BINARY;
        exec("{$php} {$artisan} app:update >> {$log} 2>&1 &");

        return response()->json([
            'status'  => 'update started',
            'log'     => 'storage/logs/update.log',
        ]);
    }
}
