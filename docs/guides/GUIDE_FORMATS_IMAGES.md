# 📐 GUIDE DES FORMATS D'IMAGES

Guide de référence pour le crop manuel et l'optimisation

---

## 🎯 PRESETS D'OPTIMISATION

### 🖼️ Hero (Image principale)
```
Format : 16:9 (Paysage horizontal)
Dimensions optimales : 1920 x 1080
Qualité WebP : 85%
Poids final typique : 300-500 KB

📌 Utilisation : Fond de la section hero
```

**Exemple de crop :**
```
┌─────────────────────────────────┐
│                                 │ ← Ratio 16:9
│        Image hero               │   (Format cinéma)
│                                 │
└─────────────────────────────────┘
Largeur : Hauteur = 16 : 9
```

---

### 🎨 Citation (Bannière)
```
Format : Panoramique large
Dimensions optimales : 1920 x 800
Qualité WebP : 85%
Poids final typique : 200-400 KB

📌 Utilisation : Citations de transition entre sections
```

**Exemple de crop :**
```
┌─────────────────────────────────────────┐
│     Citation panoramique                │ ← Format très large
└─────────────────────────────────────────┘
```

---

### 👤 Member (Photo d'équipe)
```
Format : 3:4 (Portrait vertical)
Dimensions optimales : 600 x 800
Qualité WebP : 90% (haute qualité)
Poids final typique : 80-150 KB

📌 Utilisation : Photos des membres de l'équipe
```

**Exemple de crop :**
```
┌──────────┐
│          │
│          │
│  Photo   │ ← Ratio 3:4
│ Portrait │   (Format photo ID)
│          │
│          │
└──────────┘
Largeur : Hauteur = 3 : 4
```

---

### 📄 Standard
```
Format : 4:3 ou flexible
Dimensions optimales : 1200 x 1200
Qualité WebP : 85%
Poids final typique : 150-300 KB

📌 Utilisation : Images de contenu général
```

---

## 🔢 CALCUL DES RATIOS

### Format Portrait 3:4 (Membres)
```
Si largeur = 300px → hauteur = 400px
Si largeur = 450px → hauteur = 600px
Si largeur = 600px → hauteur = 800px ✅ (optimal)
Si largeur = 750px → hauteur = 1000px

Formule : hauteur = largeur × (4/3) = largeur × 1.333
```

### Format Paysage 16:9 (Hero)
```
Si largeur = 1280px → hauteur = 720px
Si largeur = 1600px → hauteur = 900px
Si largeur = 1920px → hauteur = 1080px ✅ (optimal)

Formule : hauteur = largeur × (9/16) = largeur × 0.5625
```

---

## 🎨 GUIDE DE CROP MANUEL (Cropper.js)

### Pour une image hero :
```javascript
cropper = new Cropper(image, {
    aspectRatio: 16 / 9,  // Format 16:9
    viewMode: 2,
    guides: true
});
```

### Pour une photo membre :
```javascript
cropper = new Cropper(image, {
    aspectRatio: 3 / 4,   // Format portrait 3:4
    viewMode: 2,
    guides: true
});
```

### Pour une citation :
```javascript
cropper = new Cropper(image, {
    aspectRatio: 2.4,     // Format panoramique
    viewMode: 2,
    guides: true
});
```

---

## 📊 OPTIMISATION AUTOMATIQUE

### Ce que fait le système APRÈS le crop :

```
Image croppée par l'utilisateur (ex: 2400x1800, 2.5MB)
        ↓
    RÉDUCTION
Si trop grande → Réduit à max 600x800 (garde le ratio)
Si OK → Conserve les dimensions
        ↓
    CONVERSION WebP
Format lourd (JPEG/PNG) → WebP optimisé
        ↓
    COMPRESSION
Qualité 90% (excellent compromis)
        ↓
Image finale : 600x800, 120KB ⚡ (-95%)
```

---

## 🎯 RECOMMANDATIONS

### Lors du crop manuel :

#### Pour les photos d'équipe :
✅ **Cadrer le visage** au centre  
✅ **Format portrait** 3:4  
✅ **Espace au-dessus** de la tête  
✅ **Fond** simple si possible  

#### Pour les images hero :
✅ **Format paysage** 16:9  
✅ **Point focal** au centre ou tiers droit  
✅ **Espace pour le texte** à prévoir  
✅ **Contraste** suffisant avec overlay  

#### Pour les citations :
✅ **Format panoramique** large  
✅ **Fond** pas trop chargé (lisibilité du texte)  
✅ **Contraste** avec l'overlay sombre  

---

## 📐 AIDE-MÉMOIRE RAPIDE

| Type | Ratio | Format | Largeur × Hauteur |
|------|-------|--------|-------------------|
| Hero | 16:9 | Paysage | 1920 × 1080 |
| Citation | ~2.4:1 | Panoramique | 1920 × 800 |
| **Membre** | **3:4** | **Portrait** | **600 × 800** |
| Standard | 1:1 ou 4:3 | Variable | 1200 × 1200 |

---

## 💡 ASTUCE CROPPER.JS

### Afficher le ratio pendant le crop :
```javascript
cropper = new Cropper(image, {
    aspectRatio: 3 / 4,
    preview: '.preview',  // Prévisualisation temps réel
    crop(event) {
        // Afficher les dimensions pendant le crop
        console.log('Largeur:', event.detail.width);
        console.log('Hauteur:', event.detail.height);
    }
});
```

---

## ✅ VALIDATION

### Après le crop, vérifier :
- [ ] Ratio correct (3:4 pour membres)
- [ ] Cadrage centré sur le sujet
- [ ] Qualité visuelle acceptable
- [ ] Prévisualisation satisfaisante

### Après l'optimisation système :
- [ ] Fichier créé et > 0 octets
- [ ] Format WebP (ou JPEG en fallback)
- [ ] Poids < 200 KB
- [ ] Dimensions respectent le max

---

**📸 Format membre mis à jour : 3:4 (600×800) - Portrait vertical**

