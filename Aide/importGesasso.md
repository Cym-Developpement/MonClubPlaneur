# Import Gesasso

Permet d'importer une planche de vol exportée depuis l'application **Gesasso** (logiciel de gestion de vols de l'aéroclub) sous forme de fichier CSV.

---

## Étape 1 — Chargement du fichier

Sélectionnez le fichier CSV exporté depuis Gesasso puis cliquez sur **Importer**.

Le fichier est analysé : chaque ligne est comparée aux vols déjà présents en base de données pour détecter les doublons, et chaque pilote/aéronef est résolu automatiquement.

---

## Étape 2 — Vérification avant import

Un tableau récapitulatif s'affiche avec tous les vols du fichier.

### Codes couleur et badges

| Indicateur | Signification |
|---|---|
| Ligne normale | Vol prêt à être importé |
| **Badge rouge "Aéronef Inconnu"** | L'immatriculation de l'appareil ne correspond à aucun appareil enregistré dans l'application |
| **Badge rouge "Pilote Inconnu"** | Le nom du pilote dans Gesasso ne correspond à aucun utilisateur |
| Ligne **barrée** (grisée) | Vol déjà présent en base — masqué automatiquement, non importable |

> Les lignes en erreur (pilote ou aéronef inconnu) ne peuvent pas être cochées. Il faut d'abord créer l'utilisateur ou l'appareil manquant, puis relancer l'import.

### Colonnes du tableau

| Colonne | Description |
|---|---|
| **Importer** | Case à cocher pour inclure ce vol dans l'import |
| **Date** | Date du vol |
| **Début / Fin** | Heures de décollage et d'atterrissage |
| **Appareil** | Immatriculation de l'appareil |
| **Pilote 1 / Pilote 2** | Pilote en commandant de bord et éventuel passager/élève |
| **Remorqueur** | Pilote du remorqueur si vol remorqué |
| **École** | Indicateur de vol école |
| **Centièmes Moteur** | Temps moteur du remorqueur en centièmes d'heure |
| **Lancement** | Type de décollage à sélectionner + tarif calculé |
| **À facturer** | Utilisateur sur lequel la transaction sera imputée (modifiable) |

### Case "Tout cocher"

La case dans l'en-tête de colonne **Importer** coche toutes les lignes valides en un clic.

### Type de lancement (Planeur)

Pour chaque vol de planeur, le type de lancement doit être confirmé :
- La sélection est pré-remplie automatiquement selon les centièmes moteur (remorquage si > 15 centièmes)
- Le tarif correspondant est affiché entre parenthèses pour vérification

### Remorquage automatique

Lorsqu'un vol est de type **remorqué**, l'import crée automatiquement deux vols :
1. Le vol du planeur (facturé au pilote sélectionné)
2. Le vol du remorqueur (durée calculée depuis les centièmes moteur, imputé au compte remorquage)

---

## Étape 3 — Enregistrement

Après vérification, cliquez sur **Enregistrer**. Chaque vol coché est importé :
- Le vol est créé dans la base de données
- La transaction financière correspondante est générée et imputée sur le compte du pilote

Un résumé s'affiche ensuite pour chaque vol importé avec confirmation de la durée, de l'appareil, du pilote et du montant facturé.

---

## Total

En bas du tableau, une ligne **TOTAL** affiche la durée cumulée de tous les vols du fichier (hh:mm).
