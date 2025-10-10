# 🧹 Nettoyage des fichiers

## Fichiers à supprimer du serveur (si présents)

### Scripts de diagnostic temporaires
- ❌ `admin/test-data-path.php` (script de debug)
- ❌ `admin/check-data-path.php` (script de debug)

### Documentation temporaire
- ❌ `UPLOAD_INSTRUCTIONS.txt` (instructions temporaires)

### Anciens scripts de développement
Le dossier `docs/scripts/` contient d'anciens scripts de test qui peuvent être supprimés :
- ❌ `docs/scripts/test_admin_migration.php`
- ❌ `docs/scripts/test_final.php`
- ❌ `docs/scripts/test_json_integrity.php`
- ❌ `docs/scripts/test_refactor.php`
- ❌ `docs/scripts/test_sidebar_links.php`
- ❌ `docs/scripts/clean_debug_code.php`

**Note** : Les scripts dans `tools/` sont fonctionnels et doivent être conservés :
- ✅ `tools/backup_daily.php` (sauvegardes automatiques)
- ✅ `tools/test-email-system.php` (test configuration email)
- ✅ `tools/test-contact-forms.php` (test formulaires)
- ✅ `tools/test-approval-email.php` (test emails propositions)

## Fichiers obsolètes dans data/

Le dossier `data/` contient des fichiers obsolètes (le système utilise maintenant `data-osons/`) :

**À GARDER** :
- ✅ `data/load_content.php` (fichier PHP fonctionnel)
- ✅ `data/README.txt` (documentation)

**OBSOLÈTES (mais garder comme backup)** :
- ⚠️ `data/site_content.json` (anciennes données)
- ⚠️ `data/propositions.json` (anciennes données)
- ⚠️ `data/admin_log.json` (anciens logs)
- ⚠️ `data/backups/` (anciennes sauvegardes)

**Recommandation** : Garder ces fichiers quelques semaines comme backup de sécurité, puis les supprimer une fois certain que `data-osons/` fonctionne parfaitement.

## Commandes de nettoyage (optionnel)

### Sur le serveur (via SSH)

```bash
# Supprimer les scripts de debug
rm -f admin/test-data-path.php
rm -f admin/check-data-path.php
rm -f UPLOAD_INSTRUCTIONS.txt

# Supprimer les anciens scripts de développement
rm -rf docs/scripts/test_*.php
rm -f docs/scripts/clean_debug_code.php
```

### En local

Les fichiers suivants ont déjà été supprimés :
- ✅ `admin/test-data-path.php`
- ✅ `admin/check-data-path.php`
- ✅ `UPLOAD_INSTRUCTIONS.txt`

## ⚠️ Important

**NE JAMAIS SUPPRIMER** :
- 🚫 Le dossier `data-osons/` (données de production)
- 🚫 Les fichiers dans `tools/` (scripts fonctionnels)
- 🚫 Les fichiers `*.json` actifs dans `data-osons/`

