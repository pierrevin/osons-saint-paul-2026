# Osons Saint-Paul 2026

Site de campagne électorale pour les municipales de Saint-Paul-sur-Save (31530).

## 🚀 Démarrage rapide

### Prérequis
- PHP 7.4+
- Serveur web (Apache/Nginx)
- Hébergement OVH (production)

### Installation locale

1. Cloner le dépôt
   ```bash
git clone [URL_DU_DEPOT]
cd osons-saint-paul
```

2. Créer le répertoire de données
```bash
mkdir -p data-osons/backups
```

3. Configurer les permissions
   ```bash
chmod 755 data-osons
chmod 644 data-osons/*.json
```

4. Lancer un serveur local
   ```bash
   php -S localhost:8000
   ```

5. Accéder au site : `http://localhost:8000`

## 📁 Structure du projet

```
osons-saint-paul/
├── admin/              # Interface d'administration
├── forms/              # Formulaires publics
├── data-osons/         # Données persistantes (production)
├── uploads/            # Fichiers uploadés
├── tools/              # Scripts utilitaires
├── index.php           # Page d'accueil
└── DOCUMENTATION.md    # Documentation complète
```

## 🔐 Administration

**URL** : `/admin/pages/schema_admin_new.php`

**Sections** :
- Dashboard
- Hero (bannière)
- Programme (propositions)
- Citations
- Équipe
- Rendez-vous
- Charte
- Contact
- Médiathèque
- Gestion utilisateurs
- Logs de sécurité

## 📧 Configuration emails

Fichier : `forms/email-config.php`

```php
define('ADMIN_EMAIL', 'bonjour@osons-saint-paul.fr');
define('FROM_EMAIL', 'bonjour@osons-saint-paul.fr');
define('FROM_NAME', 'Osons Saint-Paul 2026');
```

## 🔒 Sécurité

- ✅ Google reCAPTCHA v3 sur tous les formulaires
- ✅ Tokens CSRF pour toutes les soumissions
- ✅ Rate limiting sur les formulaires
- ✅ Logs de sécurité complets
- ✅ Conformité RGPD

## 💾 Sauvegardes

**Automatique** : Quotidienne à 3h (cron)
**Rétention** : 3 jours
**Script** : `tools/backup_daily.php`

## 📚 Documentation

Pour plus de détails, consultez [`DOCUMENTATION.md`](./DOCUMENTATION.md)

## 🆘 Support

**Email** : bonjour@osons-saint-paul.fr

## 📜 Licence

© 2025 Osons Saint-Paul - Tous droits réservés
