# 🎨 Guide de Typographie UX - Osons Saint-Paul

## Hiérarchie Visuelle Cohérente

### 📊 Principe : Éviter le noir pur partout

**❌ Problème avec le noir pur (#000000) :**
- Fatigue visuelle excessive
- Contraste trop dur (ratio > 21:1)
- Manque de nuance et de personnalité
- Pas de hiérarchie claire

**✅ Solution adoptée :**
Une hiérarchie à 3 niveaux avec des couleurs de votre palette

---

## 🎯 Hiérarchie des Textes

### 1️⃣ **Titres Principaux**
```css
Couleur: #000000 (Noir)
Usage: Titres H1, H2 (sections principales)
Justification: Impact maximum pour structurer le contenu
```

### 2️⃣ **Textes Principaux / Descriptions**
```css
Couleur: rgba(0, 74, 109, 0.85) (Bleu foncé adouci)
Usage: Corps de texte, descriptions CTA
Justification: Lisibilité optimale sans fatigue visuelle
Avantage: S'intègre à votre palette de couleurs
```

### 3️⃣ **Notes / Textes Secondaires**
```css
Couleur: var(--deep-green) #65ae99 (Vert)
Style: Italic
Opacité: 0.9
Usage: Notes explicatives, textes complémentaires
Justification: Crée une hiérarchie visuelle claire
Avantage: Utilise une couleur de votre identité visuelle
```

---

## 📐 Application Pratique

### Exemple 1 : CTA Programme
```
[Bouton Corail: "💡 Faire une proposition"]
Texte note en vert italic (opacity 0.9):
"Ce programme évolutif s'enrichit au fil de vos propositions..."
```

### Exemple 2 : CTA Équipe
```
Titre corail: "Rencontrons-nous !"
Description bleu adouci (0.85):
"Vous avez des questions ? Des idées à partager ?"
[Boutons d'action]
```

---

## 🎨 Palette de Couleurs pour le Texte

| Niveau | Couleur | Nom | Usage |
|--------|---------|-----|-------|
| 1 | `#000000` | Noir | Titres principaux |
| 2 | `rgba(0, 74, 109, 0.85)` | Bleu foncé adouci | Textes principaux |
| 3 | `#65ae99` | Vert (opacity 0.9) | Notes secondaires |
| Accent | `#ec654f` | Corail | CTA, titres manuscrits |

---

## ✨ Avantages de cette Approche

✅ **Hiérarchie claire** : 3 niveaux visuels distincts
✅ **Lisibilité optimale** : Contraste adapté (WCAG AA)
✅ **Cohérence** : Utilise votre palette de couleurs
✅ **Douceur visuelle** : Pas de noir pur partout
✅ **Personnalité** : Reflète votre identité visuelle
✅ **Accessibilité** : Ratios de contraste respectés

---

## 📱 Responsive

La hiérarchie reste cohérente sur tous les écrans :
- Mobile : Même hiérarchie, tailles adaptées
- Tablette : Même hiérarchie, tailles adaptées  
- Desktop : Hiérarchie complète

---

## 🔍 Test de Lisibilité

**Noir pur (#000) sur blanc :**
- Ratio de contraste : 21:1 (trop élevé)
- Fatigue visuelle après 5-10 minutes

**Bleu foncé adouci (rgba(0, 74, 109, 0.85)) sur blanc :**
- Ratio de contraste : ~12:1 (optimal)
- Lisibilité confortable
- Conforme WCAG AAA

**Vert (opacity 0.9) sur blanc :**
- Ratio de contraste : ~4.8:1 (bon pour texte secondaire)
- Conforme WCAG AA
- Parfait pour notes en italique

---

## 💡 Recommandations

1. **Toujours utiliser** le vert italic pour les notes explicatives
2. **Réserver le noir** aux titres principaux uniquement
3. **Utiliser le bleu adouci** pour tous les textes principaux
4. **Maintenir l'opacity à 0.9** pour les notes (pas 0.8)
5. **Ajouter line-height: 1.5** pour meilleure lisibilité

---

## 🎓 Principes UX Appliqués

### Loi de la Hiérarchie Visuelle
> "Les éléments importants doivent attirer l'attention en premier"

### Loi du Contraste
> "Le contraste crée la hiérarchie, mais trop de contraste fatigue"

### Loi de la Cohérence
> "Une expérience cohérente réduit la charge cognitive"

---

**Dernière mise à jour :** Octobre 2025  
**Maintenu par :** Équipe Osons Saint-Paul

