#!/bin/bash

# Script de déploiement sécurisé pour OVH
# Usage: ./deploy.sh

set -e  # Arrêter en cas d'erreur

echo "🚀 Début du déploiement..."

# Configuration
REPO_URL="https://github.com/pierrevin/osons-saint-paul-2026.git"
BRANCH="main"
DEPLOY_DIR="/home/pierrevin/osons-saint-paul"

# Couleurs pour les logs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Fonction de sauvegarde
backup_current() {
    log_info "Création d'une sauvegarde..."
    BACKUP_DIR="/home/pierrevin/backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"
    
    if [ -d "$DEPLOY_DIR" ]; then
        cp -r "$DEPLOY_DIR" "$BACKUP_DIR/"
        log_info "Sauvegarde créée dans $BACKUP_DIR"
    fi
}

# Fonction de déploiement
deploy() {
    log_info "Début du déploiement..."
    
    # Aller dans le répertoire de déploiement
    cd "$DEPLOY_DIR" || {
        log_error "Répertoire de déploiement non trouvé: $DEPLOY_DIR"
        exit 1
    }
    
    # Sauvegarder les fichiers de configuration locaux
    log_info "Sauvegarde des fichiers de configuration..."
    if [ -f ".env" ]; then
        cp .env /tmp/.env.backup
    fi
    if [ -f "admin/.htaccess" ]; then
        cp admin/.htaccess /tmp/admin.htaccess.backup
    fi
    
    # Nettoyer le répertoire Git
    log_info "Nettoyage du répertoire Git..."
    git clean -fd
    git reset --hard HEAD
    
    # Récupérer les dernières modifications
    log_info "Récupération des dernières modifications..."
    git fetch origin
    
    # Forcer la synchronisation avec le repository distant
    log_info "Synchronisation avec le repository distant..."
    git reset --hard origin/$BRANCH
    
    # Restaurer les fichiers de configuration
    log_info "Restauration des fichiers de configuration..."
    if [ -f "/tmp/.env.backup" ]; then
        cp /tmp/.env.backup .env
        rm /tmp/.env.backup
    fi
    if [ -f "/tmp/admin.htaccess.backup" ]; then
        cp /tmp/admin.htaccess.backup admin/.htaccess
        rm /tmp/admin.htaccess.backup
    fi
    
    # Définir les permissions correctes
    log_info "Définition des permissions..."
    find . -type d -exec chmod 755 {} \;
    find . -type f -exec chmod 644 {} \;
    chmod 755 deploy.sh
    
    # Nettoyer les caches
    log_info "Nettoyage des caches..."
    if [ -d "admin/logs" ]; then
        find admin/logs -name "*.log" -mtime +30 -delete
    fi
    
    log_info "✅ Déploiement terminé avec succès!"
}

# Fonction de rollback
rollback() {
    log_warn "Rollback en cours..."
    LATEST_BACKUP=$(ls -t /home/pierrevin/backups/ | head -n1)
    if [ -n "$LATEST_BACKUP" ]; then
        rm -rf "$DEPLOY_DIR"
        cp -r "/home/pierrevin/backups/$LATEST_BACKUP/osons-saint-paul" "$DEPLOY_DIR"
        log_info "Rollback terminé vers $LATEST_BACKUP"
    else
        log_error "Aucune sauvegarde trouvée pour le rollback"
        exit 1
    fi
}

# Fonction de vérification
verify_deployment() {
    log_info "Vérification du déploiement..."
    
    # Vérifier que les fichiers critiques existent
    CRITICAL_FILES=(
        "index.php"
        "admin/index.php"
        "admin/pages/schema_admin.php"
        "admin/includes/user_manager.php"
        "admin/users.json"
    )
    
    for file in "${CRITICAL_FILES[@]}"; do
        if [ ! -f "$DEPLOY_DIR/$file" ]; then
            log_error "Fichier critique manquant: $file"
            return 1
        fi
    done
    
    # Vérifier les permissions
    if [ ! -r "$DEPLOY_DIR/admin/users.json" ]; then
        log_error "Permissions incorrectes sur admin/users.json"
        return 1
    fi
    
    log_info "✅ Vérification du déploiement réussie"
    return 0
}

# Fonction principale
main() {
    case "${1:-deploy}" in
        "deploy")
            backup_current
            deploy
            if verify_deployment; then
                log_info "🎉 Déploiement réussi!"
            else
                log_error "❌ Échec de la vérification, rollback..."
                rollback
                exit 1
            fi
            ;;
        "rollback")
            rollback
            ;;
        "verify")
            verify_deployment
            ;;
        *)
            echo "Usage: $0 {deploy|rollback|verify}"
            exit 1
            ;;
    esac
}

# Exécution
main "$@"
