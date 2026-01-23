# Nouvelle fonctionnalité : Export de facture (invoiceExport)

## Description

La fonction `invoiceExport` permet d'exporter uniquement les transactions négatives d'un utilisateur sous forme de PDF, créant ainsi une facture des montants dus.

## Fonctionnalités

### ✅ Export de facture
- **URL** : `/invoiceExport?user={id}&year={année}`
- **Route** : `invoiceExport`
- **Méthode** : `GET`
- **Middleware** : `can:admin` (réservé aux administrateurs)

### 📋 Critères de sélection
- **Année** : Paramètre `year` (année courante par défaut si non spécifiée)
- **Transactions** : Seulement les transactions avec `value < 0` (négatives)
- **Tri** : Par date et ID croissants

### 📄 Contenu du PDF
- **Titre** : "Facture" au lieu de "Extrait de compte"
- **Période** : Année sélectionnée (ou année courante par défaut)
- **Date d'émission** : Date actuelle
- **Tableau** : Transactions négatives uniquement (Date, Description, Montant en positif)
- **Total** : Somme des montants en positif
- **Solde du compte** : Information sur le solde créditeur/débiteur en petit italique (rouge si débiteur)
- **Instructions de paiement** : Informations pour régler la facture (chèque, virement, carte bancaire via https://compte.cvvt.fr/don)

## Utilisation

### 1. Accès via URL
```
/invoiceExport?user=123&year=2024
```

### 2. Accès via route nommée
```php
route('invoiceExport', ['user' => $userId, 'year' => 2024])
```

### 3. Dans une vue Blade
```html
<a href="{{ route('invoiceExport', ['user' => $user->id, 'year' => 2024]) }}" class="btn btn-primary">
    Exporter la facture 2024
</a>
```

## Fichiers créés/modifiés

### 1. Contrôleur
- **Fichier** : `app/Http/Controllers/admin.php`
- **Fonction** : `invoiceExport(Request $request)`
- **Ligne** : 703-739

### 2. Route
- **Fichier** : `routes/web.php`
- **Route** : `Route::get('/invoiceExport', [App\Http\Controllers\admin::class, 'invoiceExport'])->name('invoiceExport')->middleware('can:admin');`
- **Ligne** : 69

### 3. Vue PDF
- **Fichier** : `resources/views/exportPdfInvoice.blade.php`
- **Contenu** : Template PDF spécialisé pour les factures

## Différences avec accountExport

| Aspect | accountExport | invoiceExport |
|--------|---------------|---------------|
| **Transactions** | Toutes | Négatives uniquement |
| **Titre PDF** | "Extrait de compte" | "Facture" |
| **Nom fichier** | `CVVT-NOM_DATE.pdf` | `CVVT-FACTURE-NOM_ANNEE_DATE.pdf` |
| **Colonnes** | Date, Description, Montant, Solde | Date, Description, Montant |
| **Total** | Solde du compte | Montant à payer |
| **Message** | Approvisionnement si négatif | Instructions de paiement |

## Exemple d'utilisation

```php
// Dans un contrôleur ou une vue
$userId = 123;
$year = 2024;
$invoiceUrl = route('invoiceExport', ['user' => $userId, 'year' => $year]);

// Ou directement
$invoiceUrl = url('/invoiceExport?user=' . $userId . '&year=' . $year);

// Année courante par défaut (si pas de paramètre year)
$invoiceUrl = route('invoiceExport', ['user' => $userId]);
```

## Sécurité

- **Accès restreint** : Seuls les administrateurs peuvent accéder à cette fonction
- **Validation** : Vérification de l'existence de l'utilisateur
- **Gestion d'erreur** : Affichage d'erreur si aucun utilisateur spécifié

## Test

Pour tester la fonctionnalité :

1. **Connectez-vous en tant qu'administrateur**
2. **Accédez à** : `/invoiceExport?user={id_utilisateur}&year={année}`
3. **Vérifiez** : Le PDF se télécharge avec le nom `CVVT-FACTURE-NOM_ANNEE_DATE.pdf`
4. **Contrôlez** : Seules les transactions négatives de l'année sélectionnée apparaissent (montants affichés en positif)
5. **Validez** : Le total correspond à la somme des montants en positif
6. **Testez** : Sans paramètre `year` (utilise l'année courante par défaut)
