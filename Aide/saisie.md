# Saisie de transaction

## Sélectionner un pilote

Utilisez la liste déroulante en haut de page pour sélectionner le pilote concerné, puis cliquez sur **Afficher**. L'historique de ses transactions apparaît ainsi que les formulaires de saisie.

Les transactions de l'année en cours sont affichées directement. Les années précédentes sont masquées par défaut — un bouton **Afficher/Masquer** permet de les consulter.

Les lignes en **jaune** correspondent à des transactions en attente de validation.

---

## Enregistrer un vol

Le bouton **Enregistrer un vol** (en haut à droite) ouvre le formulaire de saisie de vol.

### Champs du formulaire

| Champ | Description |
|---|---|
| **Instructeur** | Optionnel — sélectionnez l'instructeur ou le passager présent à bord |
| **Utilisateur à facturer** | Par défaut le pilote sélectionné, modifiable si le vol est facturé à quelqu'un d'autre |
| **Appareil** | Sélectionnez l'appareil — le type de formulaire s'adapte automatiquement |
| **Heure de décollage** | Format `jj/mm/aaaa HH:MM` — saisie avec masque |
| **Heure d'atterrissage** | Idem — le temps de vol est **calculé automatiquement** à partir des deux heures |
| **Temps de vol** | En minutes — se remplit automatiquement si les heures sont saisies, sinon saisie manuelle |
| **Nombre de décollages** | Nombre de tours de piste ou de treuillées (défaut : 1) |

### Selon le type d'appareil

**Planeur** — un champ supplémentaire apparaît :
- **Type de décollage** : remorqué, treuil, autonome, etc. — chaque type a un tarif spécifique

**Avion / TMG / ULM** — deux champs supplémentaires :
- **Compteur moteur au départ** et **à l'arrivée** — le temps moteur est déduit de ces valeurs

### Calcul du prix

Le bouton **Calculer** (ou toute modification d'un champ) interroge le serveur et affiche le montant calculé selon les tarifs configurés.

> Le bouton **Enregistrer** reste grisé tant que l'appareil et l'heure de décollage ne sont pas renseignés.

### Résumé planche de vol

Un tableau récapitulatif en bas de la fenêtre affiche en temps réel la ligne telle qu'elle apparaîtra dans la planche de vol : date, appareil, pilote, instructeur, heures, durée et type.

### Enregistrement

- **Enregistrer** — sauvegarde le vol et réinitialise le formulaire pour saisir un vol suivant sans fermer la fenêtre
- **Enregistrer & fermer** — sauvegarde et ferme la fenêtre, la page se recharge

---

## Saisie Rapide

Permet d'enregistrer une transaction depuis une liste de types prédéfinis.

1. Choisissez **Encaissement (+)** ou **Vente (-)** selon le sens de la transaction
2. Sélectionnez le **type de transaction** — le montant se pré-remplit automatiquement si un tarif est configuré
3. Modifiez le **montant** si nécessaire
4. Cliquez sur **Ajouter**

---

## Saisie Complète

Permet de créer une transaction libre avec un intitulé personnalisé.

- **Intitulé** : texte libre décrivant la transaction
- **Montant** : positif pour un encaissement, négatif pour un débit

---

## Actions sur le compte

En bas du tableau des transactions :

- **Recalculer le solde** — recalcule le solde à partir de zéro en rejouant toutes les transactions (utile après une correction)
- **Supprimer la dernière transaction** — supprime uniquement la transaction la plus récente

---

## Export

- **Export PDF** — génère l'extrait de compte complet du pilote (toutes les transactions)
- **Facture** — génère une facture annuelle (liste déroulante par année)
