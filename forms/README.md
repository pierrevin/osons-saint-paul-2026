# 💡 Système de Propositions Citoyennes - Osons Saint-Paul 2026

## 📋 Vue d'ensemble

Ce système permet aux citoyens de soumettre des propositions pour améliorer Saint-Paul. Les propositions sont collectées via un formulaire intégré, stockées et peuvent être gérées par l'équipe administrative.

## 🏗️ Architecture

### Structure des fichiers
```
forms/
├── config.php                          # Configuration principale
├── proposition-citoyenne.php           # Formulaire public
├── process-form.php                    # Traitement des soumissions
├── confirmation.php                    # Page de confirmation
├── test-system.php                     # Tests du système
├── README.md                           # Documentation
└── admin/
    └── manage-proposition.php          # Gestion individuelle détaillée

admin/pages/
├── schema_admin.php                    # Interface admin principale (avec onglets unifiés)
└── citizen-proposals-ajax.php          # Endpoint AJAX pour les actions
```

### Données
- **Stockage** : `data/propositions.json`
- **Sauvegardes** : `data/backups/propositions/`
- **Logs** : `logs/propositions_errors.log`

## 🚀 Fonctionnalités

### Pour les citoyens
- ✅ Formulaire intuitif et responsive
- ✅ Validation en temps réel
- ✅ Confirmation par email
- ✅ Suivi des propositions
- ✅ Limite anti-spam (5 propositions/jour/email)

### Pour les administrateurs
- ✅ Interface de gestion complète
- ✅ Filtres par statut et catégorie
- ✅ Actions rapides (approuver/rejeter)
- ✅ Intégration au programme principal
- ✅ Statistiques en temps réel
- ✅ Gestion individuelle détaillée

## 🔧 Configuration

### 1. Emails
Modifiez `config.php` :
```php
define('ADMIN_EMAIL', 'votre@email.fr');
define('FROM_EMAIL', 'noreply@votresite.fr');
```

### 2. Sécurité
Changez le mot de passe admin :
```php
define('ADMIN_PASSWORD', 'votre_mot_de_passe_securise');
```

### 3. Limites
Ajustez selon vos besoins :
```php
define('MAX_PROPOSITION_LENGTH', 500);
define('MAX_TITLE_LENGTH', 100);
```

## 📝 Utilisation

### Accès au formulaire
- **URL publique** : `/forms/proposition-citoyenne.php`
- **Lien dans le menu** : "💡 Proposition"

### Interface d'administration
- **URL admin principal** : `/admin/pages/schema_admin.php` (section Programme → onglet Propositions citoyennes)
- **URL gestion individuelle** : `/forms/admin/manage-proposition.php?id=XXX`
- **Mot de passe** : `admin2026` (par défaut)
- **Interface unifiée** : Propositions du programme + propositions citoyennes dans la même section

### Workflow typique
1. **Citoyen** soumet une proposition
2. **Système** envoie confirmation + notifie admin
3. **Admin** examine et approuve/rejette
4. **Si approuvée** → peut être intégrée au programme
5. **Citoyen** est informé de l'avancement

## 🛡️ Sécurité

### Mesures implémentées
- ✅ Protection CSRF
- ✅ Validation et nettoyage des données
- ✅ Limite de propositions par email
- ✅ Authentification admin
- ✅ Sauvegardes automatiques
- ✅ Logs d'erreurs

### Recommandations production
- 🔒 Changer le mot de passe admin
- 🔒 Configurer HTTPS
- 🔒 Limiter l'accès aux fichiers sensibles
- 🔒 Surveiller les logs d'erreurs
- 🔒 Mettre en place un système de backup externe

## 🧪 Tests

### Test du système
Accédez à `/forms/test-system.php` pour :
- Vérifier l'installation
- Tester les fonctions
- Valider la configuration
- Voir les statistiques

### Tests manuels recommandés
1. **Soumission** : Tester le formulaire complet
2. **Emails** : Vérifier l'envoi des confirmations
3. **Admin** : Tester toutes les actions
4. **Intégration** : Ajouter une proposition au programme
5. **Limites** : Tester la limite de 5 propositions/jour

## 📊 Maintenance

### Nettoyage automatique
- Les propositions rejetées > 1 an sont automatiquement supprimées
- Les sauvegardes > 10 sont automatiquement nettoyées

### Monitoring
- Surveillez `logs/propositions_errors.log`
- Vérifiez l'espace disque des sauvegardes
- Contrôlez les statistiques d'utilisation

## 🔄 Intégration avec le site

### Menu principal
Le lien "💡 Proposition" a été ajouté au menu principal.

### Programme principal
Les propositions approuvées peuvent être intégrées au programme avec le label "Proposition citoyenne".

### Admin principal
Une section dédiée a été ajoutée dans l'interface d'administration.

## 🆘 Dépannage

### Problèmes courants

**Formulaire ne s'affiche pas**
- Vérifiez les permissions du dossier `forms/`
- Contrôlez la configuration PHP

**Emails non envoyés**
- Vérifiez la configuration SMTP du serveur
- Testez avec `test-system.php`

**Admin inaccessible**
- Vérifiez le mot de passe dans `config.php`
- Contrôlez les permissions des fichiers admin

**Données corrompues**
- Restaurez depuis `data/backups/propositions/`
- Utilisez `test-system.php` pour diagnostiquer

### Support
Pour toute question technique, consultez :
1. Ce README
2. Le fichier `test-system.php`
3. Les logs d'erreurs
4. La documentation PHP du serveur

## 🎯 Évolutions futures

### Fonctionnalités possibles
- 📱 Notifications push
- 📊 Tableaux de bord avancés
- 🔔 Système de notifications
- 📈 Analytics détaillées
- 🌐 API REST
- 📧 Templates d'emails personnalisés
- 🏷️ Système de tags
- 👥 Gestion des équipes

### Optimisations
- ⚡ Cache des statistiques
- 🗄️ Base de données (si volume important)
- 🔍 Recherche avancée
- 📱 PWA (Progressive Web App)
- 🌍 Internationalisation

---

**Développé avec ❤️ pour Osons Saint-Paul 2026**
