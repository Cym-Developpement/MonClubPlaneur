<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class HelloAssoService
{
    private $apiUrl;
    private $oauthUrl;
    private $organizationSlug;
    private $clientId;
    private $clientSecret;
    private $isSandbox;

    public function __construct()
    {
        $this->isSandbox = config('services.helloasso.sandbox', false);
        
        // Définir les URLs selon le mode (production ou sandbox)
        if ($this->isSandbox) {
            $this->apiUrl = 'https://api.helloasso-sandbox.com/v5';
            $this->oauthUrl = 'https://api.helloasso-sandbox.com/oauth2';
        } else {
            $this->apiUrl = 'https://api.helloasso.com/v5';
            $this->oauthUrl = 'https://api.helloasso.com/oauth2';
        }
        
        $this->organizationSlug = config('services.helloasso.organization_slug');
        $this->clientId = config('services.helloasso.client_id');
        $this->clientSecret = config('services.helloasso.client_secret');
    }

    /**
     * Créer une intention de paiement HelloAsso Checkout
     *
     * @param array $data Données du paiement
     * @return array|null
     */
    public function createCheckoutIntent(array $data)
    {
        try {
            $accessToken = $this->getValidAccessToken();
            if (!$accessToken) {
                Log::error('HelloAssoService: Impossible d\'obtenir un jeton d\'accès.');
                return null;
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->post($this->apiUrl . '/organizations/' . $this->organizationSlug . '/checkout-intents', $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erreur HelloAsso Checkout Intent', [
                'status' => $response->status(),
                'body' => $response->body(),
                'data' => $data
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Exception HelloAsso Checkout Intent', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);

            return null;
        }
    }

    /**
     * Récupérer les détails d'une intention de paiement
     *
     * @param string $checkoutIntentId
     * @return array|null
     */
    public function getCheckoutIntent(string $checkoutIntentId)
    {
        try {
            $accessToken = $this->getValidAccessToken();
            if (!$accessToken) {
                Log::error('HelloAssoService: Impossible d\'obtenir un jeton d\'accès.');
                return null;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($this->apiUrl . '/organizations/' . $this->organizationSlug . '/checkout-intents/' . $checkoutIntentId);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erreur HelloAsso Get Checkout Intent', [
                'status' => $response->status(),
                'body' => $response->body(),
                'checkoutIntentId' => $checkoutIntentId
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Exception HelloAsso Get Checkout Intent', [
                'message' => $e->getMessage(),
                'checkoutIntentId' => $checkoutIntentId
            ]);

            return null;
        }
    }

    /**
     * Récupérer les détails d'une commande
     *
     * @param string $orderId
     * @return array|null
     */
    public function getOrder(string $orderId)
    {
        try {
            $accessToken = $this->getValidAccessToken();
            if (!$accessToken) {
                Log::error('HelloAssoService: Impossible d\'obtenir un jeton d\'accès.');
                return null;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($this->apiUrl . '/organizations/' . $this->organizationSlug . '/orders/' . $orderId);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Erreur HelloAsso Get Order', [
                'status' => $response->status(),
                'body' => $response->body(),
                'orderId' => $orderId
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Exception HelloAsso Get Order', [
                'message' => $e->getMessage(),
                'orderId' => $orderId
            ]);

            return null;
        }
    }

    /**
     * Valider un paiement en récupérant les détails de la commande
     *
     * @param string $checkoutIntentId
     * @param string $orderId
     * @return bool
     */
    public function validatePayment(string $checkoutIntentId, string $orderId)
    {
        try {
            // Récupérer les détails de l'intention de paiement
            $checkoutIntent = $this->getCheckoutIntent($checkoutIntentId);
            Log::warning('checkoutIntent', $checkoutIntent);
            if (!$checkoutIntent) {
                return false;
            }

            // Récupérer les détails de la commande
            $order = $this->getOrder($orderId);
            
            if (!$order) {
                return false;
            }

            // Vérifier que la commande est bien liée à cette intention de paiement
            if ($order['checkoutIntentId'] !== $checkoutIntentId) {
                Log::warning('HelloAsso: Order not linked to checkout intent', [
                    'checkoutIntentId' => $checkoutIntentId,
                    'orderId' => $orderId,
                    'orderCheckoutIntentId' => $order['checkoutIntentId'] ?? 'N/A'
                ]);
                return false;
            }

            // Vérifier le statut de la commande
            if ($order['state'] !== 'Processed') {
                Log::warning('HelloAsso: Order not processed', [
                    'orderId' => $orderId,
                    'state' => $order['state'] ?? 'N/A'
                ]);
                return false;
            }

            return true;

        } catch (Exception $e) {
            Log::error('Exception HelloAsso Validate Payment', [
                'message' => $e->getMessage(),
                'checkoutIntentId' => $checkoutIntentId,
                'orderId' => $orderId
            ]);

            return false;
        }
    }

    /**
     * Créer les données de base pour un paiement
     *
     * @param int $totalAmount Montant total en centimes
     * @param int $initialAmount Montant initial en centimes
     * @param string $itemName Description de l'achat
     * @param string $backUrl URL de retour si annulation
     * @param string $errorUrl URL de retour en cas d'erreur
     * @param string $returnUrl URL de retour après paiement
     * @param bool $containsDonation Indique si c'est un don
     * @param array $payer Données du payeur (optionnel)
     * @param array $metadata Métadonnées (optionnel)
     * @param array $terms Échéances (optionnel)
     * @return array
     */
    public function buildPaymentData(
        int $totalAmount,
        int $initialAmount,
        string $itemName,
        string $backUrl,
        string $errorUrl,
        string $returnUrl,
        bool $containsDonation = false,
        array $payer = [],
        array $metadata = [],
        array $terms = []
    ): array {
        $data = [
            'totalAmount' => $totalAmount,
            'initialAmount' => $initialAmount,
            'itemName' => $itemName,
            'backUrl' => $backUrl,
            'errorUrl' => $errorUrl,
            'returnUrl' => $returnUrl,
            'containsDonation' => $containsDonation,
        ];

        if (!empty($payer)) {
            $data['payer'] = $payer;
        }

        if (!empty($metadata)) {
            $data['metadata'] = $metadata;
        }

        if (!empty($terms)) {
            $data['terms'] = $terms;
        }

        return $data;
    }

    /**
     * Obtenir un access token et un refresh token selon la documentation HelloAsso
     * L'access token expire après 30 minutes
     * 
     * @return array|null Retourne ['access_token' => string, 'refresh_token' => string, 'expires_in' => int] ou null
     */
    public function getAccessToken()
    {
        try {
            $response = Http::asForm()->post($this->oauthUrl . '/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Vérifier que nous avons bien les tokens requis
                if (isset($data['access_token']) && isset($data['refresh_token'])) {
                    Log::info('HelloAsso Access Token obtenu avec succès', [
                        'environment' => $this->isSandbox ? 'sandbox' : 'production',
                        'oauth_url' => $this->oauthUrl,
                        'expires_in' => $data['expires_in'] ?? 'non spécifié'
                    ]);
                    
                    return [
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'],
                        'expires_in' => $data['expires_in'] ?? 1800, // 30 minutes par défaut
                        'token_type' => $data['token_type'] ?? 'Bearer'
                    ];
                }
                
                Log::error('HelloAsso Access Token - Réponse incomplète', [
                    'response' => $data
                ]);
                
                return null;
            }

            Log::error('Erreur HelloAsso Get Access Token', [
                'status' => $response->status(),
                'body' => $response->body(),
                'client_id' => $this->clientId
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Exception HelloAsso Get Access Token', [
                'message' => $e->getMessage(),
                'client_id' => $this->clientId
            ]);

            return null;
        }
    }

    /**
     * Rafraîchir un access token en utilisant le refresh token
     * 
     * @param string $refreshToken Le refresh token
     * @return array|null Retourne ['access_token' => string, 'refresh_token' => string, 'expires_in' => int] ou null
     */
    public function refreshAccessToken(string $refreshToken)
    {
        try {
            $response = Http::asForm()->post($this->oauthUrl . '/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['access_token']) && isset($data['refresh_token'])) {
                    Log::info('HelloAsso Access Token rafraîchi avec succès', [
                        'environment' => $this->isSandbox ? 'sandbox' : 'production',
                        'oauth_url' => $this->oauthUrl,
                        'expires_in' => $data['expires_in'] ?? 'non spécifié'
                    ]);
                    
                    return [
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'],
                        'expires_in' => $data['expires_in'] ?? 1800,
                        'token_type' => $data['token_type'] ?? 'Bearer'
                    ];
                }
                
                Log::error('HelloAsso Refresh Token - Réponse incomplète', [
                    'response' => $data
                ]);
                
                return null;
            }

            Log::error('Erreur HelloAsso Refresh Access Token', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Exception HelloAsso Refresh Access Token', [
                'message' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Obtenir un access token valide (obtient toujours un nouveau token)
     * 
     * @return string|null L'access token ou null en cas d'erreur
     */
    public function getValidAccessToken()
    {
        // Obtenir un nouveau token
        $tokenData = $this->getAccessToken();
        return $tokenData['access_token'] ?? null;
    }

    /**
     * Vérifier si le service est en mode sandbox
     * 
     * @return bool
     */
    public function isSandboxMode()
    {
        return $this->isSandbox;
    }

    /**
     * Obtenir l'URL de l'API actuelle (production ou sandbox)
     * 
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Obtenir l'URL OAuth actuelle (production ou sandbox)
     * 
     * @return string
     */
    public function getOauthUrl()
    {
        return $this->oauthUrl;
    }

    /**
     * Vérifier un paiement HelloAsso via l'API
     * Utilise l'endpoint GET /payments/{paymentId} selon la documentation HelloAsso
     * 
     * @param string $paymentId
     * @param string $orderId
     * @return array|null
     */
    public function verifyPayment(string $paymentId, string $orderId)
    {
        try {
            $accessToken = $this->getValidAccessToken();
            if (!$accessToken) {
                Log::error('HelloAssoService: Impossible d\'obtenir un jeton d\'accès pour vérifier le paiement.');
                return null;
            }

            // Utiliser l'endpoint correct selon la documentation HelloAsso
            // GET https://api.helloasso.com/v5/payments/{paymentId}
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($this->apiUrl . '/payments/' . $paymentId);

            if ($response->successful()) {
                $paymentData = $response->json();
                
                Log::info('Paiement HelloAsso récupéré via API', [
                    'payment_id' => $paymentId,
                    'api_endpoint' => $this->apiUrl . '/payments/' . $paymentId,
                    'response_data' => $paymentData
                ]);
                
                // Vérifier que le paiement correspond à la commande
                if (isset($paymentData['order']['id']) && $paymentData['order']['id'] == $orderId) {
                    Log::info('Paiement HelloAsso vérifié avec succès', [
                        'payment_id' => $paymentId,
                        'order_id' => $orderId,
                        'amount' => $paymentData['amount'] ?? 'N/A',
                        'state' => $paymentData['state'] ?? 'N/A',
                        'installment_number' => $paymentData['installmentNumber'] ?? 'N/A',
                        'payment_means' => $paymentData['paymentMeans'] ?? 'N/A',
                        'date' => $paymentData['date'] ?? 'N/A'
                    ]);
                    
                    return $paymentData;
                } else {
                    Log::error('Paiement HelloAsso ne correspond pas à la commande', [
                        'payment_id' => $paymentId,
                        'expected_order_id' => $orderId,
                        'actual_order_id' => $paymentData['order']['id'] ?? 'N/A'
                    ]);
                    return null;
                }
            }

            Log::error('Erreur HelloAsso Get Payment', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payment_id' => $paymentId,
                'api_endpoint' => $this->apiUrl . '/payments/' . $paymentId
            ]);

            return null;

        } catch (Exception $e) {
            Log::error('Exception HelloAsso Get Payment', [
                'message' => $e->getMessage(),
                'payment_id' => $paymentId,
                'api_endpoint' => $this->apiUrl . '/payments/' . $paymentId
            ]);

            return null;
        }
    }

    /**
     * Obtenir des informations sur l'environnement actuel
     * 
     * @return array
     */
    public function getEnvironmentInfo()
    {
        return [
            'is_sandbox' => $this->isSandbox,
            'api_url' => $this->apiUrl,
            'oauth_url' => $this->oauthUrl,
            'environment' => $this->isSandbox ? 'sandbox' : 'production',
            'organization_slug' => $this->organizationSlug,
            'has_client_credentials' => !empty($this->clientId) && !empty($this->clientSecret),
            'token_management' => 'automatic' // Les tokens sont maintenant gérés automatiquement
        ];
    }
}