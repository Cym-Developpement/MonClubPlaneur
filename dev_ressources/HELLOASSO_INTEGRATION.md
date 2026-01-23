# Intégration HelloAsso Checkout

## Configuration

Ajoutez les variables suivantes à votre fichier `.env` :

```env
# Configuration HelloAsso
HELLOASSO_API_URL=https://api.helloasso.com/v5
HELLOASSO_ORGANIZATION_SLUG=votre-slug-association
HELLOASSO_ACCESS_TOKEN=votre-token-acces
HELLOASSO_CLIENT_ID=votre-client-id
HELLOASSO_CLIENT_SECRET=votre-client-secret
```

## Utilisation

### 1. Afficher un bouton de paiement

#### Bouton simple avec le design officiel HelloAsso
```php
// Dans une vue Blade
@include('partials.helloasso-button', [
    'amount' => 50.00,
    'itemName' => 'Adhésion au club',
    'containsDonation' => false
])
```

Le bouton utilise maintenant le design officiel HelloAsso avec :
- Logo HelloAsso officiel
- Design conforme aux standards HelloAsso
- Logos de sécurité (Visa, Mastercard, CB, PCI DSS)
- Animations et effets de survol

#### Formulaire de paiement complet
```php
@include('partials.helloasso-form', [
    'amount' => 100.00,
    'itemName' => 'Cotisation annuelle 2024',
    'containsDonation' => true,
    'payer' => [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john@example.com'
    ],
    'metadata' => [
        'userId' => auth()->id(),
        'type' => 'cotisation'
    ]
])
```

Le formulaire complet inclut :
- Affichage du montant et de la description
- Informations de sécurité
- Bouton HelloAsso officiel
- Logos de certification

#### Paiement avec échéances
```php
@include('partials.helloasso-form', [
    'amount' => 300.00,
    'itemName' => 'Formation pilote - Paiement échelonné',
    'containsDonation' => false,
    'terms' => [
        [
            'amount' => 10000, // 100€ en centimes
            'date' => '2024-02-01'
        ],
        [
            'amount' => 10000, // 100€ en centimes
            'date' => '2024-03-01'
        ]
    ]
])
```

### 2. Créer un paiement programmatiquement

```php
use App\Services\HelloAssoService;

$helloAssoService = new HelloAssoService();

// Données du paiement
$paymentData = $helloAssoService->buildPaymentData(
    1000, // Montant total en centimes (10.00€)
    1000, // Montant initial en centimes (10.00€)
    'Adhésion au club', // Description
    'https://votre-site.com/helloasso/back', // URL de retour si annulation
    'https://votre-site.com/helloasso/error', // URL de retour en cas d'erreur
    'https://votre-site.com/helloasso/return', // URL de retour après paiement
    false, // Contient un don
    [ // Données du payeur (optionnel)
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john@example.com',
        'dateOfBirth' => '1986-07-06',
        'address' => '23 rue du palmier',
        'city' => 'Paris',
        'zipCode' => '75000',
        'country' => 'FRA'
    ],
    [ // Métadonnées (optionnel)
        'reference' => 12345,
        'userId' => 98765
    ]
);

// Créer l'intention de paiement
$result = $helloAssoService->createCheckoutIntent($paymentData);

if ($result && isset($result['redirectUrl'])) {
    // Rediriger vers HelloAsso
    return redirect($result['redirectUrl']);
}
```

### 3. Valider un paiement

```php
// Dans votre callback de retour
$checkoutIntentId = $request->get('checkoutIntentId');
$orderId = $request->get('orderId');

$isValid = $helloAssoService->validatePayment($checkoutIntentId, $orderId);

if ($isValid) {
    // Paiement validé avec succès
    // Traiter le paiement dans votre application
}
```

### 4. Gérer les notifications

Les notifications HelloAsso sont automatiquement traitées par le contrôleur. Vous pouvez personnaliser les méthodes `handleOrderNotification` et `handlePaymentNotification` dans `HelloAssoController`.

## Options disponibles

### Options pour les boutons de paiement

| Option | Type | Défaut | Description |
|--------|------|--------|-------------|
| `amount` | float/int | 0 | Montant en euros (sera converti en centimes) |
| `itemName` | string | 'Paiement' | Description de l'achat |
| `containsDonation` | boolean | false | Indique si c'est un don |
| `payer` | array | [] | Données du payeur (firstName, lastName, email, etc.) |
| `metadata` | array | [] | Métadonnées personnalisées |
| `terms` | array | [] | Échéances pour paiement échelonné |
| `buttonText` | string | 'Payer avec HelloAsso' | Texte du bouton |
| `buttonClass` | string | 'btn btn-primary btn-lg' | Classes CSS du bouton |
| `showAmount` | boolean | true | Afficher le montant |
| `currency` | string | '€' | Symbole de la devise |

### Exemple d'échéances
```php
'terms' => [
    [
        'amount' => 10000, // 100€ en centimes
        'date' => '2024-02-01'
    ],
    [
        'amount' => 10000, // 100€ en centimes
        'date' => '2024-03-01'
    ]
]
```

## Routes disponibles

- `POST /helloasso/create-payment` - Créer un paiement
- `POST /helloasso/notification` - Recevoir les notifications HelloAsso
- `GET /helloasso/return` - Retour après paiement réussi
- `GET /helloasso/error` - Retour en cas d'erreur
- `GET /helloasso/back` - Retour si annulation

## Sécurité

⚠️ **Important** : Toujours valider les paiements côté serveur en utilisant la méthode `validatePayment()` avant de traiter un paiement comme réussi. Les paramètres de retour peuvent être falsifiés.

## Documentation HelloAsso

Pour plus de détails, consultez la [documentation officielle HelloAsso](https://dev.helloasso.com/docs/int%C3%A9grer-le-paiement-sur-votre-site).