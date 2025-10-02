#!/bin/bash
# sync-local.sh - Script de synchronisation automatique en local

echo "🔄 Synchronisation automatique en local"
echo "======================================"

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

# Étape 1: Récupérer les modifications depuis GitHub
log_info "Étape 1: Récupération des modifications depuis GitHub..."
git fetch origin main

# Vérifier s'il y a des différences
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)

if [ "$LOCAL" = "$REMOTE" ]; then
    log_info "✅ Aucune modification à synchroniser"
    exit 0
fi

# Étape 2: Sauvegarder les modifications locales
log_info "Étape 2: Sauvegarde des modifications locales..."
if ! git diff-index --quiet HEAD --; then
    git stash push -m "Sauvegarde avant sync automatique - $(date)"
    log_info "✅ Modifications locales sauvegardées"
fi

# Étape 3: Fusionner les modifications
log_info "Étape 3: Fusion des modifications..."
git pull origin main

# Étape 4: Restaurer les modifications locales
log_info "Étape 4: Restauration des modifications locales..."
if git stash list | grep -q "Sauvegarde avant sync automatique"; then
    git stash pop
    log_info "✅ Modifications locales restaurées"
fi

# Étape 5: Analyser les modifications de contenu
log_info "Étape 5: Analyse des modifications de contenu..."

# Vérifier les fichiers de contenu modifiés
CONTENT_FILES=(
    "data/site_content.json"
    "data/propositions.json"
    "admin/users.json"
    "admin/logs/security.log"
)

MODIFIED_CONTENT=false
for file in "${CONTENT_FILES[@]}"; do
    if git diff HEAD~1 HEAD --name-only | grep -q "$file"; then
        log_info "📝 Contenu modifié: $file"
        MODIFIED_CONTENT=true
    fi
done

if [ "$MODIFIED_CONTENT" = true ]; then
    log_warn "⚠️  Modifications de contenu détectées !"
    echo ""
    echo "📋 Ces modifications viennent de l'interface admin en production."
    echo "💡 Vous pouvez maintenant :"
    echo "   1. Tester ces modifications en local"
    echo "   2. Les modifier si nécessaire"
    echo "   3. Les commiter pour les sauvegarder"
    echo ""
    echo "🔍 Voir les modifications :"
    echo "   git diff HEAD~1 HEAD"
    echo ""
    echo "💾 Commiter les modifications :"
    echo "   git add ."
    echo "   git commit -m 'Sync: Modifications de contenu depuis production'"
    echo "   git push origin main"
fi

log_info "✅ Synchronisation automatique terminée !"
echo ""
echo "📊 Résumé :"
echo "✅ Code de développement: Synchronisé"
echo "✅ Contenu de production: Récupéré"
echo "✅ Modifications locales: Préservées"
