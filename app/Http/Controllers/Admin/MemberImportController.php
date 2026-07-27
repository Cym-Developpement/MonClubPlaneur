<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\MemberImport;
use Illuminate\Http\Request;

/**
 * Import de membres à partir d'un fichier CSV : dépôt du fichier,
 * prévisualisation ligne à ligne, puis création des comptes sélectionnés.
 */
class MemberImportController extends Controller
{
    /**
     * Formulaire de dépôt du fichier.
     */
    public function form()
    {
        return view('admin.importMembres');
    }

    /**
     * Analyse le fichier déposé et affiche la prévisualisation.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'members' => 'required|file|max:5120',
        ], [
            'members.required' => 'Merci de sélectionner un fichier CSV.',
            'members.max'      => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        $result = MemberImport::parse(file_get_contents($request->file('members')->getRealPath()));

        if (! is_null($result['error'])) {
            return redirect('/admin/importMembres')->with('error', $result['error']);
        }

        if (count($result['rows']) === 0) {
            return redirect('/admin/importMembres')->with('error', 'Aucune ligne exploitable dans ce fichier.');
        }

        return view('admin.importMembres', ['rows' => $result['rows']]);
    }

    /**
     * Crée les membres cochés dans la prévisualisation.
     */
    public function save(Request $request)
    {
        $imported = [];
        $ignored  = [];

        foreach ($request->input('import', []) as $json) {
            $row = json_decode($json, true);
            if (! is_array($row) || ! isset($row['email'])) {
                continue;
            }

            // Le fichier a pu être importé entre-temps : on revalide côté serveur.
            $blockers = MemberImport::blockers($row, []);
            if (count($blockers) > 0) {
                $ignored[] = ['row' => $row, 'blockers' => $blockers];
                continue;
            }

            $role       = $request->input('role.' . ($row['idx'] ?? ''), null);
            $imported[] = MemberImport::createUser($row, $role);
        }

        return view('admin.importMembres', ['imported' => $imported, 'ignored' => $ignored]);
    }
}
