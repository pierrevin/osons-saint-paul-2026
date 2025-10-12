# 🚀 Guide de Déploiement Automatique

**Site : Osons Saint-Paul 2026**  
**Date de création : 2025-10-11**  
**Version : 2.0.0**

---

## 📋 Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Configuration initiale](#configuration-initiale)
3. [Workflow de déploiement](#workflow-de-déploiement)
4. [Dépannage](#dépannage)
5. [Maintenance](#maintenance)

---

## Vue d'ensemble

### Architecture de déploiement

```
┌─────────────┐      ┌─────────────┐      ┌─────────────┐
│   Local     │──1──>│   GitHub    │──2──>│     OVH     │
│ (votre PC)  │      │ Repository  │      │   Serveur   │
└─────────────┘      └─────────────┘      └─────────────┘
                                                  │
                                                  │ 3
                                                  ▼
                                          ┌─────────────┐
                                          │  Webhook    │
                                          │Deploy Script│
                                          └─────────────┘
```

### Composants

- **GitHub** : Repository principal (https://github.com/pierrevin/osons-saint-paul-2026)
- **OVH Webhook** : Déploiement automatique Git
- **webhook-deploy.php** : Script de post-déploiement automatique
- **Composer** : Gestionnaire de dépendances PHP

---

## Configuration initiale

### 1. Configuration OVH

#### a) Webhook Git

Dans le panel OVH, configurez le webhook :

**Section** : Web Cloud → Hébergement → osons-saint-paul.fr → Plus → Webhook Git

**Paramètres** :
- **Repository Git** : `https://github.com/pierrevin/osons-saint-paul-2026.git`
- **Branche** : `main`
- **Répertoire de déploiement** : `/osons-saint-paul`
- **Clé SSH** : (générée par OVH)
- **Script post-déploiement** : `webhook-deploy.php` (ou laissez vide, le script sera appelé manuellement)

#### b) Webhook post-déploiement (optionnel)

Si OVH permet de configurer un webhook après déploiement :

**URL à appeler** : `https://osons-saint-paul.fr/webhook-deploy.php`

### 2. Configuration GitHub

#### a) Ajouter la clé SSH OVH

1. Dans OVH, copiez la clé SSH publique générée
2. Sur GitHub → Settings → Deploy keys
3. Ajoutez la clé OVH avec les permissions de lecture

#### b) Protection de la branche main

**Settings → Branches → Branch protection rules** :
- ✅ Require pull request reviews (optionnel)
- ✅ Require status checks to pass
- ✅ Include administrators

---

## Workflow de déploiement

### Déploiement standard

```bash
# 1. Développement local
git add .
git commit -m "Description des modifications"

# 2. Push vers GitHub
git push origin main

# 3. Automatique : OVH détecte et déploie
# (Attendre 2-5 minutes)

# 4. Vérification
# Consulter : https://osons-saint-paul.fr/logs/webhook-deploy.log
```

### Workflow détaillé

#### Étape 1 : Modifications locales

Modifiez les fichiers nécessaires :
```bash
cd /Users/pierre/Desktop/Osons\ -\ Saint\ Paul\ Site
# Modifier les fichiers...
```

#### Étape 2 : Commit Git

```bash
git status                    # Vérifier les modifications
git add fichier1 fichier2     # Ajouter les fichiers modifiés
git commit -m "Message"       # Commiter avec message descriptif
```

**Convention de messages de commit** :
- `✨ feat:` Nouvelle fonctionnalité
- `🐛 fix:` Correction de bug
- `🔧 config:` Modification de configuration
- `📝 docs:` Documentation
- `🎨 style:` CSS/Design
- `♻️ refactor:` Refactoring code
- `🚀 deploy:` Déploiement

#### Étape 3 : Push vers GitHub

```bash
git push origin main
```

**Résultat attendu** :
```
Enumerating objects: X, done.
Counting objects: 100% (X/X), done.
...
To https://github.com/pierrevin/osons-saint-paul-2026.git
   xxxxx..yyyyy  main -> main
```

#### Étape 4 : Déploiement automatique OVH

OVH détecte le push et déclenche :
1. **Pull du code** depuis GitHub
2. **Déploiement** dans `/home/pierrevit/osons-saint-paul/`
3. **Exécution** de `webhook-deploy.php` (si configuré)

**Durée** : 2-5 minutes selon la taille des modifications

#### Étape 5 : Vérification

**a) Consulter les logs du webhook** :
```
https://osons-saint-paul.fr/logs/webhook-deploy.log
```

**Contenu attendu** :
```
[2025-10-11 12:34:56] === WEBHOOK DEPLOY TRIGGERED ===
[2025-10-11 12:34:56] Tentative de régénération de l'autoloader...
[2025-10-11 12:34:57] ✅ Autoloader régénéré avec succès
[2025-10-11 12:34:57] ✅ Google\Client disponible
[2025-10-11 12:34:57] ✅ Google\Service\AnalyticsData disponible
[2025-10-11 12:34:57] ✅ Google\Auth\Credentials\ServiceAccountCredentials disponible
[2025-10-11 12:34:57] 🎉 Déploiement terminé avec succès
```

**b) Tester le site** :
- Site public : https://osons-saint-paul.fr/
- Admin : https://osons-saint-paul.fr/admin/

---

## Dépannage

### Problème 1 : Webhook ne se déclenche pas

**Symptômes** :
- Push Git réussi sur GitHub
- Aucun changement sur le serveur OVH

**Solutions** :
1. Vérifier la configuration du webhook OVH
2. Vérifier que la clé SSH est correctement configurée sur GitHub
3. Consulter les logs OVH (panel → Logs)

### Problème 2 : Erreur "Class not found" après déploiement

**Symptômes** :
```
Class "Google\Auth\Credentials\ServiceAccountCredentials" not found
```

**Solutions** :

**Option A : Exécuter le script manuel**
```
https://osons-saint-paul.fr/post-deploy.php?token=cb8e8c9af483e75f563e54787b8a5e2f
```

**Option B : Upload manuel via FileZilla**
Uploadez ces fichiers depuis local vers OVH :
```
vendor/composer/autoload_classmap.php
vendor/composer/autoload_namespaces.php
vendor/composer/autoload_psr4.php
vendor/composer/autoload_real.php
vendor/composer/autoload_static.php
```

**Option C : SSH (si disponible)**
```bash
ssh username@osons-saint-paul.fr
cd osons-saint-paul
composer dump-autoload --optimize
```

### Problème 3 : Admin vide / sections disparues

**Symptômes** :
- Admin charge mais toutes les sections sont vides
- Sidebar visible mais pas de contenu

**Diagnostic** :
```
https://osons-saint-paul.fr/admin/diagnostic-analytics.php
```

**Solutions** :
1. Vérifier les logs : `logs/webhook-deploy.log`
2. Vérifier que `data-osons/site_content.json` existe
3. Vérifier les permissions des fichiers (755 pour dossiers, 644 pour fichiers)

### Problème 4 : Mode maintenance bloqué

**Symptômes** :
- Site affiche toujours la page de maintenance

**Solution** :
Le mode maintenance est contrôlé dans `index.php` lignes 5-12.

Pour **désactiver** :
```php
// MODE MAINTENANCE DÉSACTIVÉ - Site public accessible
// Pour réactiver le mode maintenance, décommentez les lignes suivantes :
/*
$user_connected = isset($_SESSION['user_id']) || isset($_SESSION['admin_logged_in']);
if (!$user_connected) {
    include __DIR__ . '/maintenance.php'; exit;
}
*/
```

Pour **activer** :
```php
// MODE MAINTENANCE ACTIVÉ
$user_connected = isset($_SESSION['user_id']) || isset($_SESSION['admin_logged_in']);
if (!$user_connected) {
    include __DIR__ . '/maintenance.php'; exit;
}
```

---

## Maintenance

### Fichiers sensibles à ne JAMAIS commiter

Ces fichiers sont dans `.gitignore` et ne doivent **jamais** être versionnés :

**Données de production** :
- `data-osons/` (données du site en production)
- `admin/users.json` (si modifié en production)
- `admin/logs/` (logs sensibles)

**Configuration** :
- `credentials/` (clés API Google)
- `forms/recaptcha-config.php` (clés reCAPTCHA)

**Fichiers générés** :
- `vendor/composer/autoload_*.php` (régénérés à chaque déploiement)

### Fichiers à uploader manuellement (une seule fois)

Ces fichiers ne sont pas dans Git et doivent être uploadés via FileZilla :

1. **`credentials/ga-service-account.json`**
   - Clés Google Analytics
   - Local : `credentials/ga-service-account.json`
   - Serveur : `/credentials/ga-service-account.json`

2. **`data-osons/site_content.json`** (si besoin de restaurer)
   - Données du site
   - Local : `data-osons/site_content.json`
   - Serveur : `/data-osons/site_content.json`

### Backup automatique

Les données sont sauvegardées automatiquement dans :
```
data-osons/backups/site_content.json.YYYY-MM-DD-HH-ii-ss.json
```

### Monitoring

**Logs à surveiller** :
- `logs/webhook-deploy.log` - Déploiements automatiques
- `admin/logs/security.log` - Tentatives de connexion
- `admin/logs/email_logs.log` - Envois d'emails
- `logs/newsletter_logs.log` - Inscriptions newsletter

**Commande pour voir les derniers logs** :
```bash
tail -50 logs/webhook-deploy.log
```

---

## Checklist de déploiement

Avant chaque déploiement important :

- [ ] Tests en local réussis
- [ ] Commit avec message descriptif
- [ ] Push vers GitHub réussi
- [ ] Attendre fin du déploiement OVH (2-5 min)
- [ ] Vérifier logs webhook-deploy.log
- [ ] Tester le site public
- [ ] Tester l'admin
- [ ] Vérifier Google Analytics
- [ ] Backup des données si modifications importantes

---

## Support

### Ressources

- **Documentation OVH** : https://docs.ovh.com/fr/hosting/
- **Documentation Composer** : https://getcomposer.org/doc/
- **Repository GitHub** : https://github.com/pierrevin/osons-saint-paul-2026

### Contacts

- **Développeur** : Pierre Vincenot (pierre.vincenot@gmail.com)
- **Support OVH** : https://www.ovh.com/fr/support/

---

## Historique des versions

### Version 2.0.0 (2025-10-11)
- ✅ Déploiement automatique Git
- ✅ Webhook post-déploiement
- ✅ Google Analytics intégré
- ✅ SEO optimisé complet
- ✅ Tracking UTM
- ✅ Gestion automatique autoloader Composer

### Version 1.0.0 (2025-10-06)
- ✅ Site initial avec admin
- ✅ Gestion du contenu dynamique
- ✅ Formulaires de contact

---

**Dernière mise à jour** : 2025-10-11  
**Auteur** : Pierre Vincenot  
**Projet** : Osons Saint-Paul 2026


