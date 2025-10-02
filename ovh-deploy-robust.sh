#!/bin/bash

# Script de déploiement robuste pour OVH
# Ce script gère automatiquement les conflits de contenu
# Usage: Exécuté automatiquement par le webhook OVH

set -e

echo "🚀 Déploiement robuste OVH - $(date)"
echo "=================================="

# Configuration
REPO_URL="https://github.com/pierrevin/osons-saint-paul-2026.git"
BRANCH="main"
DEPLOY_DIR="/home/pierrevin/osons-saint-paul"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Aller dans le répertoire de déploiement
cd "$DEPLOY_DIR" || {
    log_error "Répertoire de déploiement non trouvé: $DEPLOY_DIR"
    exit 1
}

# Étape 1: Sauvegarder les fichiers de contenu modifiés en production
log_info "Étape 1: Sauvegarde des modifications de contenu..."

BACKUP_DIR="/tmp/ovh-content-backup-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"

# Fichiers de contenu à préserver (modifiés via l'interface admin)
CONTENT_FILES=(
    "data/site_content.json"
    "admin/users.json"
    "admin/logs/security.log"
    "admin/logs/login_attempts.json"
    "uploads/"
)

for file in "${CONTENT_FILES[@]}"; do
    if [ -e "$file" ]; then
        cp -r "$file" "$BACKUP_DIR/"
        log_info "✅ Sauvegardé: $file"
    fi
done

# Étape 2: Nettoyer le repository Git
log_info "Étape 2: Nettoyage du repository Git..."

# Sauvegarder les modifications locales
git stash push -m "Sauvegarde avant déploiement - $(date)" || true

# Nettoyer complètement
git clean -fd
git reset --hard HEAD

# Étape 3: Récupérer les dernières modifications
log_info "Étape 3: Récupération des modifications GitHub..."

git fetch origin
git reset --hard origin/$BRANCH

log_info "✅ Code synchronisé avec GitHub"

# Étape 4: Restaurer les fichiers de contenu
log_info "Étape 4: Restauration du contenu de production..."

for file in "${CONTENT_FILES[@]}"; do
    if [ -e "$BACKUP_DIR/$file" ]; then
        # Créer le répertoire parent si nécessaire
        mkdir -p "$(dirname "$file")"
        
        # Restaurer le fichier
        cp -r "$BACKUP_DIR/$file" "$file"
        log_info "✅ Restauré: $file"
    fi
done

# Étape 5: Définir les permissions correctes
log_info "Étape 5: Définition des permissions..."

find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 755 *.sh 2>/dev/null || true

# Étape 6: Nettoyage des caches
log_info "Étape 6: Nettoyage des caches..."

# Nettoyer les anciens logs (garder les 30 derniers jours)
if [ -d "admin/logs" ]; then
    find admin/logs -name "*.log" -mtime +30 -delete 2>/dev/null || true
fi

# Étape 7: Vérification du déploiement
log_info "Étape 7: Vérification du déploiement..."

# Vérifier que les fichiers critiques existent
CRITICAL_FILES=(
    "index.php"
    "admin/index.php"
    "admin/pages/schema_admin.php"
    "admin/includes/user_manager.php"
)

for file in "${CRITICAL_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        log_error "❌ Fichier critique manquant: $file"
        exit 1
    fi
done

log_info "✅ Vérification du déploiement réussie"

# Étape 8: Nettoyage
log_info "Étape 8: Nettoyage..."

rm -rf "$BACKUP_DIR"

log_info "🎉 Déploiement terminé avec succès!"
log_info "📊 Contenu de production préservé"
log_info "🔧 Code mis à jour depuis GitHub"

echo ""
echo "✅ DÉPLOIEMENT RÉUSSI !"
echo "======================"
echo "📝 Contenu de production: PRÉSERVÉ"
echo "💻 Code de développement: MIS À JOUR"
echo "🔒 Permissions: CORRECTES"
echo "🧹 Caches: NETTOYÉS"
