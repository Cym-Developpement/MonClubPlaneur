<?php

namespace App\Http\Controllers;

use App\Models\parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ParametreController extends Controller
{
    private array $textKeys = [
        'club-nom_court',
        'club-nom_complet',
        'club-tresorier',
        'club-email',
    ];

    /** Clés gérées par les sections dédiées du formulaire — exclues des "Autres paramètres". */
    private array $managedKeys = [
        'club-nom_court',
        'club-nom_complet',
        'club-tresorier',
        'club-email',
        'club-logo',
        'pwa-utiliser_logo_club',
        'backup-purge_auto',
        'paiement-iban',
        'paiement-cb_actif',
        'paiement-virement_actif',
        'paiement-cheque_actif',
        'paiement-especes_actif',
    ];

    public function index()
    {
        $params = [];
        foreach ($this->textKeys as $key) {
            $params[$key] = parametre::getValue($key, '');
        }
        $params['club-logo']                 = parametre::getValue('club-logo', '');
        $params['pwa-utiliser_logo_club']    = (bool) parametre::getValue('pwa-utiliser_logo_club', false);
        $params['backup-purge_auto']         = parametre::getValue('backup-purge_auto', 10);
        $params['paiement-iban']             = parametre::getValue('paiement-iban', 'FR76 1333 5004 0108 9253 9002 919');
        $params['paiement-cb_actif']         = (bool) parametre::getValue('paiement-cb_actif', '1');
        $params['paiement-virement_actif']   = (bool) parametre::getValue('paiement-virement_actif', '1');
        $params['paiement-cheque_actif']     = (bool) parametre::getValue('paiement-cheque_actif', '0');
        $params['paiement-especes_actif']    = (bool) parametre::getValue('paiement-especes_actif', '0');

        $autresParams = parametre::whereNotIn('nom', $this->managedKeys)
            ->orderBy('nom')
            ->get()
            ->groupBy(function ($p) {
                $parts = explode('-', $p->nom, 2);
                return count($parts) > 1 ? trim($parts[0]) : 'Divers';
            });

        $cronEvents = array_map(
            fn ($e) => ['key' => $e['key'], 'label' => $e['label'], 'expression' => $e['expression']],
            $this->cronEvents()
        );

        return view('admin.parametres', compact('params', 'autresParams', 'cronEvents'));
    }

    private function cronEvents(): array
    {
        $schedule = app(\Illuminate\Console\Scheduling\Schedule::class);
        $list = [];
        foreach ($schedule->events() as $i => $event) {
            $label = $event->description ?: $event->command ?: 'Tâche planifiée #' . ($i + 1);
            $key   = Str::slug($label) ?: 'event-' . $i;
            $list[] = [
                'key'        => $key,
                'label'      => $label,
                'expression' => $event->expression,
                'event'      => $event,
            ];
        }
        return $list;
    }

    public function update(Request $request)
    {
        foreach ($this->textKeys as $key) {
            $this->saveParam($key, $request->input($key, ''));
        }

        if ($request->hasFile('club-logo')) {
            $file    = $request->file('club-logo');
            $mime    = $file->getMimeType();
            $base64  = base64_encode(file_get_contents($file->getRealPath()));
            $this->saveParam('club-logo', 'data:' . $mime . ';base64,' . $base64);
        }

        $pwaToggle = $request->has('pwa-utiliser_logo_club');
        $this->saveBoolParam('pwa-utiliser_logo_club', $pwaToggle);

        if ($pwaToggle) {
            $logoDataUri = parametre::getValue('club-logo', '');

            if ($logoDataUri && str_starts_with($logoDataUri, 'data:')) {
                $raw = base64_decode(explode(',', $logoDataUri, 2)[1] ?? '');
                if ($raw) {
                    $this->generatePwaIcons($raw);
                }
            }
        }

        $this->saveIntParam('backup-purge_auto', $request->input('backup-purge_auto', 10));

        $this->saveParam('paiement-iban', $request->input('paiement-iban', ''));
        $this->saveBoolParam('paiement-cb_actif',        $request->has('paiement-cb_actif'));
        $this->saveBoolParam('paiement-virement_actif',  $request->has('paiement-virement_actif'));
        $this->saveBoolParam('paiement-cheque_actif',    $request->has('paiement-cheque_actif'));
        $this->saveBoolParam('paiement-especes_actif',   $request->has('paiement-especes_actif'));

        return redirect('/admin/parametres')->with('success', 'Paramètres enregistrés.');
    }

    private function saveParam(string $key, string $value): void
    {
        $p = parametre::firstOrNew(['nom' => $key]);
        $p->type  = 'string';
        $p->value = $value;
        $p->save();
    }

    public function updateAutres(Request $request): \Illuminate\Http\RedirectResponse
    {
        foreach ($request->input('autres', []) as $id => $value) {
            $p = parametre::find((int) $id);
            if (! $p || in_array($p->nom, $this->managedKeys)) {
                continue;
            }
            $p->value = match ($p->type) {
                'integer' => (string) (int) $value,
                'double'  => (string) (float) $value,
                'boolean' => $value ? '1' : '0',
                default   => (string) $value,
            };
            $p->save();
        }

        return redirect('/admin/parametres')->with('success', 'Paramètres enregistrés.');
    }

    private function saveIntParam(string $key, mixed $value): void
    {
        $p = parametre::firstOrNew(['nom' => $key]);
        $p->type  = 'integer';
        $p->value = (string) max(0, (int) $value);
        $p->save();
    }

    private function saveBoolParam(string $key, bool $value): void
    {
        $p = parametre::firstOrNew(['nom' => $key]);
        $p->type  = 'boolean';
        $p->value = $value ? '1' : '0';
        $p->save();
    }

    public function runCron(?string $key = null): \Illuminate\Http\JsonResponse
    {
        $events = $this->cronEvents();

        if ($key !== null) {
            $events = array_values(array_filter($events, fn ($e) => $e['key'] === $key));
            if (empty($events)) {
                return response()->json([
                    'exit_code' => 1,
                    'output'    => "Tâche introuvable : {$key}",
                ], 404);
            }
        }

        $outputs  = [];
        $exitCode = 0;

        foreach ($events as $entry) {
            $event    = $entry['event'];
            $tempFile = null;

            try {
                if (! ($event instanceof \Illuminate\Console\Scheduling\CallbackEvent)) {
                    $tempFile = tempnam(sys_get_temp_dir(), 'cron-output-');
                    $event->sendOutputTo($tempFile);
                }

                $event->run(app());
                $outputs[] = "✓ {$entry['label']}";

                if ($tempFile && is_file($tempFile)) {
                    $captured = trim(file_get_contents($tempFile));
                    if ($captured !== '') {
                        $outputs[] = $captured;
                    }
                }
            } catch (\Throwable $e) {
                $exitCode  = 1;
                $outputs[] = "✗ {$entry['label']} : {$e->getMessage()}";
            } finally {
                if ($tempFile && is_file($tempFile)) {
                    @unlink($tempFile);
                }
            }
        }

        if (empty($outputs)) {
            $outputs[] = 'Aucune tâche planifiée définie.';
        }

        return response()->json([
            'exit_code' => $exitCode,
            'output'    => implode("\n", $outputs),
        ]);
    }

    private function generatePwaIcons(string $imageData): void
    {
        $source = @imagecreatefromstring($imageData);
        if (! $source) {
            return;
        }

        $icons = [
            public_path('icons/icon-512x512.png')     => 512,
            public_path('icons/icon-192x192.png')     => 192,
            public_path('icons/apple-touch-icon.png') => 180,
            public_path('favicon-32x32.png')          => 32,
            public_path('favicon-16x16.png')          => 16,
        ];

        // Activer la transparence pour les PNG
        imagesavealpha($source, true);

        foreach ($icons as $path => $size) {
            $resized = imagescale($source, $size, $size, IMG_BICUBIC);
            if ($resized) {
                imagesavealpha($resized, true);
                imagepng($resized, $path);
                imagedestroy($resized);
            }
        }

        imagedestroy($source);
    }
}
