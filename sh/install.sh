#!/usr/bin/env bash

set -e

# Se placer à la racine du projet (dossier parent de sh/)
cd "$(dirname "${BASH_SOURCE[0]}")/.."

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

ok()   { echo -e "${GREEN}✔ $*${NC}"; }
info() { echo -e "${CYAN}→ $*${NC}"; }
warn() { echo -e "${YELLOW}⚠ $*${NC}"; }
fail() { echo -e "${RED}✘ $*${NC}"; exit 1; }

echo ""
echo "========================================"
echo "     Installation MonClubPlaneur"
echo "========================================"
echo ""

# ── Vérifications préalables ─────────────────────────────────────────────────

command -v php  >/dev/null 2>&1 || fail "PHP n'est pas installé."
ok "PHP $(php -r 'echo PHP_VERSION;') détecté."

# ── Téléchargement local de Composer ─────────────────────────────────────────

COMPOSER_BIN="./composer.phar"

if [ -f "$COMPOSER_BIN" ]; then
    warn "composer.phar déjà présent, téléchargement ignoré."
else
    info "Téléchargement de Composer..."
    EXPECTED_HASH="$(php -r "copy('https://composer.github.io/installer.sig', 'php://stdout');")"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_HASH="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"
    if [ "$EXPECTED_HASH" != "$ACTUAL_HASH" ]; then
        php -r "unlink('composer-setup.php');"
        fail "Le hash du programme d'installation Composer est invalide."
    fi
    php composer-setup.php --quiet --install-dir=. --filename=composer.phar
    php -r "unlink('composer-setup.php');"
    ok "Composer $(php composer.phar --version --no-ansi | awk '{print $3}') téléchargé."
fi

# ── Fichier .env ──────────────────────────────────────────────────────────────

if [ ! -f .env ]; then
    if [ ! -f .env.example ]; then
        fail ".env.example introuvable."
    fi
    cp .env.example .env
    ok ".env créé depuis .env.example."
else
    warn ".env existant conservé."
fi

# Configuration SQLite
echo ""
info "Configuration de la base de données (SQLite)"
DB_PATH="database/database.sqlite"
read -rp "  Chemin du fichier SQLite [${DB_PATH}] : " db_path
db_path="${db_path:-${DB_PATH}}"

mkdir -p "$(dirname "$db_path")"
touch "$db_path"
ok "Fichier SQLite prêt : ${db_path}."

sed -i "s/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/" .env
# Supprime les directives MySQL inutiles
sed -i "/^DB_HOST=/d" .env
sed -i "/^DB_PORT=/d" .env
sed -i "/^DB_DATABASE=/d" .env
sed -i "/^DB_USERNAME=/d" .env
sed -i "/^DB_PASSWORD=/d" .env
# Ajoute le chemin absolu du fichier SQLite
echo "DB_DATABASE=$(pwd)/${db_path}" >> .env
ok "Paramètres DB SQLite enregistrés dans .env."

# Saisie de l'URL de l'application
echo ""
read -rp "  APP_URL [http://localhost] : " app_url; app_url="${app_url:-http://localhost}"
sed -i "s|^APP_URL=.*|APP_URL=${app_url}|" .env
ok "APP_URL défini à ${app_url}."

# ── Dépendances PHP ───────────────────────────────────────────────────────────

echo ""
info "Installation des dépendances Composer..."
# --no-scripts : évite que @php artisan package:discover échoue si le binaire
# PHP utilisé par Composer diffère de celui de la session (ex. OVH).
php "$COMPOSER_BIN" install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
ok "Dépendances PHP installées."

# ── Clé d'application ─────────────────────────────────────────────────────────

info "Génération de la clé d'application..."
php artisan key:generate --no-interaction
ok "APP_KEY généré."

# ── Découverte des packages Laravel ──────────────────────────────────────────

info "Découverte des packages Laravel..."
php artisan package:discover --ansi
ok "Packages découverts."

# ── Permissions ───────────────────────────────────────────────────────────────

echo ""
info "Réglage des permissions sur storage/ et bootstrap/cache/..."
chmod -R 775 storage bootstrap/cache
ok "Permissions appliquées."

# ── Cache de configuration ────────────────────────────────────────────────────

info "Mise en cache de la configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
ok "Cache généré."

# ── Résumé ────────────────────────────────────────────────────────────────────

echo ""
echo "========================================"
ok "Installation terminée !"
echo ""
echo "  Prochaines étapes :"
echo "  1. Lancez les migrations : php artisan migrate"
echo "  2. (Optionnel) Seeders   : php artisan db:seed"
echo "  3. Démarrez le serveur   : php artisan serve"
echo "========================================"
echo ""
