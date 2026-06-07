<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Liste des produits + derniers achats.
     */
    public function index()
    {
        $products  = Product::orderBy('title')->get();
        $purchases = ProductPurchase::orderBy('created_at', 'desc')->limit(100)->get();

        return view('admin.produits.index', compact('products', 'purchases'));
    }

    public function create()
    {
        return view('admin.produits.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        $product = new Product();
        $this->fillProduct($product, $request, $data);
        $product->save();

        return redirect()->route('admin.produits.index')
            ->with('success', 'Produit « ' . $product->title . ' » créé. Lien : ' . $product->public_url);
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);

        return view('admin.produits.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $data    = $this->validateProduct($request, $product->id);

        $this->fillProduct($product, $request, $data);
        $product->save();

        return redirect()->route('admin.produits.index')
            ->with('success', 'Produit « ' . $product->title . ' » mis à jour.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $title   = $product->title;
        $product->delete();

        return redirect()->route('admin.produits.index')
            ->with('success', 'Produit « ' . $title . ' » supprimé.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function validateProduct(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'title'        => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255|regex:/^[a-z0-9\-]+$/',
            'description'  => 'nullable|string',
            'amount'       => 'required|numeric|min:1|max:10000',
            'active'       => 'boolean',
            'show_on_site' => 'boolean',
            'email_extra'  => 'nullable|string',
        ]);
    }

    private function fillProduct(Product $product, Request $request, array $data): void
    {
        $slug = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($data['title']);

        $product->title        = $data['title'];
        $product->slug         = Product::uniqueSlug($slug, $product->id);
        $product->description  = $data['description'] ?? null;
        $product->amount_cts   = (int) round((float) $data['amount'] * 100);
        $product->active       = $request->boolean('active');
        $product->show_on_site = $request->boolean('show_on_site');
        $product->email_extra  = $data['email_extra'] ?? null;
    }
}
