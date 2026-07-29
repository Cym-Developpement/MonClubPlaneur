<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\transaction;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Facturation en lot d'un même produit à plusieurs membres.
 *
 * Le produit peut être choisi dans le catalogue (il pré-remplit l'intitulé et
 * le prix) ou saisi librement pour une facturation ponctuelle.
 */
class ProductBillingController extends Controller
{
    /**
     * Étape 1 : choix du produit, puis liste des membres à cocher.
     */
    public function form(Request $request)
    {
        $products = Product::orderBy('title')->get();
        $label    = trim((string) $request->input('label', ''));
        $price    = $request->input('price', '');
        $quantity = $request->input('quantity', 1);
        $date     = $request->input('date', date('Y-m-d'));
        $users    = [];
        $billed   = [];
        $soldes   = [];

        // La liste des membres n'apparaît qu'une fois l'intitulé et le prix connus.
        if ($label !== '' && $price !== '') {
            $users  = User::where('state', 1)->orderBy('name')->get();
            $billed = transaction::where('name', $label)
                ->where('year', date('Y', strtotime($date)))
                ->pluck('idUser')
                ->toArray();

            foreach ($users as $user) {
                $soldes[$user->id] = $this->solde($user->id);
            }
        }

        return view('admin.facturationProduits', [
            'products'    => $products,
            'productId'   => $request->input('product', ''),
            'label'       => $label,
            'price'       => $price,
            'quantity'    => $quantity,
            'date'        => $date,
            'observation' => $request->input('observation', ''),
            'users'       => $users,
            'billed'      => $billed,
            'soldes'      => $soldes,
        ]);
    }

    /**
     * Solde courant d'un membre, en centimes.
     *
     * Lecture seule (dernière transaction) : contrairement à
     * User::real_amount_account, cela ne réécrit pas toutes les transactions.
     */
    private function solde($userId)
    {
        $last = transaction::where('idUser', $userId)
            ->orderBy('time', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return is_null($last) ? 0 : intval($last->solde);
    }

    /**
     * Étape 2 : création d'une transaction de débit par membre coché.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'label'       => 'required|string|max:255',
            'price'       => 'required|numeric|min:0.01',
            'quantity'    => 'required|integer|min:1',
            'date'        => 'required|date',
            'observation' => 'nullable|string|max:255',
            'users'       => 'required|array|min:1',
            'users.*'     => 'integer|exists:users,id',
        ], [
            'label.required'    => 'L\'intitulé de la facturation est obligatoire.',
            'price.required'    => 'Le prix est obligatoire.',
            'price.min'         => 'Le prix doit être supérieur à 0.',
            'quantity.required' => 'La quantité est obligatoire.',
            'quantity.integer'  => 'La quantité doit être un nombre entier.',
            'quantity.min'      => 'La quantité doit être d\'au moins 1.',
            'users.required'    => 'Merci de cocher au moins un membre à facturer.',
        ]);

        $unitCts   = (int) round($data['price'] * 100);
        $quantity  = (int) $data['quantity'];
        $amountCts = $unitCts * $quantity;
        $label     = trim($data['label']);
        $count     = 0;

        foreach (User::whereIn('id', $data['users'])->get() as $user) {
            transaction::add($user->id, -$amountCts, $label, $data['observation'] ?? null, $data['date'], $quantity);
            $count++;
        }

        $total   = number_format(($amountCts * $count) / 100, 2, ',', ' ');
        $detail  = $quantity > 1 ? ' (' . $quantity . ' x ' . number_format($unitCts / 100, 2, ',', ' ') . ' €)' : '';

        return redirect('/admin/facturationProduits')
            ->with('success', $count . ' membre(s) facturé(s) « ' . $label . ' »' . $detail . ' pour un total de ' . $total . ' €.');
    }
}
