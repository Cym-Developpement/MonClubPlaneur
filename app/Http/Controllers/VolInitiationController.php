<?php

namespace App\Http\Controllers;

use App\Mail\VolInitiationConfirmation;
use App\Models\VolInitiation;
use App\Models\parametre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class VolInitiationController extends Controller
{
    /**
     * Retourne la liste des types VI depuis la table parametres
     */
    private function getTypes(): \Illuminate\Support\Collection
    {
        return parametre::where('nom', 'like', 'vi-%')
            ->where('nom', 'not like', 'vi_config-%')
            ->orderBy('nom')
            ->get()
            ->map(fn($p) => [
                'label'    => trim(explode('-', $p->nom, 2)[1]),
                'prix_cts' => (int) $p->value,
            ]);
    }

    /**
     * Liste tous les vols d'initiation avec filtres
     */
    public function index(Request $request)
    {
        $query = VolInitiation::orderBy('created_at', 'desc');

        $filtre = $request->get('filtre', 'tous');

        if ($filtre === 'non_actifs') {
            $query->where('actif', false);
        } elseif ($filtre === 'actifs_non_realises') {
            $query->where('actif', true)->where('realise', false);
        } elseif ($filtre === 'realises') {
            $query->where('realise', true);
        }

        $vis = $query->get();

        return view('admin.vi.index', compact('vis', 'filtre'));
    }

    /**
     * Formulaire de création manuelle
     */
    public function create()
    {
        $types = $this->getTypes();

        return view('admin.vi.create', compact('types'));
    }

    /**
     * Enregistre un nouveau VI
     */
    public function store(Request $request)
    {
        $request->validate([
            'source' => 'required|in:admin,offert',
            'type'   => 'nullable|string|max:100',
            'notes'  => 'nullable|string',
        ]);

        $vi = new VolInitiation();
        $vi->source = $request->input('source');
        $vi->notes  = $request->input('notes');

        $typeLabel = $request->input('type');
        if ($typeLabel) {
            $vi->type = $typeLabel;

            // Récupérer le prix depuis parametres
            $param = parametre::where('nom', 'vi-' . $typeLabel)->first();
            if ($param) {
                $vi->prix_cts = (int) $param->value;
            }
        }

        $vi->save();

        return redirect()->route('admin.vi.show', $vi->id)
            ->with('success', 'Bon VI créé — code : ' . $vi->code);
    }

    /**
     * Détail d'un VI
     */
    public function show($id)
    {
        $vi = VolInitiation::findOrFail($id);

        return view('admin.vi.show', compact('vi'));
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        $vi    = VolInitiation::findOrFail($id);
        $types = $this->getTypes();

        return view('admin.vi.edit', compact('vi', 'types'));
    }

    /**
     * Enregistre les modifications d'un VI
     */
    public function update(Request $request, $id)
    {
        $vi = VolInitiation::findOrFail($id);

        $request->validate([
            'source'         => 'required|in:admin,offert,helloasso',
            'type'           => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
            'nom'            => 'nullable|string|max:255',
            'prenom'         => 'nullable|string|max:255',
            'date_naissance' => 'nullable|date',
            'adresse'        => 'nullable|string|max:255',
            'cp'             => 'nullable|string|max:10',
            'ville'          => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'telephone'      => 'nullable|string|max:20',
            'actif'          => 'boolean',
            'realise'        => 'boolean',
            'date_realisation' => 'nullable|date',
        ]);

        $typeLabel = $request->input('type');
        $prix_cts  = $vi->prix_cts;
        if ($typeLabel && $typeLabel !== $vi->type) {
            $param = parametre::where('nom', 'vi-' . $typeLabel)->first();
            if ($param) {
                $prix_cts = (int) $param->value;
            }
        }

        $vi->fill([
            'source'           => $request->input('source'),
            'type'             => $typeLabel,
            'prix_cts'         => $prix_cts,
            'notes'            => $request->input('notes'),
            'disponibilites'   => $request->input('disponibilites'),
            'nom'              => $request->input('nom'),
            'prenom'           => $request->input('prenom'),
            'date_naissance'   => $request->input('date_naissance'),
            'adresse'          => $request->input('adresse'),
            'cp'               => $request->input('cp'),
            'ville'            => $request->input('ville'),
            'email'            => $request->input('email'),
            'telephone'        => $request->input('telephone'),
            'actif'            => $request->boolean('actif'),
            'realise'          => $request->boolean('realise'),
            'date_realisation' => $request->input('date_realisation'),
        ]);
        $vi->save();

        return redirect()->route('admin.vi.show', $vi->id)
            ->with('success', 'Bon VI mis à jour.');
    }

    /**
     * Supprime un VI
     */
    public function destroy($id)
    {
        $vi = VolInitiation::findOrFail($id);
        $code = $vi->code;
        $vi->delete();

        return redirect()->route('admin.vi.index')
            ->with('success', 'Bon VI ' . $code . ' supprimé.');
    }

    /**
     * Marquer le vol comme réalisé
     */
    public function marquerRealise(Request $request, $id)
    {
        $vi = VolInitiation::findOrFail($id);
        $vi->realise          = true;
        $vi->date_realisation = now()->toDateString();
        $vi->save();

        return redirect()->route('admin.vi.index')
            ->with('success', 'Vol d\'initiation marqué comme réalisé.');
    }

    // ─── Gestion des types VI (parametres) ───────────────────────────────────

    /**
     * Crée un type VI dans la table parametres
     */
    public function viTypeStore(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'prix'  => 'required|numeric|min:0',
        ]);

        $label = trim($request->input('label'));
        $nom   = 'vi-' . $label;

        if (parametre::where('nom', $nom)->exists()) {
            return redirect()->route('tarifs')
                ->with('error', 'Un type VI "' . $label . '" existe déjà.');
        }

        $p = new parametre();
        $p->nom      = $nom;
        $p->type     = 'integer';
        $p->value    = (string) (int) round((float) $request->input('prix') * 100);
        $p->monetary = 1;
        $p->public   = $request->boolean('public') ? 1 : 0;
        $p->save();

        return redirect()->route('tarifs')
            ->with('success', 'Type VI "' . $label . '" créé.');
    }

    /**
     * Met à jour un type VI
     */
    public function viTypeUpdate(Request $request, $id)
    {
        $request->validate([
            'label' => 'required|string|max:100',
            'prix'  => 'required|numeric|min:0',
        ]);

        $p = parametre::findOrFail($id);
        $label = trim($request->input('label'));

        $p->nom      = 'vi-' . $label;
        $p->value    = (string) (int) round((float) $request->input('prix') * 100);
        $p->public   = $request->boolean('public') ? 1 : 0;
        $p->save();

        return redirect()->route('tarifs')
            ->with('success', 'Type VI "' . $label . '" mis à jour.');
    }

    /**
     * Supprime un type VI
     */
    public function viTypeDestroy($id)
    {
        $p = parametre::findOrFail($id);
        $label = trim(explode('-', $p->nom, 2)[1] ?? $p->nom);
        $p->delete();

        return redirect()->route('tarifs')
            ->with('success', 'Type VI "' . $label . '" supprimé.');
    }

    // ─── Pages publiques d'activation ────────────────────────────────────────

    /**
     * Affiche la page de saisie du code
     */
    public function lookupForm()
    {
        return view('vi.index');
    }

    /**
     * Redirige vers la page d'activation selon le code saisi
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:8',
        ], [
            'code.required' => 'Veuillez saisir votre code.',
            'code.size'     => 'Le code doit faire exactement 8 caractères.',
        ]);

        $code = strtolower(trim($request->input('code')));

        if (!VolInitiation::where('code', $code)->exists()) {
            return back()->with('error', 'Code introuvable. Vérifiez votre bon et réessayez.')->withInput();
        }

        return redirect()->route('vi.activation', $code);
    }

    /**
     * Affiche le formulaire public d'activation
     */
    public function activationForm($code)
    {
        $vi = VolInitiation::where('code', $code)->firstOrFail();

        return view('vi.activation', compact('vi'));
    }

    /**
     * Traite la soumission du formulaire d'activation
     */
    public function activationStore(Request $request, $code)
    {
        $vi = VolInitiation::where('code', $code)->firstOrFail();

        if ($vi->actif) {
            return redirect()->route('vi.activation', $code)
                ->with('info', 'Ce bon a déjà été activé.');
        }

        $request->validate([
            'nom'            => 'required|string|max:255',
            'prenom'         => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'adresse'        => 'nullable|string|max:255',
            'cp'             => 'nullable|string|max:10',
            'ville'          => 'nullable|string|max:255',
            'email'          => 'required|email|max:255',
            'telephone'      => 'nullable|string|max:20',
        ]);

        $vi->fill([
            'nom'            => $request->input('nom'),
            'prenom'         => $request->input('prenom'),
            'date_naissance' => $request->input('date_naissance'),
            'adresse'        => $request->input('adresse'),
            'cp'             => $request->input('cp'),
            'ville'          => $request->input('ville'),
            'email'           => $request->input('email'),
            'telephone'       => $request->input('telephone'),
            'disponibilites'  => $request->input('disponibilites'),
            'actif'           => true,
        ]);
        $vi->save();

        try {
            Mail::to($vi->email)->send(new VolInitiationConfirmation($vi));
        } catch (\Exception $e) {
            \Log::error('Erreur envoi email confirmation VI', [
                'code'    => $vi->code,
                'email'   => $vi->email,
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()->route('vi.activation', $code)
            ->with('success', 'Votre bon a bien été activé ! Un email de confirmation vous a été envoyé. Nous vous contacterons prochainement pour fixer la date de votre vol.');
    }
}
