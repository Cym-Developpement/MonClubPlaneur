#!/usr/bin/env bash

set -e

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

# Saisie interactive des paramètres DB
echo ""
info "Configuration de la base de données"
read -rp "  DB_HOST     [127.0.0.1] : " db_host;    db_host="${db_host:-127.0.0.1}"
read -rp "  DB_PORT     [3306]      : " db_port;    db_port="${db_port:-3306}"
read -rp "  DB_DATABASE [monclubplaneur] : " db_name; db_name="${db_name:-monclubplaneur}"
read -rp "  DB_USERNAME [root]      : " db_user;    db_user="${db_user:-root}"
read -srp " DB_PASSWORD []         : " db_pass;    echo ""

sed -i "s/^DB_HOST=.*/DB_HOST=${db_host}/" .env
sed -i "s/^DB_PORT=.*/DB_PORT=${db_port}/" .env
sed -i "s/^DB_DATABASE=.*/DB_DATABASE=${db_name}/" .env
sed -i "s/^DB_USERNAME=.*/DB_USERNAME=${db_user}/" .env
sed -i "s/^DB_PASSWORD=.*/DB_PASSWORD=${db_pass}/" .env
ok "Paramètres DB enregistrés dans .env."

# Saisie de l'URL de l'application
echo ""
read -rp "  APP_URL [http://localhost] : " app_url; app_url="${app_url:-http://localhost}"
sed -i "s|^APP_URL=.*|APP_URL=${app_url}|" .env
ok "APP_URL défini à ${app_url}."

# ── Dépendances PHP ───────────────────────────────────────────────────────────

echo ""
info "Installation des dépendances Composer..."
php "$COMPOSER_BIN" install --no-interaction --prefer-dist --optimize-autoloader
ok "Dépendances PHP installées."

# ── Clé d'application ─────────────────────────────────────────────────────────

info "Génération de la clé d'application..."
php artisan key:generate --no-interaction
ok "APP_KEY généré."

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
echo "  1. Créez la base de données '${db_name}' sur ${db_host}:${db_port}"
echo "  2. Lancez les migrations : php artisan migrate"
echo "  3. (Optionnel) Seeders   : php artisan db:seed"
echo "  4. Démarrez le serveur   : php artisan serve"
echo "========================================"
echo ""
