# Intégration GESASSO — Export des vols

Objectif : envoyer les vols depuis MonClubPlaneur vers le logiciel GESASSO (FFVV)
via son API, en mappant les champs des deux systèmes.

---

## Mapping des champs

### ✅ Disponibles

| Champ GESASSO | Source MonClubPlaneur |
|---|---|
| `flight[date]` | `flightTimestamp` / `takeOffTime` |
| `flight[aircraft][registration][name]` | `aircraft->name` |
| `flight[aircraft][registration][identifier]` | `aircraft->register` |
| `flight[crew][person_one][licence_number][name]` | `idUser->name` |
| `flight[crew][person_one][licence_number][identifier]` | `idUser->licenceNumber` |
| `flight[crew][instruction_flight]` | `idInstructor != 0` |
| `flight[crew][person_two]` (stagiaire si instruction) | `idUser` (quand `idInstructor` = instructeur) |
| `flight[takeoff_time][hour/minute]` | `takeOffTime` |
| `flight[landing_time][hour/minute]` | `landingTime` |
| `flight[takeoff_oaci_code]` | `airportStartCode` |
| `flight[landing_oaci_code]` | `airportEndCode` |
| `flight[launching][takeoff_count]` | `landing` |
| `flight[launching][engine_duration]` | `motorEndTime - motorStartTime` (centièmes) |
| `flight[launching][launching_mode]` | `startType` (mapping à faire, voir ci-dessous) |
| `flight[launching][tow_aircraft]` | via `towingFlightId → aircraft` (si peuplé) |
| `flight[launching][tow_crew][tow_person_one]` | via `towingFlightId → idUser` (si peuplé) |

---

### ❌ Manquant — à implémenter

#### 1. Passager (vol non-instruction)
- Champ GESASSO : `flight[crew][person_two]`
- Aucun champ "passager" sur la table `flights`
- **Action** : ajouter colonne `passengerId` (nullable) sur `flights`

#### 2. Lien remorqueur ↔ vol planeur
- Champs GESASSO : `flight[launching][tow_aircraft]` + `tow_crew[tow_person_one]`
- La colonne **`towingFlightId`** existe déjà dans `flights` mais n'est jamais renseignée
- Une fois peuplée, l'aéronef et le pilote remorqueur sont dérivables automatiquement
- **Action** : renseigner `towingFlightId` lors de l'import OGN
  (les paires remorqueur/planeur sont déjà détectées via `towing`/`tow` dans les données OGN)

#### 3. Commentaire
- Champ GESASSO : `flight[comment]`
- **Action** : ajouter colonne `comment` (text, nullable) sur `flights`

#### 4. Identifiant GESASSO
- Pour éviter les doublons lors d'envois successifs
- **Action** : ajouter colonne `gesassoId` (nullable) sur `flights`
- Un vol avec `gesassoId` non null a déjà été envoyé

#### 5. Mapping `launching_mode`
- GESASSO attend : `AIRCRAFT_TOWING`, `WINCH`, `AUTONOMOUS`, `CAR_TOWING`, `ELASTIC`
- MonClubPlaneur utilise `startType` → table `sailplane_start_prices`
- **Action** : ajouter colonne `gesassoCode` (nullable) sur `sailplane_start_prices`

---

## Hors périmètre (club planeur sans treuil)

- `flight[launching][winch]` — treuil
- `flight[launching][winch_person]` — treuilleur
- Champs `external` (aéronef / pilote externe) — à décider si nécessaire

---

## Ordre d'implémentation suggéré

1. Renseigner `towingFlightId` lors de l'import OGN
2. Ajouter `gesassoId` sur `flights` + `gesassoCode` sur `sailplane_start_prices`
3. Ajouter `passengerId` et `comment` sur `flights`
4. Développer le service d'export vers l'API GESASSO

---

## Références

- Code association GESASSO : `547902`
- Routes API utilisées dans le formulaire :
  - Autocomplete aéronefs : `ffvvgesasso_front_api_aircraftautocomplete`
  - Autocomplete pilotes : `ffvvgesasso_front_api_personautocomplete`
  - Autocomplete OACI : `ffvvgesasso_front_api_oacicodeautocomplete`
  - Création aéronef : `ffvvgesasso_front_equipment_createaircraft`
  - Création profil : `ffvvgesasso_front_person_createprofile`
