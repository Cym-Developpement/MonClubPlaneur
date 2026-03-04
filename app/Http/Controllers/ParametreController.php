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
    ];

    public function index()
    {
        $params = [];
        foreach ($this->textKeys as $key) {
            $params[$key] = parametre::getValue($key, '');
        }
        $params['club-logo']          = parametre::getValue('club-logo', '');
        $params['backup-purge_auto']  = parametre::getValue('backup-purge_auto', 10);

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

        return redirect('/admin/parametres')->with('success', 'Paramètres enregistrés.');
    }

    private function saveParam(string $key, string $value): void
    {
        $p = parametre::firstOrNew(['nom' => $key]);
        $p->type  = 'string';
        $p->value = $value;
        $p->save();
    }

    private function saveIntParam(string $key, mixed $value): void
    {
        $p = parametre::firstOrNew(['nom' => $key]);
        $p->type  = 'integer';
        $p->value = (string) max(0, (int) $value);
        $p->save();
    }
}
