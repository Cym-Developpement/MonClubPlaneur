# Liste des pilotes

## Filtres

Le bouton **Filtres** (en haut à droite) permet de restreindre la liste affichée :

| Filtre | Contenu |
|---|---|
| **Actif uniquement** | Uniquement les pilotes dont le statut est Actif (défaut) |
| **Actifs et Inactifs** | Tous les pilotes sans exception |
| **Adhérents AAAA** | Pilotes ayant une transaction de type *Cotisation AAAA* — reflète les adhérents réels d'une année |

Le filtre actif est indiqué dans le titre de la liste, avec le nombre de résultats entre parenthèses.

---

## Recherche et tri

La liste utilise **DataTables** : elle est triable par colonne (clic sur l'entête) et dispose d'un champ de **recherche** permettant de filtrer par nom, email, numéro de licence ou solde.

---

## Colonnes du tableau

| Colonne | Description |
|---|---|
| **Nom** | Nom complet du pilote |
| **FFVP** | Numéro de licence fédérale |
| **E-mail** | Adresse email |
| **Solde** | Solde du compte — **vert** si positif ou nul, **rouge** si débiteur. Cliquer ouvre directement le compte pilote |
| **Attributs** | Badges des attributs associés au pilote (ex. : instructeur, élève, technique…) |
| **Action** | Interrupteur actif/inactif + menu d'actions |

La **ligne de total** en bas du tableau affiche la somme de tous les soldes de la liste filtrée.

---

## Activer / Désactiver un pilote

L'interrupteur dans la colonne Action bascule instantanément le statut du pilote entre **Actif** et **Inactif**, sans rechargement de page.

> Un pilote inactif n'apparaît plus dans les listes de saisie et n'est plus inclus dans les envois d'emails automatiques.

---

## Menu d'actions (bouton bleu ⓘ)

Un clic sur le bouton bleu ouvre un menu par pilote :

| Action | Description |
|---|---|
| **Compte pilote** | Ouvre la page de saisie/visualisation du compte (nouvel onglet) |
| **Carnet de vol AAAA** | Ouvre le carnet de vol du pilote pour l'année sélectionnée (nouvel onglet) |
| **Accès administrateur temporaire** | Génère un accès admin temporaire pour ce pilote — utile pour diagnostiquer un problème depuis son point de vue |
| **Modifier l'utilisateur** | Ouvre la fiche de modification du pilote (droits, attributs, informations) |
| **Envoyer extrait de compte** | Envoie immédiatement par email l'extrait de compte du pilote (PDF en pièce jointe) |

Le menu affiche également des **statistiques de l'année en cours** en lecture seule :
- HDV (heures de vol totales)
- HDV facturable (heures facturées)
- Jours de vol et jours de vol facturables

---

## Envoi d'emails groupés

Le bouton **Envoyer un email** (en haut à droite) donne accès à trois actions d'envoi en masse :

### Email compte débiteur
Envoie une notification aux pilotes dont le solde est **négatif** et dont le statut est actif.
Une page de prévisualisation liste les destinataires avant confirmation.

### État de compte adhérents (AAAA)
Envoie l'extrait de compte complet (PDF en pièce jointe) à tous les pilotes ayant une cotisation pour l'année sélectionnée.
Une page de prévisualisation liste les destinataires avec leur solde avant confirmation.

> Chaque prévisualisation propose un **envoi de test** (à l'administrateur connecté uniquement) pour vérifier le rendu avant l'envoi réel.

---

## Export CSV

Le bouton **Export CSV** télécharge la liste des pilotes selon le filtre actif au format CSV (compatible Excel).

---

## Tableau des totaux

En bas de page, un récapitulatif affiche pour la liste filtrée :
- **Nombre d'adhérents**
- **Total des soldes**
- **Total des comptes positifs**
- **Total des comptes négatifs**
- **Nombre de pilotes par attribut** (instructeur, élève, etc.)
