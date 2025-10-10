# 📚 Documentation - Site Osons Saint-Paul 2026

## 🗂️ Architecture des données

### Système de stockage persistant (Octobre 2025)

Le site utilise un système de **données persistantes** pour éviter toute perte lors des déploiements Git ou uploads FileZilla.

#### Structure des répertoires

```
osons-saint-paul/
├── data/                          ⚠️ DÉPRÉCIÉ - Ne plus utiliser
│   ├── load_content.php          ✅ Fichier PHP fonctionnel (garder)
│   ├── site_content.json         ❌ OBSOLÈTE (utiliser data-osons/)
│   ├── propositions.json         ❌ OBSOLÈTE (utiliser data-osons/)
│   └── admin_log.json            ❌ OBSOLÈTE (utiliser data-osons/)
│
├── data-osons/                    ✅ SYSTÈME ACTIF - Données persistantes
│   ├── site_content.json         ✅ Toutes les données du site
│   ├── propositions.json         ✅ Propositions citoyennes
│   ├── admin_log.json            ✅ Logs de sécurité
│   └── backups/                  ✅ Sauvegardes automatiques
│       ├── backup_history.json
│       └── [archives quotidiennes]
│
├── admin/                         🔐 Interface d'administration
├── forms/                         📝 Formulaires publics
├── uploads/                       📁 Fichiers uploadés
└── tools/                         🛠️ Scripts utilitaires
```

---

## 🔧 Configuration

### Fichier `admin/config.php`

Le système détecte automatiquement le répertoire de données dans l'ordre suivant :

1. **Variable d'environnement `DATA_DIR`** (si définie sur l'hébergeur)
2. **`/home/USER/data-osons`** (hors www, recommandé pour la sécurité)
3. **`/home/USER/www/data-osons`** (dans le projet)
4. **Fallback : `data/`** (ancien système, déprécié)

```php
// Exemple de détection automatique
$envDataDir = getenv('DATA_DIR');
if ($envDataDir && is_dir($envDataDir)) {
    define('DATA_PATH', rtrim($envDataDir, '/'));
} elseif (is_dir(dirname(ROOT_PATH) . '/data-osons')) {
    define('DATA_PATH', dirname(ROOT_PATH) . '/data-osons');
} elseif (is_dir(ROOT_PATH . '/data-osons')) {
    define('DATA_PATH', ROOT_PATH . '/data-osons');
} else {
    define('DATA_PATH', ROOT_PATH . '/data');
}
```

---

## 📊 Fichiers de données

### `data-osons/site_content.json`

Contient **toutes les données éditables du site** :

```json
{
  "hero": { ... },
  "programme": {
    "proposals": [ ... ]  // Propositions de l'équipe (ajoutées via admin)
  },
  "citations": [ ... ],
  "equipe": {
    "members": [ ... ]
  },
  "rendez_vous": {
    "events": [ ... ]
  },
  "charte": { ... },
  "contact": { ... }
}
```

### `data-osons/propositions.json`

Contient **uniquement les propositions citoyennes** soumises via le formulaire public `/proposez` :

```json
{
  "propositions": [
    {
      "id": "prop_...",
      "date": "2025-10-10 12:00:00",
      "status": "pending|approved|rejected|deleted",
      "data": {
        "nom": "...",
        "email": "...",
        "titre": "...",
        "description": "...",
        ...
      },
      "rejection_reason": "..." // Si rejetée
    }
  ],
  "statistics": { ... }
}
```

### `data-osons/admin_log.json`

Journal de toutes les actions administratives :

```json
[
  {
    "timestamp": "2025-10-10 12:00:00",
    "user": "admin",
    "action": "add_team_member",
    "details": "Ajout: John Doe",
    "ip": "xxx.xxx.xxx.xxx"
  }
]
```

---

## 🔄 Sauvegardes automatiques

### Script `tools/backup_daily.php`

- **Fréquence** : Quotidienne (via cron)
- **Rétention** : 3 jours d'historique
- **Fichiers sauvegardés** :
  - `site_content.json`
  - `propositions.json`
  - `admin_log.json`

#### Configuration du cron (sur OVH)

```bash
# Exécution quotidienne à 3h du matin
0 3 * * * /usr/bin/php /home/USER/www/tools/backup_daily.php --cron
```

#### Exécution manuelle

```bash
php tools/backup_daily.php --run-now
```

Ou via HTTP (avec token de sécurité) :
```
https://osons-saint-paul.fr/tools/backup_daily.php?token=BACKUP_TOKEN
```

---

## 🔐 Sécurité

### Protection Git

Le fichier `.gitignore` exclut les données de production :

```gitignore
# Contenus runtime (ne pas déployer via Git)
data/site_content.json
data/propositions.json
data/admin_log.json
data/backups/

# data-osons/ est également ignoré pour éviter tout écrasement
```

### Google reCAPTCHA v3

- **Site Key** : `6LeOrNorAAAAAGfkiHS2IqTbd5QbQHvinxR_4oek`
- **Secret Key** : `6LeOrNorAAAAAAyrKUig543vV-h1OJlb9xefHYhA`
- **Actions** : 
  - `submit_proposal` (formulaire propositions)
  - `submit_contact` (formulaire contact)
  - `submit_newsletter` (formulaire newsletter)

### RGPD

- **Responsable des données** : Pierre Vincenot, Saint-Paul-Sur-Save, 31530, France
- **Contact** : `bonjour@osons-saint-paul.fr`
- **Hébergement** : OVH, France (conforme RGPD, ISO 27001)
- **Politique de confidentialité** : `/forms/politique-confidentialite.php`

---

## 📧 Emails

### Configuration (`forms/email-config.php`)

```php
define('ADMIN_EMAIL', 'bonjour@osons-saint-paul.fr');
define('FROM_EMAIL', 'bonjour@osons-saint-paul.fr');
define('FROM_NAME', 'Osons Saint-Paul 2026');
```

### Service d'envoi (`forms/email-service.php`)

Le système utilise **Brevo API** (anciennement Sendinblue) avec fallback sur `mail()` PHP natif.

#### Types d'emails

1. **Nouvelle proposition citoyenne**
   - Notification admin
   - Confirmation au citoyen

2. **Proposition acceptée**
   - Email au citoyen avec félicitations

3. **Proposition rejetée**
   - Email au citoyen avec raison du refus

4. **Formulaire de contact**
   - Notification admin
   - Confirmation à l'utilisateur

---

## 🚀 Déploiement

### Méthode 1 : Git (Recommandée)

```bash
# Depuis le dépôt local
git add .
git commit -m "Description des modifications"
git push origin main

# Sur le serveur OVH (si webhook configuré)
# Le déploiement se fait automatiquement
```

**⚠️ Important** : Les fichiers dans `data-osons/` ne sont **jamais écrasés** par Git.

### Méthode 2 : FileZilla (Upload manuel)

1. Se connecter au serveur OVH
2. Uploader les fichiers modifiés
3. **NE JAMAIS UPLOADER** le dossier `data-osons/` de local vers serveur
   - Cela écraserait les données de production !

---

## 🧪 Tests

### Script de test email

```bash
php tools/test-email-system.php
```

Teste :
- Configuration email
- Envoi de test
- Tous les templates
- Logs récents

### Script de test des formulaires

```bash
php tools/test-contact-forms.php
```

Teste :
- Formulaire page d'accueil
- Formulaire page confidentialité

---

## 📝 Workflow de développement

### En local

1. Modifier les fichiers sources
2. Tester en local
3. Commit Git

### En production

1. Git push (déploiement automatique si webhook configuré)
   OU
2. Upload manuel via FileZilla

**⚠️ Règle d'or** :
- **Jamais uploader `data-osons/`** de local vers serveur
- **Toujours vérifier** que les modifications sont dans Git avant upload

---

## 🆘 Dépannage

### Problème : Les données ne sont pas sauvegardées

1. Vérifier dans l'admin (badge en bas à droite) :
   - ✅ "Données persistantes (data-osons)" → OK
   - ⚠️ "Données temporaires (data)" → Problème

2. Vérifier que `data-osons/` existe sur le serveur

3. Vérifier les permissions d'écriture :
   ```bash
   chmod 755 data-osons
   chmod 644 data-osons/*.json
   ```

### Problème : Perte de données après upload

**Cause** : Upload du dossier `data-osons/` local qui écrase les données serveur.

**Solution** :
1. Récupérer les sauvegardes dans `data-osons/backups/`
2. Restaurer le fichier le plus récent
3. Ne plus uploader `data-osons/` à l'avenir

### Problème : Badge "Données temporaires"

**Cause** : Le fichier `admin/config.php` n'a pas été uploadé ou `data-osons/` n'existe pas.

**Solution** :
1. Uploader `admin/config.php`
2. Vérifier que `data-osons/` existe sur le serveur
3. Recharger l'admin

---

## 📞 Support

- **Email** : `bonjour@osons-saint-paul.fr`
- **Développeur** : Claude (via Cursor AI)
- **Date de mise en place** : Octobre 2025

---

## 📜 Historique des versions

### Version 2.0 (Octobre 2025)
- ✅ Système de données persistantes (`data-osons/`)
- ✅ Sauvegardes automatiques quotidiennes
- ✅ Protection Git des données de production
- ✅ Google reCAPTCHA v3 sur tous les formulaires
- ✅ Service d'envoi d'emails centralisé (Brevo API)
- ✅ Politique de confidentialité RGPD complète
- ✅ Badge de proposition citoyenne

### Version 1.0 (Septembre 2025)
- Interface d'administration complète
- Gestion des propositions citoyennes
- Formulaires publics
- Système d'envoi d'emails de base

