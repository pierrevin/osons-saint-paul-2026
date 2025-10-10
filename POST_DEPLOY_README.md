# 🚀 Procédure de Post-Déploiement

## Problème résolu

Après un déploiement Git sur OVH, les fichiers d'autoload Composer peuvent être obsolètes, causant l'erreur :
```
Class "Google\Auth\Credentials\ServiceAccountCredentials" not found
```

## Solution automatique (RECOMMANDÉE)

### Configuration du webhook OVH :

**URL du webhook** : `https://osons-saint-paul.fr/webhook-deploy.php`

Le webhook s'exécute **automatiquement** après chaque déploiement Git et :
1. ✅ Tente de régénérer l'autoloader Composer
2. ✅ Vérifie que toutes les classes Google sont disponibles
3. ✅ Logue le résultat dans `logs/webhook-deploy.log`

### Vérifier les logs du webhook :

Consultez : `logs/webhook-deploy.log` pour voir le résultat de chaque déploiement.

---

## Solution manuelle (si webhook échoue)

### Après chaque déploiement Git sur OVH :

**Accédez à** : `https://osons-saint-paul.fr/post-deploy.php?token=VOTRE_TOKEN`

**Token d'accès** : `<?= md5('osons-saint-paul-2026') ?>`

Le script va :
1. ✅ Vérifier l'installation Composer
2. ✅ Tenter de régénérer l'autoloader
3. ✅ Vérifier que toutes les classes Google sont disponibles
4. ✅ Afficher un rapport détaillé

---

## Solution manuelle (si Composer non disponible)

Si le script indique que Composer n'est pas disponible sur le serveur :

### Via FileZilla, uploadez ces fichiers depuis local vers OVH :

```
vendor/composer/autoload_classmap.php
vendor/composer/autoload_namespaces.php
vendor/composer/autoload_psr4.php
vendor/composer/autoload_real.php
vendor/composer/autoload_static.php
```

**Ces fichiers sont maintenant exclus du Git** (voir `.gitignore`), donc ils ne seront plus écrasés lors des déploiements.

---

## Prévention

### Fichiers exclus du Git :

Les fichiers d'autoload Composer sont maintenant dans `.gitignore` :
- `vendor/composer/autoload_classmap.php`
- `vendor/composer/autoload_namespaces.php`
- `vendor/composer/autoload_psr4.php`
- `vendor/composer/autoload_real.php`
- `vendor/composer/autoload_static.php`
- `vendor/composer/installed.php`

### Workflow recommandé :

1. **Développement local** : Modifier le code
2. **Commit & Push** : `git push origin main`
3. **Déploiement automatique** : Le webhook OVH déploie
4. **Post-déploiement** : Exécuter `post-deploy.php` (ou upload manuel)
5. **Vérification** : Tester l'admin

---

## Diagnostic

En cas de problème, utilisez :
- `https://osons-saint-paul.fr/admin/diagnostic-analytics.php` (à créer si besoin)
- `https://osons-saint-paul.fr/post-deploy.php?token=TOKEN`

---

## Notes importantes

- ⚠️ Ne commitez JAMAIS les fichiers `autoload_*.php` dans Git
- ⚠️ Conservez `post-deploy.php` pour les prochains déploiements
- ⚠️ Le token est nécessaire pour sécuriser l'accès au script

---

**Date de création** : 2025-10-11
**Problème résolu** : Erreur ServiceAccountCredentials not found

