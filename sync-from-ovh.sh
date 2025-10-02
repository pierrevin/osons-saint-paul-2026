#!/bin/bash

# Script de synchronisation depuis OVH
# Usage: ./sync-from-ovh.sh
# Ce script récupère les modifications d'OVH et les intègre en local

set -e

echo "🔄 Synchronisation depuis OVH"
echo "============================"

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

# Vérifier s'il y a des modifications locales non commitées
if ! git diff-index --quiet HEAD --; then
    log_warn "⚠️  Modifications locales détectées !"
    echo "Voulez-vous :"
    echo "1) Sauvegarder et continuer"
    echo "2) Annuler la synchronisation"
    read -p "Votre choix (1/2): " choice
    
    if [ "$choice" = "2" ]; then
        log_info "Synchronisation annulée"
        exit 0
    fi
    
    # Sauvegarder les modifications locales
    log_info "Sauvegarde des modifications locales..."
    git stash push -m "Sauvegarde avant sync OVH - $(date)"
fi

# Récupérer les modifications d'OVH
log_info "Récupération des modifications d'OVH..."
git fetch origin

# Vérifier s'il y a des différences
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)

if [ "$LOCAL" = "$REMOTE" ]; then
    log_info "✅ Aucune modification à synchroniser"
    exit 0
fi

# Afficher les différences
log_info "Différences détectées :"
git log --oneline HEAD..origin/main

# Fusionner les modifications
log_info "Fusion des modifications..."
git pull origin main

# Restaurer les modifications locales si elles existaient
if git stash list | grep -q "Sauvegarde avant sync OVH"; then
    log_info "Restauration des modifications locales..."
    git stash pop
fi

log_info "✅ Synchronisation terminée !"
echo ""
echo "📋 Prochaines étapes :"
echo "1. Tester les modifications en local"
echo "2. Résoudre les conflits éventuels"
echo "3. Commit et push si tout fonctionne"
