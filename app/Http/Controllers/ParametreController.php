<?php

namespace App\Http\Controllers;

use App\Models\parametre;
use Illuminate\Http\Request;

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
        'backup-purge_auto',
        'paiement-iban',
        'paiement-cb_actif',
        'paiement-virement_actif',
        'paiement-cheque_actif',
    ];

    public function index()
    {
        $params = [];
        foreach ($this->textKeys as $key) {
            $params[$key] = parametre::getValue($key, '');
        }
        $params['club-logo']                 = parametre::getValue('club-logo', '');
        $params['backup-purge_auto']         = parametre::getValue('backup-purge_auto', 10);
        $params['paiement-iban']             = parametre::getValue('paiement-iban', 'FR76 1333 5004 0108 9253 9002 919');
        $params['paiement-cb_actif']         = (bool) parametre::getValue('paiement-cb_actif', '1');
        $params['paiement-virement_actif']   = (bool) parametre::getValue('paiement-virement_actif', '1');
        $params['paiement-cheque_actif']     = (bool) parametre::getValue('paiement-cheque_actif', '0');

        $autresParams = parametre::whereNotIn('nom', $this->managedKeys)
            ->orderBy('nom')
            ->get()
            ->groupBy(function ($p) {
                $parts = explode('-', $p->nom, 2);
                return count($parts) > 1 ? trim($parts[0]) : 'Divers';
            });

        return view('admin.parametres', compact('params', 'autresParams'));
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

        $this->saveIntParam('backup-purge_auto', $request->input('backup-purge_auto', 10));

        $this->saveParam('paiement-iban', $request->input('paiement-iban', ''));
        $this->saveBoolParam('paiement-cb_actif',        $request->has('paiement-cb_actif'));
        $this->saveBoolParam('paiement-virement_actif',  $request->has('paiement-virement_actif'));
        $this->saveBoolParam('paiement-cheque_actif',    $request->has('paiement-cheque_actif'));

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
}
