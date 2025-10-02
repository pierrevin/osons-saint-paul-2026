#!/bin/bash

# Script de configuration du déploiement OVH
# À exécuter sur le serveur OVH

echo "🔧 Configuration du déploiement OVH robuste"
echo "=========================================="

# Configuration
DEPLOY_DIR="/home/pierrevin/osons-saint-paul"
SCRIPT_NAME="ovh-deploy-robust.sh"

# Aller dans le répertoire de déploiement
cd "$DEPLOY_DIR" || {
    echo "❌ Répertoire de déploiement non trouvé: $DEPLOY_DIR"
    exit 1
}

# Rendre le script exécutable
chmod +x "$SCRIPT_NAME"

# Créer un lien symbolique pour le webhook
ln -sf "$DEPLOY_DIR/$SCRIPT_NAME" "/home/pierrevin/deploy.sh"

echo "✅ Script de déploiement configuré"
echo "✅ Lien symbolique créé: /home/pierrevin/deploy.sh"
echo ""
echo "📋 Configuration du webhook OVH :"
echo "URL: https://osons-saint-paul.fr/webhook-deploy.php"
echo "Script: /home/pierrevin/deploy.sh"
echo ""
echo "🎉 Configuration terminée !"
