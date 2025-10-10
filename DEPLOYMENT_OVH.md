# 🚀 Guide de Déploiement OVH - Osons Saint-Paul 2026

## 📋 Checklist de Déploiement

### ✅ Fichiers à uploader sur le serveur :

#### 🏠 **Fichiers racine :**
- `index.php` (optimisé SEO + Open Graph)
- `styles.css` (classe .card-title-text ajoutée)
- `script.js`
- `maintenance.php`
- `equipe-formulaire.php`
- `proposez.php`
- `mentions-legales.php` (SEO optimisé)
- `gestion-cookies.php` (SEO optimisé)
- `merci-inscription.php` (SEO optimisé)
- `load_gallery_images.php`
- `critical.css`

#### 🎨 **Favicons et icônes :**
- `favicon.ico`
- `favicon-16x16.png`
- `favicon-32x32.png`
- `apple-touch-icon.png`
- `android-chrome-192x192.png`
- `android-chrome-512x512.png`

#### 📄 **Configuration :**
- `.htaccess` (optimisations serveur)
- `robots.txt` (SEO)
- `sitemap.xml` (SEO)
- `manifest.json` (PWA)

#### 📁 **Dossiers essentiels :**
- `admin/` (interface d'administration)
- `forms/` (formulaires)
- `uploads/` (images + og-image.png)
- `data-osons/` (données du site)
- `assets/` (ressources CSS/JS)
- `vendor/` (dépendances PHP)

#### 📁 **Logs (optionnel) :**
- `admin/logs/` (logs de sécurité)

---

### ❌ **Fichiers à NE PAS uploader :**

#### 🚫 **Sécurité :**
- `.git/` (repository Git)
- `credentials/` (clés API)

#### 📚 **Développement :**
- `docs/` (documentation technique)
- `tools/` (scripts utilitaires)
- `archive/` (archives)
- `backups/` (sauvegardes locales)
- `data/` (doublon de développement)

#### 🧪 **Fichiers de test :**
- `test-cookies.html`
- `cookies.txt`

#### 🔧 **Configuration dev :**
- `composer.json`
- `composer.lock`
- `.gitignore`
- `.DS_Store`
- Tous les fichiers `*.md`

---

## 🔧 Configuration Post-Déploiement

### 1. **Permissions des dossiers :**
```bash
chmod 755 uploads/
chmod 755 data-osons/
chmod 755 admin/logs/
chmod 644 .htaccess
```

### 2. **Protection des logs :**
Ajouter dans `.htaccess` :
```apache
# Protection des logs
<Files "*.log">
    Order Allow,Deny
    Deny from all
</Files>
<Files "*.json">
    <RequireAll>
        Require all denied
        Require local
    </RequireAll>
</Files>
```

### 3. **Vérifications :**
- [ ] Site accessible en HTTPS
- [ ] Favicons s'affichent correctement
- [ ] Admin fonctionne
- [ ] Formulaires fonctionnent
- [ ] Images s'affichent
- [ ] Sitemap accessible : `/sitemap.xml`
- [ ] Robots.txt accessible : `/robots.txt`

---

## 🤖 Configuration Webhook OVH

### URL de webhook suggérée :
```
https://votre-domaine.com/webhook/deploy.php
```

### Script de déploiement automatique :
```php
<?php
// webhook/deploy.php
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if ($data['ref'] === 'refs/heads/main') {
    // Exécuter git pull
    exec('cd /path/to/site && git pull origin main');
    
    // Log du déploiement
    file_put_contents('deployment.log', date('Y-m-d H:i:s') . " - Deploy successful\n", FILE_APPEND);
}
?>
```

---

## 📊 Version Actuelle

**Version :** 2.0.0  
**Commit :** b7c96275  
**Date :** $(date)  

### Nouvelles fonctionnalités :
- ✅ Tracking UTM complet
- ✅ Optimisations SEO avancées
- ✅ Favicons multi-tailles
- ✅ Open Graph optimisé
- ✅ PWA manifest
- ✅ Architecture images unifiée

**Site prêt pour la production !** 🎉
