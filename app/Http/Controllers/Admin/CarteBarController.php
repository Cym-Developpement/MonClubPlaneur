<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\parametre;
use App\Services\CarteBarService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CarteBarController extends Controller
{
    /**
     * Enregistre le format des cartes de bar et celui de la planche imprimable.
     */
    public function saveConfig(Request $request)
    {
        $data = $request->validate([
            'largeur_mm'    => 'required|integer|min:20|max:420',
            'hauteur_mm'    => 'required|integer|min:20|max:420',
            'format_page'   => 'required|in:' . implode(',', CarteBarService::formats()),
            'orientation'   => 'required|in:portrait,paysage',
            'marge_mm'      => 'required|integer|min:0|max:50',
            'espacement_mm' => 'required|integer|min:0|max:50',
        ]);

        [$pageW, $pageH] = CarteBarService::pageSize($data['format_page'], $data['orientation']);
        if ($data['largeur_mm'] > $pageW - 2 * $data['marge_mm']
            || $data['hauteur_mm'] > $pageH - 2 * $data['marge_mm']) {
            return redirect('/admin/parametres#cartes-bar')
                ->with('error', 'La carte est plus grande que la zone imprimable de la planche choisie.');
        }

        $this->saveInt('cartebar-largeur_mm', $data['largeur_mm']);
        $this->saveInt('cartebar-hauteur_mm', $data['hauteur_mm']);
        $this->saveStr('cartebar-format_page', $data['format_page']);
        $this->saveStr('cartebar-orientation', $data['orientation']);
        $this->saveInt('cartebar-marge_mm', $data['marge_mm']);
        $this->saveInt('cartebar-espacement_mm', $data['espacement_mm']);

        return redirect('/admin/parametres#cartes-bar')
            ->with('success', 'Format des cartes de bar enregistré.');
    }

    /**
     * Génère la planche PDF assemblée : cartes vierges + pointillés de découpe.
     */
    public function download()
    {
        $config = CarteBarService::config();
        $layout = CarteBarService::layout($config);

        if ($layout['count'] === 0) {
            return redirect('/admin/parametres#cartes-bar')
                ->with('error', 'Aucune carte ne tient sur la planche avec ce format. Ajustez les dimensions.');
        }

        $logo     = parametre::getValue('club-logo', '');
        $clubName = parametre::getValue('club-nom_complet', '') ?: parametre::getValue('club-nom_court', '');

        $pdf = Pdf::loadView('admin.cartes.modele-pdf', ['layout' => $layout, 'logo' => $logo, 'clubName' => $clubName])
            ->setPaper(
                strtolower($config['format_page']),
                $config['orientation'] === 'paysage' ? 'landscape' : 'portrait'
            );

        return $pdf->download('modele-cartes-bar.pdf');
    }

    private function saveInt(string $key, int $value): void
    {
        $p = parametre::firstOrNew(['nom' => $key]);
        $p->type  = 'integer';
        $p->value = (string) $value;
        $p->save();
    }

    private function saveStr(string $key, string $value): void
    {
        $p = parametre::firstOrNew(['nom' => $key]);
        $p->type  = 'string';
        $p->value = $value;
        $p->save();
    }
}
