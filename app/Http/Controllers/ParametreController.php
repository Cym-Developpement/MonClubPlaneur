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

    public function index()
    {
        $params = [];
        foreach ($this->textKeys as $key) {
            $params[$key] = parametre::getValue($key, '');
        }
        $params['club-logo'] = parametre::getValue('club-logo', '');

        return view('admin.parametres', compact('params'));
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

        return redirect('/admin/parametres')->with('success', 'Paramètres enregistrés.');
    }

    private function saveParam(string $key, string $value): void
    {
        $p = parametre::firstOrNew(['nom' => $key]);
        $p->type  = 'string';
        $p->value = $value;
        $p->save();
    }
}
