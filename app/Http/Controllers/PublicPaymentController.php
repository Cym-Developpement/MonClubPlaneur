<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\HelloAssoService;

class PublicPaymentController extends Controller
{
    protected $helloAssoService;

    public function __construct(HelloAssoService $helloAssoService)
    {
        $this->helloAssoService = $helloAssoService;
    }

    /**
     * Afficher la page de paiement public
     */
    public function index(Request $request)
    {
        $prefillAmount = $request->query('amount');
        $prefillEmail  = $request->query('email');
        $mode          = $request->query('mode', 'don'); // 'don' ou 'paiement'

        return view('public.payment', compact('prefillAmount', 'prefillEmail', 'mode'));
    }

    /**
     * Traiter le paiement public
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000',
            'payer_firstname' => 'required|string|max:255',
            'payer_lastname' => 'required|string|max:255',
            'payer_email' => 'required|email|max:255',
            'message' => 'nullable|string|max:500'
        ]);

        $amount = $request->amount;
        $payerFirstname = $request->payer_firstname;
        $payerLastname = $request->payer_lastname;
        $payerEmail = $request->payer_email;
        $message = $request->message;
        $mode = $request->input('mode', 'don');
        $isPaiement = $mode === 'paiement';

        try {
            $itemName   = $isPaiement ? 'Régularisation de compte' : 'Don au club de planeur';
            $amountCts  = (int) round($amount * 100);
            $baseUrl    = config('app.url');

            $paymentData = $this->helloAssoService->buildPaymentData(
                $amountCts,
                $amountCts,
                $itemName,
                $baseUrl . '/cb',
                $baseUrl . '/cb',
                $baseUrl . '/cb',
                !$isPaiement, // containsDonation
                [
                    'firstName' => $payerFirstname,
                    'lastName'  => $payerLastname,
                    'email'     => $payerEmail,
                ],
                $message ? ['message' => $message] : []
            );

            $result = $this->helloAssoService->createCheckoutIntent($paymentData);

            if ($result && isset($result['redirectUrl'])) {
                return redirect($result['redirectUrl']);
            }

            return redirect()->back()->with('error', 'Erreur lors de la création du paiement. Veuillez réessayer.');

        } catch (\Exception $e) {
            \Log::error('Erreur paiement public: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Une erreur est survenue. Veuillez réessayer plus tard.');
        }
    }

    /**
     * Vérifie si un email correspond à un membre (AJAX)
     */
    public function checkMember(Request $request)
    {
        $email = $request->query('email', '');
        if (!$email) {
            return response()->json(['is_member' => false]);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['is_member' => false]);
        }

        return response()->json([
            'is_member'  => true,
            'first_name' => $user->prenom ?? '',
            'last_name'  => $user->nom ?? '',
        ]);
    }

    /**
     * Callback de retour après paiement (webhook)
     */
    public function callback(Request $request)
    {
        try {
            // Vérifier la signature HelloAsso
            if (!$this->helloAssoService->verifyWebhookSignature($request)) {
                \Log::warning('Signature HelloAsso invalide pour paiement public');
                return response('Unauthorized', 401);
            }

            $data = $request->all();
            
            // Log du paiement pour information
            if (isset($data['data']['state']) && $data['data']['state'] === 'Processed') {
                $paymentData = $data['data'];
                \Log::info('Don public reçu: ' . $paymentData['amount'] / 100 . '€ de ' . $paymentData['payerEmail']);
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            \Log::error('Erreur callback paiement public: ' . $e->getMessage());
            return response('Error', 500);
        }
    }
}