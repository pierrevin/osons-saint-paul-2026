# 🚀 Workflow de Développement - Osons Saint Paul

## ⚠️ RÈGLE D'OR : Ne jamais modifier directement en production !

### 📋 Workflow recommandé :

#### 1. **Développement local uniquement**
```bash
# Toujours développer en local
php -S localhost:8000

# Tester toutes les modifications en local
# Valider que tout fonctionne
```

#### 2. **Commit et push**
```bash
git add .
git commit -m "Description des modifications"
git push origin main
```

#### 3. **Déploiement automatique**
- OVH déploie automatiquement via webhook
- Aucune modification manuelle sur le serveur

### 🔄 Si vous devez modifier en production (URGENCE) :

#### Option A : Synchronisation immédiate
```bash
# 1. Récupérer les modifications d'OVH
git fetch origin
git pull origin main

# 2. Appliquer vos modifications locales
# 3. Tester en local
# 4. Push vers GitHub
git push origin main
```

#### Option B : Script de synchronisation
```bash
# Utiliser le script de sync (à recréer si nécessaire)
./sync-ovh.sh
```

### 🛡️ Prévention des conflits :

1. **Toujours tester en local avant de push**
2. **Ne jamais modifier directement sur OVH**
3. **Utiliser l'interface admin en local pour les tests**
4. **Synchroniser régulièrement avec OVH**

### 📊 Monitoring :

- Vérifier les logs de déploiement OVH
- Surveiller les conflits Git
- Tester l'interface admin après chaque déploiement
