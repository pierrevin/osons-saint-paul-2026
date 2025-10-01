# 🔧 Guide de Maintenance - Site Osons Saint-Paul

## 🚨 Problème résolu : Image hero et header disparus

### Cause du problème
Le fichier `data/site_content.json` a été corrompu (taille réduite à 107 octets au lieu de ~18KB).

### Solution appliquée
```bash
# Restauration depuis la sauvegarde la plus récente
cp data/backups/site_content.json.2025-09-27-07-47-22.json data/site_content.json
```

### Vérification du bon fonctionnement
✅ Image hero : `uploads/hero-bg_1758956376_68d78b58ceab4.webp` (471KB)  
✅ Logo header : `Ofeuille.png` (1.4MB)  
✅ Toutes les sections dynamiques opérationnelles  
✅ Interface d'administration fonctionnelle  

## 🛡️ Prévention des problèmes futurs

### Sauvegardes automatiques
Le système crée automatiquement des sauvegardes dans `data/backups/` :
- Format : `site_content.json.YYYY-MM-DD-HH-MM-SS.json`
- Conservation : 20 dernières sauvegardes
- Fréquence : À chaque modification via l'admin

### Diagnostic rapide
Pour vérifier l'état du site :
```bash
# Vérifier la taille du fichier JSON
ls -la data/site_content.json

# Doit faire environ 18KB, si moins de 1KB = problème
```

### En cas de problème
1. **Identifier la sauvegarde la plus récente :**
   ```bash
   ls -la data/backups/ | tail -1
   ```

2. **Restaurer :**
   ```bash
   cp data/backups/site_content.json.YYYY-MM-DD-HH-MM-SS.json data/site_content.json
   ```

3. **Vérifier les permissions :**
   ```bash
   chmod 644 data/site_content.json
   chmod 755 data/uploads/
   ```

## 📊 État actuel du site

### Contenu dynamique
- ✅ Hero : Titre, boutons, image de fond
- ✅ Programme : 6 propositions avec CRUD complet
- ✅ Équipe : 19 membres avec CRUD complet
- ✅ Rendez-vous : 4 événements avec CRUD complet
- ✅ Charte : 10 principes avec CRUD complet
- ✅ Citations : 4 citations avec images
- ✅ Idées : Formulaire de contact
- ✅ Médiathèque : Lien vers drive

### Images critiques
- ✅ `uploads/hero-bg_1758956376_68d78b58ceab4.webp` (471KB)
- ✅ `Ofeuille.png` (1.4MB)
- ✅ `uploads/citation1-bg_1758957683_68d79073e32dc.webp` (555KB)
- ✅ `uploads/citation2-bg_1758958519_68d793b7c8e08.webp` (471KB)

### URLs de production
- 🌐 Site public : `http://localhost:8000/`
- 🔧 Administration : `http://localhost:8000/admin/`

## ⚡ Commandes utiles

```bash
# Relancer le serveur
php -S localhost:8000 -t .

# Vérifier l'état du site
curl -s http://localhost:8000/ | head -10

# Tester l'admin
curl -s http://localhost:8000/admin/ | head -10

# Créer une sauvegarde manuelle
cp data/site_content.json data/backups/site_content.json.$(date +%Y-%m-%d-%H-%M-%S).json
```

---
**Dernière mise à jour :** 27 septembre 2025  
**Statut :** ✅ Opérationnel
