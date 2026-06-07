<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\HelloAssoService;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\transaction;
use App\Models\VolInitiation;
use App\Models\parametre;
use App\Models\HelloAssoPendingPayment;
use App\Models\ProductPurchase;
use App\Models\usersAttributes;
use App\Mail\ProductPurchaseConfirmation;
use App\Mail\ProductPurchaseAdminNotification;
use Illuminate\Support\Facades\Mail;

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
                
                $sourceIps = array_unique(array_merge([$request->ip()], $request->ips()));

                switch ($data['eventType']) {
                    case 'Order':
                        $this->handleOrderNotification($data);
                        break;
                    case 'Payment':
                        $this->handlePaymentNotification($data, $sourceIps);
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
        
        // Détecter les commandes de vols d'initiation via le slug du formulaire HelloAsso
        $viFormSlug = parametre::getValue('vi_config-helloasso_form_slug', '');
        $orderFormSlug = $data['data']['form']['slug'] ?? null;

        if ($viFormSlug && $orderFormSlug && $orderFormSlug === $viFormSlug) {
            $this->createVolInitiationFromOrder($data);
            return;
        }
    }

    /**
     * Créer un vol d'initiation depuis une commande HelloAsso
     */
    private function createVolInitiationFromOrder(array $data): void
    {
        try {
            $order   = $data['data'] ?? [];
            $payer   = $order['payer'] ?? [];
            $items   = $order['items'] ?? [];

            // Déterminer le type et le prix depuis le premier item de la commande
            $type     = null;
            $prixCts  = null;
            if (!empty($items[0])) {
                $item    = $items[0];
                $type    = $item['name'] ?? null;
                $prixCts = isset($item['amount']) ? (int) $item['amount'] : null;
            }

            // HelloAsso inverse firstName/lastName par rapport à la convention française
            $vi = new VolInitiation();
            $vi->source              = 'helloasso';
            $vi->nom                 = $payer['lastName'] ?? null;
            $vi->prenom              = $payer['firstName'] ?? null;
            $vi->email               = $payer['email'] ?? null;
            $vi->type                = $type;
            $vi->prix_cts            = $prixCts;
            $vi->helloasso_order_id  = $order['id'] ?? null;
            $vi->helloasso_payment_id = $data['id'] ?? null;
            $vi->save();

            Log::info('Vol d\'initiation créé depuis HelloAsso', [
                'vi_code'  => $vi->code,
                'order_id' => $vi->helloasso_order_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur création VI depuis HelloAsso', [
                'message' => $e->getMessage(),
                'data'    => $data,
            ]);
        }
    }

    /**
     * Gérer les notifications de paiement
     */
    private function handlePaymentNotification(array $data, array $sourceIps = [])
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
            'source_ips' => $sourceIps,
            'full_data' => $data
        ]);

        if (! $paymentId || ! $orderId) {
            Log::error('Données de paiement HelloAsso incomplètes', [
                'payment_id' => $paymentId,
                'order_id' => $orderId
            ]);
            return;
        }

        // Paiement d'un produit public ? (corrélation via les metadata du checkout-intent)
        $metadata = $data['data']['metadata']
            ?? $data['data']['order']['metadata']
            ?? [];
        if (($metadata['type'] ?? null) === 'product') {
            $this->handleProductPayment($data['data'], $metadata, $sourceIps);
            return;
        }

        // Anti-doublon : vérifier si ce paiement a déjà été crédité ou est en attente
        if ($this->isPaymentAlreadyProcessed($paymentId)) {
            Log::info('Paiement HelloAsso déjà traité, ignoré (doublon)', [
                'payment_id' => $paymentId,
            ]);
            return;
        }

        // Si le webhook arrive depuis une IP HelloAsso de confiance, on peut faire
        // confiance au payload directement et éviter l'appel API (bloqué par Cloudflare).
        if ($this->isTrustedHelloAssoIp($sourceIps) && $paymentStatus === 'Authorized') {
            Log::info('Webhook HelloAsso depuis IP de confiance — traitement direct sans vérification API', [
                'payment_id' => $paymentId,
                'source_ips' => $sourceIps,
            ]);
            $this->processVerifiedPayment($data['data'], $payerEmail);
            return;
        }

        // Récupérer le checkoutIntentId si on l'a (cache /return ou row pending pré-existante).
        $existingPending = HelloAssoPendingPayment::where('payment_id', $paymentId)
            ->orWhere('order_id', $orderId)
            ->first();
        $checkoutIntentId = $existingPending?->checkout_intent_id
            ?? \Cache::get('helloasso_intent_for_order_' . $orderId);

        $verifiedPayment = $this->helloAssoService->verifyPayment($paymentId, $orderId, $checkoutIntentId);

        if ($verifiedPayment) {
            Log::info('Paiement HelloAsso vérifié et validé', [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'verified_amount' => $verifiedPayment['amount'] ?? 'N/A',
                'verified_state' => $verifiedPayment['state'] ?? 'N/A',
                'verified_installment' => $verifiedPayment['installmentNumber'] ?? 'N/A'
            ]);

            $this->processVerifiedPayment($verifiedPayment, $payerEmail);
            return;
        }

        // Fallback : stocker le paiement pour retry ultérieur
        Log::warning('Échec de la vérification API HelloAsso, paiement mis en attente pour retry', [
            'payment_id' => $paymentId,
            'order_id' => $orderId
        ]);

        HelloAssoPendingPayment::updateOrCreate(
            ['payment_id' => $paymentId],
            [
                'order_id' => $orderId,
                'checkout_intent_id' => $this->resolveCheckoutIntentId($orderId),
                'amount' => is_numeric($amount) ? (int) $amount : 0,
                'payer_email' => $payerEmail,
                'payer_name' => trim($payerName),
                'installment_number' => is_numeric($installmentNumber) ? (int) $installmentNumber : 1,
                'webhook_data' => $data,
                'status' => 'pending',
            ]
        );
    }

    /**
     * Récupère le checkoutIntentId stocké en cache par la route /helloasso/return
     * pour cette commande. Permet d'enrichir la row pending même si le webhook
     * arrive avant que la route return ne soit hit.
     */
    private function resolveCheckoutIntentId(string $orderId): ?string
    {
        return \Cache::pull('helloasso_intent_for_order_' . $orderId);
    }

    /**
     * Vérifie si l'IP source du webhook fait partie des IPs HelloAsso de confiance.
     * Liste configurable via le paramètre `helloasso-trusted_ips` (séparées par virgule).
     * Par défaut : IP production HelloAsso documentée.
     */
    private function isTrustedHelloAssoIp(array $sourceIps): bool
    {
        $configured = parametre::getValue('helloasso-trusted_ips', '51.138.206.200');
        $trustedIps = array_filter(array_map('trim', explode(',', $configured)));

        return (bool) array_intersect($trustedIps, array_filter($sourceIps));
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
                // Anti-doublon avant crédit
                if ($this->isPaymentAlreadyProcessed($paymentId)) {
                    Log::info('Paiement HelloAsso déjà crédité, transaction ignorée (doublon)', [
                        'payment_id' => $paymentId,
                    ]);
                    return;
                }

                // Créer une description différenciée selon le type de paiement
                $description = $installmentNumber == 1
                    ? 'CB Paiement initial - HelloAsso'
                    : "CB Échéance {$installmentNumber} - HelloAsso";
                $observation = 'paiement : '.$paymentId.' / Commande : '.$orderId;
                transaction::add($user->id, $amount, $description, $observation);

                // Toujours laisser une trace en base, en conservant le checkout_intent_id
                // si on l'a (depuis la route /helloasso/return ou déjà stocké).
                $existing = HelloAssoPendingPayment::where('payment_id', $paymentId)->first();
                HelloAssoPendingPayment::updateOrCreate(
                    ['payment_id' => $paymentId],
                    [
                        'order_id'           => $orderId,
                        'checkout_intent_id' => $existing?->checkout_intent_id ?? $this->resolveCheckoutIntentId((string) $orderId),
                        'amount'             => is_numeric($amount) ? (int) $amount : 0,
                        'payer_email'        => $payerEmail,
                        'payer_name'         => $existing?->payer_name ?? '',
                        'installment_number' => is_numeric($installmentNumber) ? (int) $installmentNumber : 1,
                        'webhook_data'       => $paymentData,
                        'status'             => 'processed',
                        'processed_at'       => now(),
                        'error_message'      => null,
                    ]
                );

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
     * Traiter un paiement de produit public (baptême, etc.).
     * Ne crédite aucun compte membre : marque l'achat comme payé et envoie les emails.
     */
    private function handleProductPayment(array $payment, array $metadata, array $sourceIps): void
    {
        try {
            $paymentId  = $payment['id'] ?? null;
            $orderId    = $payment['order']['id'] ?? null;
            $state      = $payment['state'] ?? 'Unknown';
            $purchaseId = $metadata['purchase_id'] ?? null;

            $purchase = $purchaseId ? ProductPurchase::find($purchaseId) : null;
            if (!$purchase) {
                Log::error('Achat produit introuvable pour le paiement HelloAsso', [
                    'payment_id'  => $paymentId,
                    'order_id'    => $orderId,
                    'purchase_id' => $purchaseId,
                ]);
                return;
            }

            // Anti-doublon
            if ($purchase->status === 'paid') {
                Log::info('Achat produit déjà payé, webhook ignoré (doublon)', [
                    'purchase_id' => $purchase->id,
                    'payment_id'  => $paymentId,
                ]);
                return;
            }

            // Confirmation du paiement : IP de confiance OU vérification API
            $authorized = false;
            if ($this->isTrustedHelloAssoIp($sourceIps) && $state === 'Authorized') {
                $authorized = true;
            } else {
                $checkoutIntentId = $purchase->helloasso_checkout_intent_id
                    ?? \Cache::get('helloasso_intent_for_order_' . $orderId);
                $verified = $this->helloAssoService->verifyPayment($paymentId, $orderId, $checkoutIntentId);
                if ($verified && ($verified['state'] ?? null) === 'Authorized') {
                    $authorized = true;
                }
            }

            if (!$authorized) {
                $purchase->update([
                    'helloasso_order_id'   => $orderId,
                    'helloasso_payment_id' => $paymentId,
                    'webhook_data'         => $payment,
                    'error_message'        => 'Paiement non confirmé (état : ' . $state . ')',
                ]);
                Log::warning('Paiement produit non confirmé, achat laissé en attente', [
                    'purchase_id' => $purchase->id,
                    'payment_id'  => $paymentId,
                    'state'       => $state,
                ]);
                return;
            }

            $purchase->update([
                'helloasso_order_id'   => $orderId,
                'helloasso_payment_id' => $paymentId,
                'status'               => 'paid',
                'paid_at'              => now(),
                'webhook_data'         => $payment,
                'error_message'        => null,
            ]);

            Log::info('Achat produit confirmé et payé', [
                'purchase_id' => $purchase->id,
                'product'     => $purchase->product_title,
                'amount'      => $purchase->amount_cts / 100,
                'payer_email' => $purchase->payer_email,
            ]);

            $this->sendProductPurchaseEmails($purchase);

        } catch (\Exception $e) {
            Log::error('Erreur lors du traitement du paiement produit', [
                'message'  => $e->getMessage(),
                'metadata' => $metadata,
            ]);
        }
    }

    /**
     * Envoie l'email de confirmation au payeur + la notification aux admins.
     */
    private function sendProductPurchaseEmails(ProductPurchase $purchase): void
    {
        // Email de confirmation au payeur
        try {
            Mail::to($purchase->payer_email)->send(new ProductPurchaseConfirmation($purchase));
        } catch (\Throwable $e) {
            Log::error('Erreur envoi email confirmation achat produit', [
                'purchase_id' => $purchase->id,
                'email'       => $purchase->payer_email,
                'message'     => $e->getMessage(),
            ]);
        }

        // Notification aux admins disposant du droit produits
        try {
            $adminUserIds = usersAttributes::whereIn('attributeName', ['admin:produits', 'admin:super'])
                ->pluck('userId')
                ->unique();

            $admins = User::where('isAdmin', 1)
                ->whereIn('id', $adminUserIds)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email)->send(new ProductPurchaseAdminNotification($purchase));
            }
        } catch (\Throwable $e) {
            Log::error('Erreur envoi notification admin achat produit', [
                'purchase_id' => $purchase->id,
                'message'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Vérifie si un paiement HelloAsso a déjà été traité (anti-doublon)
     */
    private function isPaymentAlreadyProcessed(string $paymentId): bool
    {
        // Vérifier dans les transactions existantes
        if (transaction::where('observation', 'LIKE', '%paiement : ' . $paymentId . '%')->exists()) {
            return true;
        }

        // Vérifier dans les pending payments déjà traités
        if (HelloAssoPendingPayment::where('payment_id', $paymentId)->where('status', 'processed')->exists()) {
            return true;
        }

        return false;
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

        // Stocker le checkoutIntentId sur la row pending si elle existe (le webhook
        // peut être arrivé avant ce retour). Sinon, on l'écrira au prochain webhook
        // via le cache ci-dessous.
        if ($checkoutIntentId && $orderId) {
            $updated = HelloAssoPendingPayment::where('order_id', $orderId)
                ->whereNull('checkout_intent_id')
                ->update(['checkout_intent_id' => $checkoutIntentId]);

            if ($updated) {
                Log::info('HelloAsso retour: checkout_intent_id stocké sur la row pending', [
                    'order_id'           => $orderId,
                    'checkout_intent_id' => $checkoutIntentId,
                ]);
            } else {
                // Pas encore de row → on garde le mapping en cache pour que le webhook le récupère
                \Cache::put('helloasso_intent_for_order_' . $orderId, $checkoutIntentId, now()->addDay());
                Log::info('HelloAsso retour: checkout_intent_id mis en cache (webhook pas encore arrivé)', [
                    'order_id'           => $orderId,
                    'checkout_intent_id' => $checkoutIntentId,
                ]);
            }
        }

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
