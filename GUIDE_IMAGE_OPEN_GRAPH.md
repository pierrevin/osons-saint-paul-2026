# Guide : Créer une image Open Graph optimale

## 📐 Spécifications techniques

### Dimensions recommandées
- **Taille idéale** : 1200 x 630 pixels
- **Format** : JPG ou PNG (WebP supporté mais moins universel)
- **Ratio** : 1.91:1
- **Poids maximum** : 5 MB (idéalement < 300 KB)

### Dimensions alternatives
- **Minimum** : 600 x 315 pixels
- **Carré (Twitter)** : 1200 x 1200 pixels (pour summary_large_image)

---

## 🎨 Recommandations de design

### Contenu visuel
1. **Logo** : Bien visible, dans le tiers supérieur
2. **Texte principal** : 
   - Titre court et impactant
   - Taille de police : minimum 60px
   - Maximum 70 caractères
3. **Slogan** : Court et lisible (30-40px)
4. **Zone de sécurité** : 
   - Marges de 40px de chaque côté
   - Éviter le contenu crucial dans les bords

### Couleurs
- **Palette** : Utiliser les couleurs de la charte
  - Coral : #ec654f
  - Jaune : #fcc549
  - Bleu : #4e9eb0
  - Vert : #65ae99
  - Bleu foncé : #004a6d
  - Crème : #FAF5EE
- **Contraste** : Minimum 4.5:1 pour la lisibilité

### Typographie
- **Police principale** : Lato (bold pour titres)
- **Police script** : Caveat (pour éléments décoratifs)
- **Lisibilité** : Tester sur mobile (aperçu petit)

---

## ✅ Checklist de validation

### Avant publication
- [ ] Dimensions exactes : 1200 x 630 px
- [ ] Poids < 300 KB
- [ ] Texte lisible en petit (aperçu mobile)
- [ ] Logo visible et reconnaissable
- [ ] Pas de texte coupé sur les bords
- [ ] Contraste suffisant
- [ ] Pas de fautes d'orthographe

### Tests
- [ ] **Facebook Debugger** : https://developers.facebook.com/tools/debug/
- [ ] **Twitter Card Validator** : https://cards-dev.twitter.com/validator
- [ ] **LinkedIn Post Inspector** : https://www.linkedin.com/post-inspector/
- [ ] **Test sur mobile** : Vérifier l'aperçu WhatsApp/Messenger

---

## 🖼️ Exemple de structure

```
┌─────────────────────────────────────┐
│  [Logo]                             │  ← 100px depuis le haut
│                                     │
│  Osons Saint-Paul 2026              │  ← Titre principal (70px)
│                                     │
│  Liste citoyenne pour               │  ← Sous-titre (40px)
│  les municipales                    │
│                                     │
│  [Image ville/équipe en fond]      │  ← Fond subtil (opacité 30%)
│                                     │
└─────────────────────────────────────┘
    Marges 40px de chaque côté
```

---

## 🛠️ Outils recommandés

### Création
1. **Canva** (gratuit) : https://www.canva.com
   - Template "Open Graph"
   - Dimensions pré-configurées
   - Facile à utiliser

2. **Figma** (gratuit) : https://www.figma.com
   - Professionnel
   - Collaboration possible
   - Export optimisé

3. **Photoshop/GIMP** (avancé)
   - Contrôle total
   - Calques et masques

### Optimisation
1. **TinyPNG** : https://tinypng.com
   - Compression sans perte de qualité
   - Réduit le poids de 60-80%

2. **Squoosh** : https://squoosh.app
   - Google Web Dev
   - Comparaison avant/après

---

## 📱 Aperçus par plateforme

### Facebook
- **Affichage** : 1200 x 630 px
- **Crop mobile** : Centre de l'image
- **Texte** : Max 20% de l'image (ancienne règle, plus stricte maintenant)

### Twitter
- **Affichage** : 1200 x 600 px (légèrement cropé en hauteur)
- **Summary large** : Privilégier le haut et centre
- **Texte** : Aucune limite

### LinkedIn
- **Affichage** : 1200 x 627 px
- **Crop** : Similaire à Facebook
- **Professionnel** : Éviter trop de couleurs vives

### WhatsApp/Messenger
- **Affichage** : Très petit (thumbnail)
- **Important** : Logo et texte principal doivent rester lisibles

---

## 🎯 Recommandations spécifiques Osons Saint-Paul

### Page d'accueil
**Contenu suggéré** :
- Logo Osons Saint-Paul (feuille)
- Titre : "Osons Saint-Paul 2026"
- Slogan : "Construisons ensemble le village vivant et partagé"
- Fond : Photo de l'équipe ou du village (subtil)
- Couleurs : Coral + Vert + Crème

### Page proposition citoyenne
**Contenu suggéré** :
- Icône ampoule 💡 (grande)
- Titre : "Faites une proposition citoyenne"
- Call-to-action : "Partagez vos idées pour Saint-Paul"
- Couleurs : Jaune + Bleu

### Pages institutionnelles (mentions, confidentialité)
**Contenu suggéré** :
- Logo simple
- Titre de la page
- Couleurs sobres : Bleu foncé + Crème

---

## 📍 Emplacement des fichiers

### Structure recommandée
```
/uploads/og-images/
├── og-home.jpg              (page d'accueil)
├── og-proposition.jpg       (formulaire proposition)
├── og-default.jpg           (image par défaut)
└── og-square.jpg            (version carrée pour Twitter)
```

### Mise à jour dans le code
Modifier dans `index.php` :
```php
<meta property="og:image" content="https://osons-saint-paul.fr/uploads/og-images/og-home.jpg">
```

---

## 🔄 Mise à jour et cache

### Après modification de l'image
1. **Vider le cache Facebook** :
   - Aller sur https://developers.facebook.com/tools/debug/
   - Entrer l'URL de la page
   - Cliquer sur "Scrape Again"

2. **Vider le cache Twitter** :
   - Aller sur https://cards-dev.twitter.com/validator
   - Entrer l'URL
   - Valider

3. **Vérifier le changement** :
   - Partager sur un réseau social privé (groupe test)
   - Vérifier l'aperçu

### Cache navigateur
- Ajouter un paramètre version : `og-home.jpg?v=2`
- Ou renommer le fichier : `og-home-v2.jpg`

---

## 📊 Suivi et analyse

### Métriques à surveiller
1. **Taux de clic (CTR)** sur les partages sociaux
2. **Engagement** (likes, partages, commentaires)
3. **Trafic** depuis les réseaux sociaux (Google Analytics)

### A/B Testing
- Tester différentes versions d'images
- Comparer les performances
- Garder la meilleure version

---

## ⚠️ Erreurs courantes à éviter

### ❌ À NE PAS FAIRE
1. Texte trop petit (< 40px)
2. Trop d'informations (surcharge visuelle)
3. Image de mauvaise qualité (pixellisée)
4. Poids trop élevé (> 1 MB)
5. Dimensions incorrectes
6. Logo illisible
7. Texte coupé sur les bords
8. Contraste insuffisant

### ✅ BONNES PRATIQUES
1. Simple et clair
2. Message principal bien visible
3. Couleurs de la charte
4. Poids optimisé
5. Tester sur tous les réseaux
6. Vérifier l'aperçu mobile
7. Cohérence avec l'identité visuelle

---

## 🆘 Support

**Besoin d'aide ?**
- Email : bonjour@osons-saint-paul.fr
- Exemples de bonnes images OG : https://www.opengraph.xyz

---

**Mis à jour** : 10 octobre 2025

