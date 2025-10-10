# 📝 Mémo rapide - À garder sous la main

## 🚨 Règles essentielles

### ❌ NE JAMAIS FAIRE
1. **Uploader `data-osons/` de local vers serveur via FileZilla**
   → Cela écraserait toutes les données de production !

2. **Modifier directement les fichiers JSON sur le serveur**
   → Utiliser toujours l'interface admin

3. **Supprimer le dossier `data-osons/` sur le serveur**
   → C'est là que sont toutes les données !

### ✅ TOUJOURS FAIRE
1. **Vérifier le badge dans l'admin** (en bas à droite)
   → Doit afficher "✅ Données persistantes (data-osons)"

2. **Commiter dans Git avant tout upload FileZilla**
   ```bash
   git add .
   git commit -m "Description"
   git push
   ```

3. **Tester après chaque modification importante**

---

## 🔗 URLs importantes

- **Site public** : https://osons-saint-paul.fr
- **Admin** : https://osons-saint-paul.fr/admin/pages/schema_admin_new.php
- **Formulaire propositions** : https://osons-saint-paul.fr/proposez
- **Politique confidentialité** : https://osons-saint-paul.fr/forms/politique-confidentialite.php

---

## 📧 Contacts

- **Email admin** : bonjour@osons-saint-paul.fr
- **Responsable données** : Pierre Vincenot, Saint-Paul-Sur-Save, 31530

---

## 🛠️ En cas de problème

### Badge "Données temporaires" au lieu de "Persistantes"
1. Vérifier que `data-osons/` existe sur le serveur
2. Re-uploader `admin/config.php`
3. Recharger l'admin

### Données non sauvegardées
1. Vérifier le badge (doit être vert "Persistantes")
2. Vérifier via FileZilla que `data-osons/site_content.json` est modifié
3. Si problème : contacter le support

### Perte de données après upload
1. Aller dans `data-osons/backups/` sur le serveur
2. Récupérer la sauvegarde la plus récente
3. Restaurer le fichier JSON concerné

### Emails non reçus
1. Vérifier les logs : `admin/logs/security.log`
2. Tester avec : `tools/test-email-system.php`
3. Vérifier que l'email admin est correct dans `forms/email-config.php`

---

## 📁 Structure des données

```
data-osons/
├── site_content.json      → Toutes les données du site (équipe, rendez-vous, etc.)
├── propositions.json      → Propositions citoyennes uniquement
├── admin_log.json         → Logs de sécurité
└── backups/               → Sauvegardes automatiques (3 jours)
```

---

## 🔄 Workflow déploiement

### Modifications simples (CSS, textes, etc.)
```bash
# En local
git add .
git commit -m "Description"
git push

# Sur serveur : déploiement auto via webhook
# OU upload manuel via FileZilla
```

### Ajout de contenu (équipe, rendez-vous, propositions)
→ Directement depuis l'admin en production
→ Pas besoin de Git/FileZilla

---

## 💾 Sauvegardes

- **Automatique** : Tous les jours à 3h du matin
- **Rétention** : 3 jours
- **Localisation** : `data-osons/backups/`

### Forcer une sauvegarde manuelle (si besoin)
```bash
php tools/backup_daily.php --run-now
```

---

## 📞 Support rapide

### Vérifier que tout fonctionne
1. Aller sur l'admin
2. Badge en bas à droite = vert ✅
3. Ajouter un rendez-vous test
4. Vérifier qu'il apparaît → OK !

### Contact développeur
- Cursor AI / Claude
- Session de travail : Octobre 2025

---

**Dernière mise à jour : 10 octobre 2025**
