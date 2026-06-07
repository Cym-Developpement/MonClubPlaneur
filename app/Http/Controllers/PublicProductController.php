<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLog;
use App\Models\Product;
use App\Models\ProductPurchase;
use App\Services\HelloAssoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicProductController extends Controller
{
    protected $helloAssoService;

    public function __construct(HelloAssoService $helloAssoService)
    {
        $this->helloAssoService = $helloAssoService;
    }

    /**
     * Page publique listant les produits affichés sur le site (lot 4).
     */
    public function list()
    {
        $products = Product::where('active', true)
            ->where('show_on_site', true)
            ->orderBy('title')
            ->get();

        return view('public.products', compact('products'));
    }

    /**
     * Page publique de paiement d'un produit : /payer/{slug}
     */
    public function show(Request $request, string $slug)
    {
        $product = Product::where('slug', $slug)->where('active', true)->firstOrFail();

        AuditLog::log('visite page paiement produit "' . $product->title . '" (' . $request->fullUrl() . ')');

        return view('public.product', compact('product'));
    }

    /**
     * Initie le paiement HelloAsso pour un produit.
     */
    public function pay(Request $request)
    {
        $request->validate([
            'slug'            => 'required|string',
            'payer_firstname' => 'required|string|max:255',
            'payer_lastname'  => 'required|string|max:255',
            'payer_email'     => 'required|email|max:255',
            'message'         => 'nullable|string|max:500',
        ]);

        $product = Product::where('slug', $request->input('slug'))->where('active', true)->firstOrFail();

        // Enregistre l'achat en attente AVANT la redirection (corrélation via metadata)
        $purchase = ProductPurchase::create([
            'product_id'      => $product->id,
            'product_title'   => $product->title,
            'amount_cts'      => $product->amount_cts,
            'payer_firstname' => $request->input('payer_firstname'),
            'payer_lastname'  => $request->input('payer_lastname'),
            'payer_email'     => $request->input('payer_email'),
            'message'         => $request->input('message'),
            'status'          => 'pending',
        ]);

        try {
            $baseUrl  = config('app.url');
            $showUrl  = $baseUrl . '/payer/' . $product->slug;

            $paymentData = $this->helloAssoService->buildPaymentData(
                $product->amount_cts,
                $product->amount_cts,
                $product->title,
                $showUrl . '?annule=1',  // backUrl (annulation)
                $showUrl . '?erreur=1',  // errorUrl
                $showUrl . '?merci=1',   // returnUrl (succès)
                false,                   // containsDonation
                [
                    'firstName' => $purchase->payer_firstname,
                    'lastName'  => $purchase->payer_lastname,
                    'email'     => $purchase->payer_email,
                ],
                [
                    'type'         => 'product',
                    'purchase_id'  => $purchase->id,
                    'product_slug' => $product->slug,
                ]
            );

            $result = $this->helloAssoService->createCheckoutIntent($paymentData);

            if ($result && isset($result['redirectUrl'])) {
                if (!empty($result['checkoutIntentId'])) {
                    $purchase->helloasso_checkout_intent_id = $result['checkoutIntentId'];
                    $purchase->save();
                }
                return redirect($result['redirectUrl']);
            }

            return redirect()->back()
                ->with('error', 'Erreur lors de la création du paiement. Veuillez réessayer.')
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Erreur paiement produit: ' . $e->getMessage(), [
                'purchase_id' => $purchase->id,
                'slug'        => $product->slug,
            ]);
            return redirect()->back()
                ->with('error', 'Une erreur est survenue. Veuillez réessayer plus tard.')
                ->withInput();
        }
    }
}
