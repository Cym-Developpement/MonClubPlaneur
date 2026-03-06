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
            // Préparer les données pour HelloAsso
            $itemName    = $isPaiement ? 'Régularisation de compte' : 'Don au club de planeur';
            $descPrefix  = $isPaiement ? 'Paiement de' : 'Don de';
            $formData = [
                'itemName' => $itemName,
                'totalAmount' => $amount * 100, // En centimes
                'payerFirstName' => $payerFirstname,
                'payerLastName' => $payerLastname,
                'payerEmail' => $payerEmail,
                'containsDonation' => $isPaiement ? 0 : 1,
                'description' => $message ? "{$descPrefix} {$payerFirstname} {$payerLastname} - {$message}" : "{$descPrefix} {$payerFirstname} {$payerLastname}"
            ];

            // Faire un appel AJAX pour obtenir l'URL de redirection HelloAsso
            $response = $this->helloAssoService->createPayment($formData);

            if ($response && isset($response['redirect_url'])) {
                return redirect($response['redirect_url']);
            } else {
                return redirect()->back()->with('error', 'Erreur lors de la création du paiement. Veuillez réessayer.');
            }

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