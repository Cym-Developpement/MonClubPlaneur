#!/usr/bin/env bash

set -e

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
echo "     Mise à jour MonClubPlaneur"
echo "========================================"
echo ""

# ── Stash des modifications locales ──────────────────────────────────────────

STASH_OUTPUT=$(git stash 2>&1)
if echo "$STASH_OUTPUT" | grep -q "No local changes"; then
    STASHED=false
    info "Aucune modification locale à mettre de côté."
else
    STASHED=true
    ok "Modifications locales mises de côté (git stash)."
fi

# ── Récupération des changements ──────────────────────────────────────────────

info "Récupération des mises à jour (git pull)..."
git pull origin master
ok "Code mis à jour."

# ── Restauration du stash ─────────────────────────────────────────────────────

if [ "$STASHED" = true ]; then
    info "Restauration des modifications locales (git stash pop)..."
    git stash pop || warn "Conflit lors du stash pop — vérifiez manuellement."
fi

# ── Dépendances PHP ───────────────────────────────────────────────────────────

info "Mise à jour des dépendances Composer..."
php artisan down --retry=60
composer install --no-dev --no-interaction --optimize-autoloader
ok "Dépendances PHP installées."

# ── Cache Laravel ─────────────────────────────────────────────────────────────

info "Rechargement des caches Laravel..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
ok "Caches vidés."

# ── Migrations ────────────────────────────────────────────────────────────────

info "Exécution des migrations..."
php artisan migrate --force
ok "Migrations appliquées."

# ── Remise en ligne ───────────────────────────────────────────────────────────

php artisan up
ok "Application remise en ligne."

echo ""
echo "========================================"
ok "Mise à jour terminée !"
echo "========================================"
echo ""
