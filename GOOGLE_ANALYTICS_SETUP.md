# Configuration Google Analytics pour le Dashboard

## Étapes pour activer les vraies statistiques GA

### 1. Installer Composer (si pas déjà fait)

```bash
# Sur macOS avec Homebrew
brew install composer

# Sur Ubuntu/Debian
sudo apt install composer

# Sur Windows
# Télécharger depuis https://getcomposer.org/
```

### 2. Installer les dépendances Google

```bash
cd /Users/pierre/Desktop/Osons\ -\ Saint\ Paul\ Site
composer install
```

### 3. Créer un Service Account Google Cloud

1. Aller sur [Google Cloud Console](https://console.cloud.google.com/)
2. Sélectionner le projet "osons-saint-paul-2026" (ou créer un nouveau projet)
3. Activer l'API "Google Analytics Data API v1"
4. Aller dans "IAM & Admin" > "Service Accounts"
5. Créer un nouveau service account :
   - Nom : `ga-service`
   - Description : `Service account pour Google Analytics`
6. Télécharger la clé JSON

### 4. Configurer les permissions Google Analytics

1. Dans Google Analytics, aller dans "Admin" > "Property" > "Property access management"
2. Cliquer sur "+" pour ajouter un utilisateur
3. Ajouter l'email du service account (format : `ga-service@osons-saint-paul-2026.iam.gserviceaccount.com`)
4. Rôle : "Viewer"
5. Sauvegarder

### 5. Placer le fichier de credentials

1. Renommer le fichier JSON téléchargé en `ga-service-account.json`
2. Le placer dans `/credentials/ga-service-account.json`
3. Remplacer le contenu du fichier existant par le vrai fichier JSON

### 6. Vérifier la configuration

1. Aller dans l'admin : `http://localhost:8000/admin/schema_admin_new.php`
2. Cliquer sur "Tableau de Bord"
3. Vérifier que l'indicateur affiche "Données réelles Google Analytics"

## Informations importantes

- **Property ID GA4** : `12275333436` (déjà configuré)
- **Fichier de credentials** : `/credentials/ga-service-account.json`
- **Sécurité** : Le fichier est dans `.gitignore` pour éviter les fuites

## Dépannage

### Erreur "Class not found"
- Vérifier que `composer install` a été exécuté
- Vérifier que le fichier `vendor/autoload.php` existe

### Erreur "Credentials manquant"
- Vérifier que le fichier `ga-service-account.json` existe
- Vérifier que le contenu JSON est valide

### Erreur "Permission denied"
- Vérifier que le service account a le rôle "Viewer" dans Google Analytics
- Vérifier que l'API "Google Analytics Data API v1" est activée

## Données affichées

Une fois configuré, le dashboard affichera :

- **Visiteurs uniques** (30 derniers jours)
- **Pages vues** totales
- **Durée moyenne** de session
- **Utilisateurs actifs** en temps réel
- **Top 5 pages** les plus visitées
- **Top 5 sources** de trafic

Toutes les données sont mises à jour en temps réel depuis Google Analytics.
