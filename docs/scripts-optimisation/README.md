# Scripts d'Optimisation d'Images

Ces scripts ont été utilisés pour optimiser les images de la galerie en amont, améliorant ainsi les performances du formulaire équipe.

## Scripts disponibles

### 1. `batch_optimize_gallery.php`
Script de traitement en lot pour optimiser toutes les images de la galerie.

**Usage :**
```bash
cd "/Users/pierre/Desktop/Osons - Saint Paul Site"
php docs/scripts-optimisation/batch_optimize_gallery.php
```

**Fonctionnalités :**
- Traite toutes les images du dossier `uploads/gallery/`
- Convertit en WebP avec qualité 90%
- Redimensionne au format 3:4 (600x800px)
- Compression de ~96% (de 99.62 MB à 3.73 MB)
- Sauvegarde dans `uploads/gallery_optimized/`

### 2. `auto_optimize_new_images.php`
Script d'optimisation automatique pour les nouvelles images.

**Usage :**
```bash
php docs/scripts-optimisation/auto_optimize_new_images.php
```

**Options :**
- `--clean` : Nettoie les images optimisées orphelines
- `--health` : Vérifie l'état du système

### 3. `optimize-gallery.php`
Interface web d'administration pour l'optimisation (désactivée).

## Résultats

- **65 images traitées** avec succès
- **Compression de 96.3%** : de 99.62 MB à 3.73 MB
- **Format WebP** : Toutes les images converties
- **Performance** : Formulaire équipe beaucoup plus rapide

## Notes

- Les scripts ont été exécutés une seule fois
- Les images optimisées sont automatiquement utilisées par le formulaire équipe
- Les scripts sont conservés pour référence future
- L'interface web a été supprimée du menu admin

## Structure des dossiers

```
uploads/
├── gallery/                    # Images originales (conservées)
└── gallery_optimized/          # Images optimisées (utilisées)
```

## Date d'exécution

Scripts exécutés le : 2025-01-06
Images optimisées : 65 fichiers
Temps de traitement : ~2 minutes
