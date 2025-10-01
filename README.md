# 🍃 Osons Saint-Paul 2026

Site web de la liste citoyenne pour les élections municipales 2026 à Saint-Paul.

## 🚀 Mise en route

### Prérequis
- PHP 8.0 ou supérieur
- Serveur web (Apache/Nginx) ou PHP built-in server
- Extensions PHP : JSON, GD, fileinfo

### Installation

1. **Cloner le repository**
   ```bash
   git clone [votre-repo]
   cd "Osons - Saint Paul Site"
   ```

2. **Configuration**
   - Copier `admin/config.php.example` vers `admin/config.php` (si nécessaire)
   - Modifier les paramètres selon votre environnement
   - **Important :** Changer le mot de passe admin !

3. **Créer les dossiers requis**
   ```bash
   mkdir -p data/backups/propositions
   mkdir -p uploads/images
   mkdir -p logs
   chmod 755 data/ uploads/ logs/
   chmod 644 data/*.json
   ```

4. **Lancer le serveur de développement**
   ```bash
   php -S localhost:8000
   ```

5. **Accéder au site**
   - Site public : http://localhost:8000/
   - Interface admin : http://localhost:8000/admin/

## 📁 Structure du projet

```
.
├── admin/                      # Interface d'administration
│   ├── config.php             # Configuration principale
│   ├── index.php              # Dashboard admin
│   ├── pages/                 # Pages admin (gestion contenus)
│   └── assets/                # CSS/JS admin
│
├── forms/                      # Formulaires publics
│   ├── proposition-citoyenne.php    # Formulaire propositions
│   ├── subscribe-newsletter.php     # Inscription newsletter
│   ├── process-form.php            # Traitement formulaires
│   └── admin/                      # Gestion des propositions
│
├── data/                       # Données JSON
│   ├── site_content.json      # Contenu du site
│   ├── propositions.json      # Propositions citoyennes
│   └── backups/               # Sauvegardes automatiques
│
├── uploads/                    # Images uploadées
│   └── images/                # Images optimisées
│
├── logs/                       # Fichiers de logs
│   ├── email_logs.log
│   └── newsletter_logs.log
│
├── tools/                      # Utilitaires et tests
│   ├── test_json_integrity.php
│   ├── backup_json.php
│   └── test-*.php
│
├── index.php                   # Page principale (dynamique)
├── styles.css                  # Styles principaux
├── script.js                   # JavaScript
└── critical.css                # CSS critique (inline)
```

## ⚙️ Fonctionnalités

### 🌐 Site public
- ✅ Page d'accueil dynamique avec hero personnalisable
- ✅ Programme avec cartes filtrables (4 piliers)
- ✅ Équipe avec photos et descriptions
- ✅ Agenda des rendez-vous/événements
- ✅ Charte des engagements
- ✅ Formulaire de propositions citoyennes
- ✅ Inscription newsletter
- ✅ Design responsive et accessible

### 🔧 Interface d'administration
- ✅ Tableau de bord centralisé
- ✅ CRUD complet pour toutes les sections :
  - Hero (titre, boutons, image de fond)
  - Programme (propositions avec piliers)
  - Équipe (membres avec photos)
  - Rendez-vous (événements futurs)
  - Charte (principes)
  - Citations (4 citations avec images)
- ✅ Upload d'images avec :
  - Redimensionnement automatique
  - Conversion WebP
  - Gestion des formats (JPEG, PNG, WebP)
- ✅ Gestion des propositions citoyennes
- ✅ Système de backup automatique
- ✅ Logs d'activité

### 🛡️ Sécurité
- ✅ Protection CSRF
- ✅ Validation et sanitisation des données
- ✅ Authentification admin
- ✅ Limite de propositions (anti-spam)
- ✅ Sauvegardes automatiques des données
- ✅ Logs des erreurs et actions

## 📖 Documentation

- **[MAINTENANCE.md](MAINTENANCE.md)** - Guide de maintenance et dépannage
- **[DEPLOYMENT_PLAN.md](DEPLOYMENT_PLAN.md)** - Plan de déploiement et roadmap
- **[SPECIFICATIONS_SECTIONS.md](SPECIFICATIONS_SECTIONS.md)** - Spécifications détaillées
- **[forms/README.md](forms/README.md)** - Documentation des formulaires

## 🔧 Configuration

### Authentification admin
Par défaut : `admin` / `admin2026`

⚠️ **IMPORTANT** : Changez le mot de passe dans `admin/config.php` :
```php
define('ADMIN_PASSWORD', 'votre_mot_de_passe_securise');
```

### Configuration email
Modifiez dans `forms/email-config.php` :
```php
define('SMTP_HOST', 'votre-smtp.fr');
define('SMTP_USER', 'votre@email.fr');
define('SMTP_PASSWORD', 'votre_mot_de_passe');
```

### Limites et sécurité
Dans `forms/config.php` :
```php
define('MAX_PROPOSITION_LENGTH', 500);  // Longueur max proposition
define('RATE_LIMIT', 5);                // Propositions max par jour
```

## 🚀 Déploiement

### Sur serveur mutualisé (OVH, etc.)

1. **Upload des fichiers**
   ```bash
   # Via FTP/SFTP, uploadez tous les fichiers
   ```

2. **Configuration Apache**
   Le fichier `.htaccess` est déjà configuré pour :
   - Redirections
   - Sécurité des dossiers sensibles
   - Optimisation cache

3. **Permissions**
   ```bash
   chmod 755 data/ uploads/ logs/
   chmod 644 data/*.json
   chmod 600 admin/config.php
   ```

4. **Configuration PHP**
   Vérifiez que votre hébergeur supporte :
   - PHP 8.0+
   - Extensions : JSON, GD, fileinfo
   - `upload_max_filesize` >= 10M

5. **Sécurité**
   - Changez le mot de passe admin
   - Configurez HTTPS (certificat SSL)
   - Vérifiez les permissions des fichiers

## 🧪 Tests

### Tester le système
```bash
# Test d'intégrité JSON
php tools/test_json_integrity.php

# Test du système de formulaires
php tools/test-system.php

# Test d'envoi d'emails
php tools/test-email.php
```

### Créer une sauvegarde manuelle
```bash
php tools/backup_json.php
```

## 🔄 Maintenance

### Sauvegardes
- **Automatiques** : À chaque modification via admin
- **Localisation** : `data/backups/`
- **Rétention** : 20 dernières sauvegardes conservées

### Restauration
```bash
# Lister les sauvegardes
ls -la data/backups/

# Restaurer une sauvegarde
cp data/backups/site_content.json.YYYY-MM-DD-HH-MM-SS.json data/site_content.json
```

### Logs
```bash
# Consulter les logs
tail -f logs/email_logs.log
tail -f logs/newsletter_logs.log
```

### Nettoyage
```bash
# Nettoyer les anciens backups (garder les 20 derniers)
cd data/backups && ls -t site_content.json.*.json | tail -n +21 | xargs rm -f
```

## 🆘 Dépannage

### Le site ne s'affiche pas
1. Vérifiez que PHP est installé : `php -v`
2. Vérifiez les permissions des dossiers
3. Consultez les logs d'erreur du serveur

### L'admin ne fonctionne pas
1. Vérifiez le mot de passe dans `admin/config.php`
2. Vérifiez que `data/site_content.json` existe et est valide
3. Testez l'intégrité : `php tools/test_json_integrity.php`

### Les images ne s'uploadent pas
1. Vérifiez les permissions du dossier `uploads/`
2. Vérifiez `upload_max_filesize` dans `php.ini`
3. Vérifiez que l'extension GD est activée

### Les emails ne partent pas
1. Vérifiez la configuration SMTP dans `forms/email-config.php`
2. Testez l'envoi : `php tools/test-email.php`
3. Consultez `logs/email_logs.log`

## 🛠️ Technologies utilisées

- **Frontend** : HTML5, CSS3, JavaScript (Vanilla)
- **Backend** : PHP 8+
- **Données** : JSON (pas de base de données)
- **Images** : GD Library (redimensionnement + WebP)
- **Polices** : Google Fonts (Lato, Caveat)
- **Icônes** : Font Awesome 6

## 📊 Statut du projet

### ✅ Complété
- Architecture modulaire (admin/public/data)
- Interface admin avec CRUD complet
- Site dynamique basé sur JSON
- Upload et conversion WebP
- Formulaires (propositions + newsletter)
- Système de backup automatique
- Protection CSRF et validation

### 🔄 En cours
- Optimisations performance (cache, lazy loading)
- Authentification 2FA
- Tests automatisés

### ⏳ À venir
- Optimisation mobile avancée
- Documentation utilisateur finale
- Tests de sécurité complets

## 📝 Licence

Projet développé pour la liste citoyenne Osons Saint-Paul 2026.

## 🤝 Contribution

Pour toute question ou suggestion, contactez l'équipe technique.

---

**Développé avec ❤️ pour Osons Saint-Paul 2026**

