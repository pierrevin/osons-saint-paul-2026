# Guide de tracking UTM - Osons Saint-Paul 2026

**Date** : 10 octobre 2025  
**Version** : 1.0

---

## 📊 Qu'est-ce que le tracking UTM ?

Les **paramètres UTM** (Urchin Tracking Module) sont des balises ajoutées à la fin d'une URL pour identifier précisément la source du trafic dans Google Analytics.

### Exemple d'URL avec UTM
```
https://osons-saint-paul.fr/forms/proposition-citoyenne.php?utm_source=qrcode&utm_medium=print&utm_campaign=cartes_postales_2026
```

Cette URL permet de savoir que le visiteur vient :
- **Source** : d'un QR code
- **Médium** : sur un support imprimé
- **Campagne** : cartes postales 2026

---

## 🎯 Pourquoi utiliser les UTM ?

### Avantages
1. **Mesurer le ROI** de chaque action (affiches, cartes, posts sociaux)
2. **Comparer les performances** des différents supports
3. **Optimiser les budgets** en investissant dans ce qui fonctionne
4. **Prouver l'impact** de vos actions terrain
5. **Différencier** le trafic admin du trafic réel

### Cas d'usage concrets
- 📮 Cartes postales distribuées : Combien de propositions citoyennes reçues ?
- 📰 Affiches publiques : Quelle localisation génère le plus de visites ?
- 📧 Newsletters : Quel sujet intéresse le plus ?
- 📱 Posts Facebook/Instagram : Quel réseau convertit le mieux ?

---

## 🏗️ Structure des paramètres UTM

### Les 5 paramètres (3 obligatoires, 2 optionnels)

#### 1️⃣ utm_source (OBLIGATOIRE)
**Définition** : D'où vient le trafic ?

**Exemples** :
- `qrcode` : QR code
- `facebook` : Facebook
- `instagram` : Instagram
- `email` : Newsletter
- `admin` : Interface d'administration

**Bonnes pratiques** :
- Toujours en minuscules
- Pas d'espaces (utiliser des underscores : `_`)
- Rester cohérent

#### 2️⃣ utm_medium (OBLIGATOIRE)
**Définition** : Quel type de support ?

**Exemples** :
- `print` : Support imprimé (cartes, affiches, flyers)
- `social` : Réseaux sociaux
- `email` : Email
- `internal` : Liens internes (admin)
- `referral` : Site référent

**Bonnes pratiques** :
- Regrouper les supports similaires
- Rester simple et général

#### 3️⃣ utm_campaign (OBLIGATOIRE)
**Définition** : Nom de la campagne

**Exemples** :
- `cartes_postales_2026` : Cartes postales
- `affiches_publiques_2026` : Affiches
- `newsletter_janvier_2026` : Newsletter de janvier
- `facebook_ads_2026` : Publicités Facebook

**Bonnes pratiques** :
- Nom descriptif et unique
- Inclure l'année si pertinent
- Utiliser des underscores

#### 4️⃣ utm_content (OPTIONNEL)
**Définition** : Pour différencier des variantes

**Exemples** :
- `version_a` / `version_b` : Tests A/B
- `qrcode_recto` / `qrcode_verso` : Position du QR code
- `place_village` / `mairie` : Localisation de l'affiche
- `cta_rouge` / `cta_bleu` : Couleur du bouton

**Quand l'utiliser** :
- Tests A/B (deux versions d'une même affiche)
- Différencier plusieurs emplacements
- Comparer plusieurs variantes d'un même support

#### 5️⃣ utm_term (OPTIONNEL)
**Définition** : Mots-clés pour les campagnes payantes

**Exemples** :
- `municipales`
- `liste citoyenne`
- `saint paul sur save`

**Quand l'utiliser** :
- Campagnes Google Ads
- Campagnes Facebook Ads avec ciblage par mots-clés
- Rarement nécessaire pour une campagne locale

---

## 📝 Nomenclature recommandée

### Convention de nommage

#### Format général
```
utm_source : [plateforme/origine]
utm_medium : [type_support]
utm_campaign : [nom_campagne_ANNEE]
utm_content : [variante] (optionnel)
```

#### Exemples complets

**1. Cartes postales distribuées (proposition)**
```
utm_source=qrcode
utm_medium=print
utm_campaign=cartes_postales_2026
utm_content=proposition
```

**2. Affiche place du village (accueil)**
```
utm_source=qrcode
utm_medium=print
utm_campaign=affiches_publiques_2026
utm_content=place_village
```

**3. Flyers programme (marché)**
```
utm_source=qrcode
utm_medium=print
utm_campaign=flyers_programme_2026
utm_content=marche_dimanche
```

**4. Newsletter janvier (programme)**
```
utm_source=email
utm_medium=email
utm_campaign=newsletter_janvier_2026
utm_content=section_programme
```

**5. Post Facebook équipe**
```
utm_source=facebook
utm_medium=social
utm_campaign=facebook_equipe_2026
utm_content=photo_groupe
```

---

## 🛠️ Utiliser le générateur UTM

### Accès
**URL** : `/admin/pages/generateur-utm.php`  
**Menu admin** : Générateur d'URLs UTM

### Étapes
1. **Sélectionner la page de destination** : Où diriger les visiteurs ?
2. **Choisir la source** : qrcode, email, facebook, etc.
3. **Choisir le médium** : print, social, email, etc.
4. **Nommer la campagne** : Nom descriptif unique
5. **Ajouter le contenu** (optionnel) : Variante si nécessaire
6. **Cliquer sur "Générer l'URL"**
7. **Copier l'URL** ou **générer le QR code**

### Fonctionnalités
- ✅ Génération d'URL trackée
- ✅ Copie en un clic
- ✅ Génération de QR code automatique
- ✅ Téléchargement du QR code (PNG haute résolution)
- ✅ Exemples d'utilisation

---

## 📱 QR Codes : Bonnes pratiques

### Spécifications techniques
- **Format** : PNG ou SVG
- **Résolution** : 300 DPI minimum pour l'impression
- **Taille minimale** : 3 x 3 cm
- **Marges** : 1 cm autour du QR code (zone de tranquillité)
- **Contraste** : Noir sur blanc (idéal) ou couleurs à haut contraste

### Design et placement
1. **Visible** : Ne pas cacher le QR code
2. **Accessible** : À hauteur des yeux
3. **Call-to-Action** : Ajouter un texte explicatif
   - ✅ "📱 Scannez pour proposer votre idée"
   - ✅ "👉 Découvrez notre programme"
   - ❌ "Scannez" (trop vague)

4. **URL courte en complément** : Pour ceux qui préfèrent taper
   - Exemple : `osons-saint-paul.fr/proposez`

### Test avant impression
1. **Tester le QR code** avec plusieurs téléphones
2. **Vérifier l'URL** dans le navigateur
3. **Imprimer un test** à la taille réelle
4. **Scanner le test** dans différentes conditions d'éclairage

---

## 📊 Analyser les données dans Google Analytics

### Google Analytics 4 (GA4)

#### 1. Vue d'ensemble du trafic
**Rapports → Acquisition → Acquisition de trafic**

Filtrer par :
- **Première source de l'utilisateur** : Voir les sources
- **Premier support de l'utilisateur** : Voir les médiums
- **Première campagne** : Voir les campagnes

#### 2. Créer des segments personnalisés

**Segment "Trafic QR Code"** :
```
Première source de l'utilisateur = qrcode
```

**Segment "Trafic Admin"** :
```
Première source de l'utilisateur = admin
```

**Segment "Trafic Cartes Postales"** :
```
Première source de l'utilisateur = qrcode
ET
Première campagne = cartes_postales_2026
```

#### 3. Rapports personnalisés

**ROI par support** :
- Dimension 1 : Premier support de l'utilisateur
- Dimension 2 : Première campagne
- Métriques : Utilisateurs, Sessions, Conversions (propositions)

**Performance des QR codes** :
- Dimension 1 : Première campagne
- Filtre : Première source = qrcode
- Métriques : Utilisateurs, Durée moyenne, Taux de rebond

### Exemples de questions à poser

1. **Combien de visites depuis les cartes postales ?**
   - Filtre : utm_source=qrcode, utm_campaign=cartes_postales_2026

2. **Quel support génère le plus de propositions citoyennes ?**
   - Comparer conversions par utm_medium

3. **Quelle localisation d'affiche est la plus efficace ?**
   - Comparer par utm_content (place_village, mairie, etc.)

4. **Le trafic admin pollue-t-il mes statistiques ?**
   - Exclure utm_source=admin des rapports

---

## 📋 Checklist d'utilisation

### Avant chaque campagne
- [ ] Définir les objectifs (visites, propositions, inscriptions)
- [ ] Créer les URLs UTM dans le générateur
- [ ] Générer les QR codes
- [ ] Tester les QR codes sur plusieurs appareils
- [ ] Documenter les URLs créées (tableau récapitulatif)

### Pendant la campagne
- [ ] Vérifier le trafic en temps réel (GA4 → Temps réel)
- [ ] S'assurer que les UTM remontent correctement
- [ ] Noter les dates de début/fin de chaque action

### Après la campagne
- [ ] Analyser les résultats dans GA4
- [ ] Comparer les performances des différents supports
- [ ] Calculer le ROI (coût vs résultats)
- [ ] Documenter les apprentissages
- [ ] Ajuster la stratégie pour la prochaine campagne

---

## 📊 Tableau récapitulatif des URLs

### Cartes postales

| Support | Destination | URL complète | QR Code | Date |
|---------|-------------|--------------|---------|------|
| Cartes postales | Proposition | `https://osons-saint-paul.fr/forms/proposition-citoyenne.php?utm_source=qrcode&utm_medium=print&utm_campaign=cartes_postales_2026` | ✅ | Oct 2025 |

### Affiches publiques

| Localisation | Destination | URL complète | QR Code | Date |
|--------------|-------------|--------------|---------|------|
| Place du village | Accueil | `https://osons-saint-paul.fr/?utm_source=qrcode&utm_medium=print&utm_campaign=affiches_publiques_2026&utm_content=place_village` | ✅ | Oct 2025 |
| Mairie | Programme | `https://osons-saint-paul.fr/#programme?utm_source=qrcode&utm_medium=print&utm_campaign=affiches_publiques_2026&utm_content=mairie` | ✅ | Oct 2025 |

### Flyers

| Distribution | Destination | URL complète | QR Code | Date |
|-------------|-------------|--------------|---------|------|
| Marché dimanche | Programme | `https://osons-saint-paul.fr/#programme?utm_source=qrcode&utm_medium=print&utm_campaign=flyers_programme_2026&utm_content=marche` | ✅ | Oct 2025 |

### Réseaux sociaux

| Plateforme | Campagne | URL complète | Date |
|-----------|----------|--------------|------|
| Facebook | Post équipe | `https://osons-saint-paul.fr/#equipe?utm_source=facebook&utm_medium=social&utm_campaign=facebook_equipe_2026` | Oct 2025 |
| Instagram | Stories programme | `https://osons-saint-paul.fr/#programme?utm_source=instagram&utm_medium=social&utm_campaign=instagram_stories_2026` | Oct 2025 |

---

## ⚠️ Erreurs courantes à éviter

### ❌ À NE PAS FAIRE

1. **Espaces dans les paramètres**
   ```
   ❌ utm_campaign=cartes postales 2026
   ✅ utm_campaign=cartes_postales_2026
   ```

2. **Majuscules et minuscules mélangées**
   ```
   ❌ utm_source=QRCode
   ✅ utm_source=qrcode
   ```

3. **Caractères spéciaux**
   ```
   ❌ utm_campaign=cartes-postales-2026!
   ✅ utm_campaign=cartes_postales_2026
   ```

4. **Paramètres trop vagues**
   ```
   ❌ utm_campaign=campagne1
   ✅ utm_campaign=cartes_postales_2026
   ```

5. **Oublier les UTM sur certains supports**
   - Toujours utiliser les UTM, même pour un test

6. **Utiliser des URLs UTM dans le contenu du site**
   - Les UTM sont UNIQUEMENT pour les liens EXTERNES vers le site

### ✅ BONNES PRATIQUES

1. **Cohérence** : Toujours la même structure
2. **Lisibilité** : Noms explicites
3. **Simplicité** : Pas de surcharge de paramètres
4. **Documentation** : Tenir un tableau récapitulatif
5. **Tests** : Toujours tester avant distribution massive

---

## 🎓 Ressources et outils

### Outils internes
- **Générateur d'URLs UTM** : `/admin/pages/generateur-utm.php`
- **Google Analytics 4** : https://analytics.google.com

### Ressources externes
- **Google Campaign URL Builder** : https://ga-dev-tools.google/campaign-url-builder/
- **UTM.io** : https://utm.io (gestion d'URLs)
- **Bitly** : https://bitly.com (raccourcir les URLs)

### Documentation Google
- **Guide officiel UTM** : https://support.google.com/analytics/answer/1033863
- **Guide GA4 Acquisition** : https://support.google.com/analytics/topic/11151952

---

## 🆘 Support et questions

**Besoin d'aide ?**
- Email : bonjour@osons-saint-paul.fr
- Documentation complète : `/DOCUMENTATION.md`

**Questions fréquentes**

**Q : Dois-je utiliser les UTM pour tous les liens ?**  
R : Non ! Uniquement pour les liens EXTERNES qui pointent VERS votre site (QR codes, emails, posts sociaux). Jamais pour les liens internes du site.

**Q : Mes UTM n'apparaissent pas dans Google Analytics ?**  
R : Vérifiez que Google Analytics est bien configuré et que vous avez attendu quelques heures (délai de traitement).

**Q : Puis-je changer un paramètre UTM après impression ?**  
R : Non, une fois imprimé, le QR code est figé. C'est pourquoi il faut bien tester avant !

**Q : Combien de temps garder les mêmes UTM ?**  
R : Tant que la campagne est active. Changez les paramètres pour chaque nouvelle campagne.

---

**Mis à jour** : 10 octobre 2025  
**Version** : 1.0

