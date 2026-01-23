<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HelloAssoService;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\transaction;

class HelloAssoController extends Controller
{
    protected $helloAssoService;

    public function __construct(HelloAssoService $helloAssoService)
    {
        $this->helloAssoService = $helloAssoService;
    }

    /**
     * Traiter les notifications HelloAsso
     */
    public function notification(Request $request)
    {
        try {
            $data = $request->all();
            $headers = $request->headers->all();
            
            // Logger toutes les informations reçues
            Log::info('=== WEBHOOK HELLOASSO REÇU ===', [
                'timestamp' => now()->toISOString(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'headers' => $headers,
                'raw_content' => $request->getContent(),
                'parsed_data' => $data,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Vérifier le type de notification
            if (isset($data['eventType'])) {
                Log::info('Type de notification HelloAsso détecté', [
                    'eventType' => $data['eventType']
                ]);
                
                switch ($data['eventType']) {
                    case 'Order':
                        $this->handleOrderNotification($data);
                        break;
                    case 'Payment':
                        $this->handlePaymentNotification($data);
                        break;
                    default:
                        Log::warning('Type de notification HelloAsso non géré', [
                            'eventType' => $data['eventType'] ?? 'N/A',
                            'data' => $data
                        ]);
                }
            } else {
                Log::warning('Notification HelloAsso sans eventType', [
                    'data' => $data
                ]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement de la notification HelloAsso', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
                'headers' => $request->headers->all()
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Gérer les notifications de commande
     */
    private function handleOrderNotification(array $data)
    {
        Log::info('=== NOTIFICATION COMMANDE HELLOASSO ===', [
            'order_id' => $data['data']['id'] ?? 'N/A',
            'order_status' => $data['data']['state'] ?? 'N/A',
            'amount' => $data['data']['amount'] ?? 'N/A',
            'payer_email' => $data['data']['payer']['email'] ?? 'N/A',
            'payer_name' => $data['data']['payer']['firstName'] . ' ' . $data['data']['payer']['lastName'] ?? 'N/A',
            'full_data' => $data
        ]);
        
        // TODO: Ajouter votre logique métier ici
        // Par exemple : mettre à jour le statut d'une commande en base de données
        // - Créer une transaction dans votre système
        // - Mettre à jour le solde du compte utilisateur
        // - Envoyer un email de confirmation
    }

    /**
     * Gérer les notifications de paiement
     */
    private function handlePaymentNotification(array $data)
    {
        $paymentId = $data['data']['id'] ?? null;
        $orderId = $data['data']['order']['id'] ?? null;
        $paymentStatus = $data['data']['state'] ?? 'N/A';
        $amount = $data['data']['amount'] ?? 'N/A';
        $installmentNumber = $data['data']['installmentNumber'] ?? 'N/A';
        $payerEmail = $data['data']['payer']['email'] ?? 'N/A';
        $payerName = ($data['data']['payer']['firstName'] ?? '') . ' ' . ($data['data']['payer']['lastName'] ?? '');
        
        Log::info('=== NOTIFICATION PAIEMENT HELLOASSO ===', [
            'payment_id' => $paymentId,
            'payment_status' => $paymentStatus,
            'amount' => $amount,
            'installment_number' => $installmentNumber,
            'order_id' => $orderId,
            'payer_email' => $payerEmail,
            'payer_name' => trim($payerName),
            'full_data' => $data
        ]);
        
        // Vérifier le paiement via l'API HelloAsso
        if ($paymentId && $orderId) {
            $verifiedPayment = $this->helloAssoService->verifyPayment($paymentId, $orderId);
            
            if ($verifiedPayment) {
                Log::info('Paiement HelloAsso vérifié et validé', [
                    'payment_id' => $paymentId,
                    'order_id' => $orderId,
                    'verified_amount' => $verifiedPayment['amount'] ?? 'N/A',
                    'verified_state' => $verifiedPayment['state'] ?? 'N/A',
                    'verified_installment' => $verifiedPayment['installmentNumber'] ?? 'N/A'
                ]);
                
                // Traiter le paiement vérifié
                $this->processVerifiedPayment($verifiedPayment, $payerEmail);
                
            } else {
                Log::error('Échec de la vérification du paiement HelloAsso', [
                    'payment_id' => $paymentId,
                    'order_id' => $orderId
                ]);
            }
        } else {
            Log::error('Données de paiement HelloAsso incomplètes', [
                'payment_id' => $paymentId,
                'order_id' => $orderId
            ]);
        }
    }
    
    /**
     * Traiter un paiement vérifié
     */
    private function processVerifiedPayment(array $paymentData, string $payerEmail)
    {
        try {
            $amount = $paymentData['amount'] ?? 0;
            $paymentId = $paymentData['id'] ?? null;
            $orderId = $paymentData['order']['id'] ?? null;
            $installmentNumber = $paymentData['installmentNumber'] ?? 1;
            $state = $paymentData['state'] ?? 'Unknown';
            
            // Déterminer le type de paiement
            $paymentType = $installmentNumber == 1 ? 'paiement initial' : "échéance {$installmentNumber}";
            
            Log::info('Traitement du paiement vérifié', [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'amount' => $amount,
                'installment_number' => $installmentNumber,
                'payment_type' => $paymentType,
                'state' => $state,
                'payer_email' => $payerEmail
            ]);
            
            // Vérifier que le paiement est autorisé
            if ($state !== 'Authorized') {
                Log::warning('Paiement non autorisé, ignoré', [
                    'payment_id' => $paymentId,
                    'state' => $state,
                    'installment_number' => $installmentNumber
                ]);
                return;
            }
            
            $user = User::where('email', $payerEmail)->first();
            if ($user) {
                // Créer une description différenciée selon le type de paiement
                $description = $installmentNumber == 1 
                    ? 'CB Paiement initial - HelloAsso'
                    : "CB Échéance {$installmentNumber} - HelloAsso";
                $observation = 'paiement : '.$paymentId.' / Commande : '.$orderId;
                transaction::add($user->id, $amount, $description, $observation);
                
                Log::info('Paiement traité avec succès', [
                    'user_id' => $user->id,
                    'payment_type' => $paymentType,
                    'amount_added' => $amount / 100,
                    'new_balance' => $user->fresh()->balance,
                    'installment_number' => $installmentNumber
                ]);
            } else {
                Log::error('Utilisateur non trouvé pour le paiement', [
                    'payer_email' => $payerEmail,
                    'payment_id' => $paymentId,
                    'installment_number' => $installmentNumber
                ]);
            }
            
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement du paiement vérifié', [
                'message' => $e->getMessage(),
                'payment_data' => $paymentData,
                'payer_email' => $payerEmail
            ]);
        }
    }

    /**
     * Page de retour après paiement
     */
    public function return(Request $request)
    {
        $checkoutIntentId = $request->get('checkoutIntentId');
        $code = $request->get('code');
        $orderId = $request->get('orderId');

        // Logger les paramètres de retour pour debug
        Log::info('Retour HelloAsso', [
            'checkoutIntentId' => $checkoutIntentId,
            'code' => $code,
            'orderId' => $orderId,
            'all_params' => $request->all()
        ]);

        // Rediriger vers la page HelloAsso avec une notification de traitement
        return redirect()->route('helloasso.page')->with('info', 'Votre paiement est en cours de traitement. Vous recevrez une confirmation par email une fois le traitement terminé.');
    }

    /**
     * Page de retour en cas d'erreur
     */
    public function error(Request $request)
    {
        $checkoutIntentId = $request->get('checkoutIntentId');
        $error = $request->get('error');

        Log::warning('Erreur HelloAsso', [
            'checkoutIntentId' => $checkoutIntentId,
            'error' => $error
        ]);

        return redirect()->route('payment.error')->with('error', 'Une erreur est survenue lors du paiement.');
    }

    /**
     * Page de retour si annulation
     */
    public function back(Request $request)
    {
        $checkoutIntentId = $request->get('checkoutIntentId');

        Log::info('Retour HelloAsso (annulation)', [
            'checkoutIntentId' => $checkoutIntentId
        ]);

        return redirect()->route('payment.cancelled')->with('info', 'Paiement annulé.');
    }

    /**
     * Créer un paiement HelloAsso
     */
    public function createPayment(Request $request)
    {
        // Vérifier que l'utilisateur est connecté
        if (!auth()->check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez être connecté pour effectuer un paiement.'
                ], 401);
            }
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour effectuer un paiement.');
        }

        try {
            $validatedData = $request->validate([
                'totalAmount' => 'required|integer|min:1',
                'initialAmount' => 'required|integer|min:1',
                'itemName' => 'required|string|max:250',
                'containsDonation' => 'boolean',
                'payerNom' => 'required|string|max:255',
                'payerPrenom' => 'required|string|max:255',
                'payerEmail' => 'required|email|max:255',
                'payer' => 'array',
                'metadata' => 'array',
                'terms' => 'nullable|string' // JSON string des échéances
            ]);

            // Construire les URLs de retour
            $baseUrl = config('app.url');
            $backUrl = $baseUrl . '/helloasso/back';
            $errorUrl = $baseUrl . '/helloasso/error';
            $returnUrl = $baseUrl . '/helloasso/return';

            // Construire les données de l'acheteur
            $payerData = [
                'firstName' => $validatedData['payerPrenom'],
                'lastName' => $validatedData['payerNom'],
                'email' => $validatedData['payerEmail']
            ];

            // Traiter les échéances si présentes
            $terms = [];
            if (!empty($validatedData['terms'])) {
                $terms = json_decode($validatedData['terms'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Erreur décodage JSON des échéances', [
                        'terms_raw' => $validatedData['terms'],
                        'json_error' => json_last_error_msg()
                    ]);
                    throw new \Exception('Format des échéances invalide');
                }
                Log::info('Échéances décodées', ['terms' => $terms]);
            }

            // Construire les données de paiement
            $paymentData = $this->helloAssoService->buildPaymentData(
                $validatedData['totalAmount'],
                $validatedData['initialAmount'],
                $validatedData['itemName'],
                $backUrl,
                $errorUrl,
                $returnUrl,
                $validatedData['containsDonation'] ?? false,
                $payerData,
                $validatedData['metadata'] ?? [],
                $terms
            );

            Log::info('Données de paiement HelloAsso', ['paymentData' => $paymentData]);

            // Créer l'intention de paiement
            $result = $this->helloAssoService->createCheckoutIntent($paymentData);

            if ($result && isset($result['redirectUrl'])) {
                // Si c'est une requête AJAX, retourner l'URL en JSON
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'redirect_url' => $result['redirectUrl']
                    ]);
                }
                
                // Sinon, rediriger normalement
                return redirect($result['redirectUrl']);
            }

            // Si c'est une requête AJAX, retourner une erreur en JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création du paiement.'
                ], 500);
            }

            return back()->with('error', 'Erreur lors de la création du paiement.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du paiement HelloAsso', [
                'message' => $e->getMessage(),
                'data' => $request->all()
            ]);

            // Si c'est une requête AJAX, retourner une erreur en JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de la création du paiement.'
                ], 500);
            }

            return back()->with('error', 'Une erreur est survenue.');
        }
    }

    /**
     * Page d'affichage HelloAsso
     */
    public function page()
    {
        $user = auth()->user();
        
        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour effectuer un paiement.');
        }
        
        return view('helloasso', [
            'user' => $user,
            'userNom' => $user->nom ?? '',
            'userPrenom' => $user->prenom ?? '',
            'userEmail' => $user->email ?? ''
        ]);
    }

    /**
     * Tester l'obtention d'un access token HelloAsso
     * Cette méthode peut être utilisée pour tester la connexion à l'API HelloAsso
     */
    public function testAccessToken()
    {
        try {
            $tokenData = $this->helloAssoService->getAccessToken();
            
            if ($tokenData) {
                Log::info('Test Access Token HelloAsso réussi', [
                    'expires_in' => $tokenData['expires_in'],
                    'token_type' => $tokenData['token_type']
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Access token obtenu avec succès',
                    'data' => [
                        'expires_in' => $tokenData['expires_in'],
                        'token_type' => $tokenData['token_type'],
                        'has_access_token' => !empty($tokenData['access_token']),
                        'has_refresh_token' => !empty($tokenData['refresh_token'])
                    ]
                ]);
            } else {
                Log::error('Test Access Token HelloAsso échoué');
                
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible d\'obtenir l\'access token'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception lors du test Access Token HelloAsso', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tester le rafraîchissement d'un access token
     */
    public function testRefreshToken(Request $request)
    {
        try {
            $refreshToken = $request->input('refresh_token');
            
            if (!$refreshToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Refresh token requis'
                ], 400);
            }

            $tokenData = $this->helloAssoService->refreshAccessToken($refreshToken);
            
            if ($tokenData) {
                Log::info('Test Refresh Token HelloAsso réussi', [
                    'expires_in' => $tokenData['expires_in'],
                    'token_type' => $tokenData['token_type']
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Access token rafraîchi avec succès',
                    'data' => [
                        'expires_in' => $tokenData['expires_in'],
                        'token_type' => $tokenData['token_type'],
                        'has_access_token' => !empty($tokenData['access_token']),
                        'has_refresh_token' => !empty($tokenData['refresh_token'])
                    ]
                ]);
            } else {
                Log::error('Test Refresh Token HelloAsso échoué');
                
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de rafraîchir l\'access token'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Exception lors du test Refresh Token HelloAsso', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les informations sur l'environnement HelloAsso
     */
    public function getEnvironmentInfo()
    {
        try {
            $envInfo = $this->helloAssoService->getEnvironmentInfo();
            
            Log::info('Informations environnement HelloAsso récupérées', $envInfo);
            
            return response()->json([
                'success' => true,
                'message' => 'Informations environnement récupérées avec succès',
                'data' => $envInfo
            ]);

        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des informations environnement', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }
}
