# Guide de l'interface de transaction avec export de facture

## Nouvelle fonctionnalité ajoutée

Un dropdown Bootstrap 5 a été ajouté à côté du bouton "Export PDF" dans la page de transaction pour permettre l'export de facture avec sélection d'année.

## Interface utilisateur

### Boutons d'export
- **Export PDF** : Bouton bleu pour l'export de compte complet
- **Export Facture** : Dropdown orange pour l'export de facture par année

### Dropdown "Export Facture"
Le dropdown contient 4 options :
- **Année courante** : `{{ date('Y') }}` (ex: 2025)
- **Année précédente** : `{{ date('Y') - 1 }}` (ex: 2024)
- **Il y a 2 ans** : `{{ date('Y') - 2 }}` (ex: 2023)
- **Il y a 3 ans** : `{{ date('Y') - 3 }}` (ex: 2022)

## Structure HTML

```html
<a href="/accountExport?user={{ $selectedUser }}" target="_blank" class="btn btn-info btn-sm float-right">
  Export PDF
</a>

<div class="dropdown float-right" style="margin-right: 10px;">
  <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
    Export Facture
  </button>
  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
    <li><a class="dropdown-item" href="/invoiceExport?user={{ $selectedUser }}&year={{ date('Y') }}">
      Année {{ date('Y') }}
    </a></li>
    <li><a class="dropdown-item" href="/invoiceExport?user={{ $selectedUser }}&year={{ date('Y') - 1 }}">
      Année {{ date('Y') - 1 }}
    </a></li>
    <li><a class="dropdown-item" href="/invoiceExport?user={{ $selectedUser }}&year={{ date('Y') - 2 }}">
      Année {{ date('Y') - 2 }}
    </a></li>
    <li><a class="dropdown-item" href="/invoiceExport?user={{ $selectedUser }}&year={{ date('Y') - 3 }}">
      Année {{ date('Y') - 3 }}
    </a></li>
  </ul>
</div>
```

## Fonctionnalités

### Export PDF (bouton bleu)
- **URL** : `/accountExport?user={id}`
- **Contenu** : Toutes les transactions de l'année courante
- **Format** : Extrait de compte complet

### Export Facture (dropdown orange)
- **URL** : `/invoiceExport?user={id}&year={année}`
- **Contenu** : Seulement les transactions négatives de l'année sélectionnée
- **Format** : Facture des montants dus

## Utilisation

1. **Sélectionner un utilisateur** dans le dropdown principal
2. **Cliquer sur "Export PDF"** pour l'extrait de compte complet
3. **Cliquer sur "Export Facture"** pour ouvrir le dropdown
4. **Sélectionner une année** dans le dropdown de facture
5. **Le PDF se télécharge** automatiquement

## Styles Bootstrap 5

- **dropdown** : Conteneur principal du dropdown (selon [Bootstrap 5.3 docs](https://getbootstrap.com/docs/5.3/components/dropdowns/))
- **btn-warning** : Couleur orange pour le bouton de facture
- **dropdown-toggle** : Indicateur de dropdown
- **data-bs-toggle="dropdown"** : Activation du dropdown Bootstrap 5
- **id="dropdownMenuButton1"** : Identifiant unique du bouton
- **aria-labelledby="dropdownMenuButton1"** : Liaison accessibilité entre bouton et menu
- **dropdown-menu** : Menu déroulant
- **dropdown-item** : Éléments du menu

## Avantages

- ✅ **Interface intuitive** : Boutons groupés et cohérents
- ✅ **Sélection d'année** : Accès facile aux 4 dernières années
- ✅ **Distinction claire** : Couleurs différentes pour PDF et facture
- ✅ **Responsive** : Compatible avec Bootstrap 5
- ✅ **Accessible** : Utilise les attributs ARIA appropriés

## Notes techniques

- **Bootstrap 5** : Utilise `data-bs-toggle` au lieu de `data-toggle`
- **PHP dynamique** : Les années sont calculées dynamiquement avec `date('Y')`
- **URLs générées** : Les liens sont générés automatiquement avec l'ID utilisateur
- **Target blank** : Les exports s'ouvrent dans un nouvel onglet
