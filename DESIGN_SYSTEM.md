# 🎨 DESIGN SYSTEM - Osons Saint-Paul
## Système de design scalable et cohérent

---

## 🏗️ ARCHITECTURE

### Pyramide de complexité:
```
┌─────────────────────────────────────┐
│   NIVEAU 1: DESIGN TOKENS           │  Variables CSS
│   (Couleurs, Typographie, Spacing)  │
├─────────────────────────────────────┤
│   NIVEAU 2: UTILITAIRES             │  Classes atomiques
│   (.text-*, .mb-*, .flex-*)         │
├─────────────────────────────────────┤
│   NIVEAU 3: COMPOSANTS              │  Blocs réutilisables
│   (.btn, .card, .cta-box)           │
├─────────────────────────────────────┤
│   NIVEAU 4: LAYOUTS                 │  Structure de page
│   (.section, .container, .grid)     │
└─────────────────────────────────────┘
```

---

## 📐 NIVEAU 1: DESIGN TOKENS

### Typographie (Déjà bien faite ✅)
```css
--font-title       /* Titres principaux */
--font-subtitle    /* Sous-titres manuscrits */
--font-body        /* Texte courant */
--font-quote       /* Citations */
--font-button      /* Boutons */
```

### Couleurs (Déjà bien faites ✅)
```css
--coral, --yellow, --light-blue, --deep-green
--dark-blue, --blue-bottom, --cream, --white, --black
```

### Spacing (À AJOUTER ⚠️)
```css
--spacing-xs: 0.5rem;   /* 8px */
--spacing-sm: 1rem;     /* 16px */
--spacing-md: 1.5rem;   /* 24px */
--spacing-lg: 2rem;     /* 32px */
--spacing-xl: 3rem;     /* 48px */
--spacing-2xl: 4rem;    /* 64px */
```

---

## 🧩 NIVEAU 2: CLASSES UTILITAIRES

### Text Helpers
```css
.text-note         /* Remplace: cta-note, contact-note, newsletter-note, etc. */
.text-description  /* Remplace: cta-description, idees-description, etc. */
.text-center       /* text-align: center */
.text-italic       /* font-style: italic */
```

### Spacing Helpers
```css
.mb-1  /* margin-bottom: var(--spacing-sm) */
.mb-2  /* margin-bottom: var(--spacing-md) */
.mb-3  /* margin-bottom: var(--spacing-lg) */
.mt-1, .mt-2, .mt-3...
.p-1, .p-2, .p-3...
```

### Display Helpers
```css
.flex          /* display: flex */
.flex-center   /* justify-content: center; align-items: center */
.flex-column   /* flex-direction: column */
.flex-wrap     /* flex-wrap: wrap */
.gap-1, .gap-2, .gap-3...
```

---

## 🎁 NIVEAU 3: COMPOSANTS

### CTA Box (UN SEUL COMPOSANT)
```css
.cta-box {
  /* Remplace: cta-section, cta-content, programme-cta, avis-cta */
  text-align: center;
  margin: var(--spacing-xl) auto;
  padding: var(--spacing-lg);
  max-width: 800px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}

.cta-box__title { /* BEM naming */
  font-family: var(--font-script);
  font-size: 2rem;
  color: var(--coral);
  margin-bottom: var(--spacing-sm);
}
```

### Buttons (Déjà bons ✅)
```css
.btn, .btn-primary, .btn-secondary
```

### Cards (Déjà bonnes ✅)
```css
.proposition-card, .team-member
```

---

## 📦 NIVEAU 4: LAYOUTS

### Container (Déjà bon ✅)
```css
.container
```

### Section (Déjà bon ✅)
```css
section, .hero, .programme, .equipe, etc.
```

---

## 🔄 MIGRATION PLAN

### Étape 1: Ajouter les nouvelles classes (non-breaking)
```css
/* Nouvelles classes utilitaires */
.text-note { ... }
.text-description { ... }
.cta-box { ... }
.mb-1, .mb-2, .mb-3 { ... }
```

### Étape 2: Utiliser dans le HTML
```html
<!-- AVANT -->
<div class="programme-cta" style="margin-bottom: 2rem;">
  <a href="/proposez" class="btn btn-primary">💡 Faire une proposition</a>
  <p class="cta-note">Ce programme évolutif...</p>
</div>

<!-- APRÈS -->
<div class="cta-box">
  <a href="/proposez" class="btn btn-primary">💡 Faire une proposition</a>
  <p class="text-note">Ce programme évolutif...</p>
</div>
```

### Étape 3: Déprécier les anciennes classes
```css
/* @deprecated - Utiliser .text-note à la place */
.cta-note,
.contact-note,
.newsletter-note,
.mediatheque-note {
  /* Rediriger vers .text-note */
}
```

### Étape 4: Supprimer les anciennes classes (après validation)

---

## 📋 CHECKLIST DE REFACTORING

### Textes
- [ ] Créer `.text-note` universel
- [ ] Créer `.text-description` universel
- [ ] Créer `.text-title` universel
- [ ] Supprimer 5 classes redondantes

### CTA
- [ ] Créer `.cta-box` universel
- [ ] Créer `.cta-box__title` (BEM)
- [ ] Créer `.cta-box__description` (BEM)
- [ ] Supprimer 4 wrappers redondants

### Spacing
- [ ] Ajouter variables spacing
- [ ] Créer classes `.mb-*`, `.mt-*`, `.p-*`
- [ ] Remplacer tous les styles inline
- [ ] Supprimer ~15 styles inline du HTML

### Display
- [ ] Créer `.flex`, `.flex-center`, etc.
- [ ] Créer `.grid`, `.grid-2`, `.grid-3`, etc.
- [ ] Standardiser les layouts

---

## 🎯 RÉSULTATS ATTENDUS

### Avant
```css
.cta-note { font-family: var(--font-sans); font-size: 0.9rem; ... }
.contact-note { font-family: var(--font-sans); font-size: 0.9rem; ... }
.newsletter-note { font-family: var(--font-sans); font-size: 0.85rem; ... }
.mediatheque-note { font-family: var(--font-sans); font-size: 0.9rem; ... }
/* 4 classes × 8 lignes = 32 lignes */
```

### Après
```css
.text-note {
  font-family: var(--font-sans);
  font-size: 0.9rem;
  color: var(--deep-green);
  font-style: italic;
  opacity: 0.9;
  line-height: 1.5;
}
/* 1 classe × 7 lignes = 7 lignes */
/* Économie: 25 lignes (78%) */
```

---

## 🚀 SCALABILITÉ

### Ajouter un nouveau CTA:
```html
<!-- Avant: Créer nouvelle classe CSS + 15 lignes -->
<div class="new-section-cta">...</div>

<!-- Après: Réutiliser composant existant -->
<div class="cta-box">
  <h3 class="cta-box__title">Nouveau titre</h3>
  <p class="text-description">Description</p>
  <a href="#" class="btn btn-primary">Action</a>
  <p class="text-note">Note explicative</p>
</div>
```

**Temps gagné:** 5 minutes → 30 secondes  
**Code ajouté:** 15 lignes → 0 ligne

---

## 📚 DOCUMENTATION

### Pour les développeurs:
1. Consulter `DESIGN_SYSTEM.md` (ce fichier)
2. Utiliser les classes utilitaires existantes
3. NE PAS créer de nouvelles classes sans validation
4. Privilégier la composition de classes

### Pour les designers:
1. Tokens de design sont dans `:root`
2. Modifier une couleur = modifier une variable
3. Effet immédiat sur tout le site

---

## ✅ VALIDATION

### Tests à effectuer:
- [ ] Toutes les CTA s'affichent correctement
- [ ] Aucune régression visuelle
- [ ] Responsive fonctionne
- [ ] Performance améliorée (CSS plus petit)

---

**Next Steps:** Voir `REFACTORING_GUIDE.md` pour l'implémentation

