# Guide de génération des migrations à partir de la base de données

## Vue d'ensemble

Cette commande permet de générer automatiquement les migrations Laravel à partir de votre base de données existante. Elle est particulièrement utile lorsque vous avez une base de données déjà en place mais que les migrations correspondantes n'existent pas encore.

## Utilisation de base

### Générer toutes les migrations

Pour générer les migrations pour toutes les tables de la base de données (sauf celles ignorées par défaut) :

```bash
php artisan migrate:generate
```

Les tables suivantes sont ignorées par défaut :
- `migrations` (table de suivi des migrations Laravel)
- `sqlite_sequence` (table système SQLite)

### Générer des migrations pour des tables spécifiques

Pour générer les migrations uniquement pour certaines tables :

```bash
php artisan migrate:generate --tables=aircraft,flight,transaction
```

### Ignorer certaines tables

Pour ignorer des tables supplémentaires lors de la génération :

```bash
php artisan migrate:generate --ignore=temp_table,old_table
```

### Spécifier un chemin de sortie

Pour sauvegarder les migrations dans un répertoire spécifique :

```bash
php artisan migrate:generate --path=/chemin/vers/migrations
```

Par défaut, les migrations sont sauvegardées dans `database/migrations`.

## Exemples d'utilisation

### Exemple 1 : Générer toutes les migrations

```bash
php artisan migrate:generate
```

Cela générera une migration pour chaque table de votre base de données.

### Exemple 2 : Générer uniquement les migrations pour les tables principales

```bash
php artisan migrate:generate --tables=users,aircraft,flight
```

### Exemple 3 : Générer toutes les migrations sauf certaines

```bash
php artisan migrate:generate --ignore=temp_data,backup_table
```

## Fonctionnalités

La commande génère automatiquement :

- ✅ **Colonnes** : Tous les types de colonnes sont détectés et mappés correctement
- ✅ **Clés primaires** : Les colonnes `id` auto-incrémentées sont détectées
- ✅ **Clés étrangères** : Les relations entre tables sont préservées
- ✅ **Index** : Les index simples et composés sont générés
- ✅ **Index uniques** : Les contraintes d'unicité sont détectées
- ✅ **Valeurs par défaut** : Les valeurs par défaut sont préservées
- ✅ **Colonnes nullable** : Les colonnes nullable sont correctement identifiées
- ✅ **Timestamps** : Les colonnes `created_at` et `updated_at` sont détectées et générées avec `timestamps()`

## Types de données supportés

La commande mappe automatiquement les types SQLite vers les types Laravel :

| Type SQLite | Type Laravel |
|------------|--------------|
| INTEGER | integer / bigIncrements (si clé primaire) |
| TEXT | text |
| VARCHAR(n) | string |
| REAL / FLOAT / DOUBLE | float |
| BLOB | binary |
| BOOLEAN | boolean |
| DATETIME / TIMESTAMP | timestamp |
| DATE | date |
| TIME | time |

## Limitations

- ⚠️ Les migrations existantes ne sont pas écrasées (un avertissement est affiché)
- ⚠️ Les triggers SQLite ne sont pas convertis en migrations
- ⚠️ Les vues ne sont pas supportées
- ⚠️ Certains types de contraintes complexes peuvent nécessiter une révision manuelle

## Après la génération

1. **Vérifiez les migrations générées** : Ouvrez les fichiers générés et vérifiez qu'ils correspondent à vos attentes
2. **Ajustez si nécessaire** : Certaines migrations peuvent nécessiter des ajustements manuels
3. **Testez les migrations** : Créez une base de données de test et exécutez les migrations pour vérifier qu'elles fonctionnent correctement

```bash
# Créer une base de données de test
touch database/database_test.sqlite

# Configurer .env pour utiliser la base de test
# Puis exécuter les migrations
php artisan migrate --database=sqlite --path=database/migrations
```

## Dépannage

### Erreur : "Aucune table trouvée"

Vérifiez que :
- Votre base de données est correctement configurée dans `.env`
- Le fichier de base de données existe
- Vous avez les permissions nécessaires

### Les migrations générées ne sont pas correctes

- Vérifiez le schéma de votre base de données
- Certains types de données peuvent nécessiter un ajustement manuel
- Les contraintes complexes peuvent ne pas être détectées automatiquement

### Les clés étrangères ne sont pas générées

- Vérifiez que les clés étrangères sont bien définies dans SQLite
- SQLite nécessite que les clés étrangères soient activées : `PRAGMA foreign_keys = ON;`

## Notes importantes

- ⚠️ **Sauvegardez votre base de données** avant d'utiliser cette commande
- ⚠️ Les migrations générées sont des **suggestions** et doivent être **révisées manuellement**
- ⚠️ Cette commande est conçue pour **SQLite** principalement, mais peut être étendue pour MySQL/PostgreSQL
