#!/bin/bash

# Configuration des alias Git pour simplifier le workflow
# Usage: ./setup-git-aliases.sh

echo "🔧 Configuration des alias Git..."

# Alias pour la synchronisation
git config alias.sync-ovh '!./sync-from-ovh.sh'
git config alias.deploy '!git add . && git commit -m "Deploy: $(date)" && git push origin main'
git config alias.status-ovh '!git fetch origin && git log --oneline HEAD..origin/main'

echo "✅ Alias Git configurés !"
echo ""
echo "📋 Nouvelles commandes disponibles :"
echo "  git sync-ovh     - Synchroniser depuis OVH"
echo "  git deploy       - Déployer rapidement"
echo "  git status-ovh   - Voir les différences avec OVH"
echo ""
echo "💡 Utilisation recommandée :"
echo "  1. git sync-ovh    (avant de commencer à travailler)"
echo "  2. [vos modifications]"
echo "  3. git deploy      (pour déployer)"
