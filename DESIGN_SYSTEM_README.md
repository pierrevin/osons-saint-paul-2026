# 🎨 Design System - Guide Rapide

## 🚀 Quick Start

Vous voulez **ajouter un CTA** ? Voici comment faire maintenant :

### ✨ AVANT (Ancien système - ❌ Ne plus utiliser)
```html
<div class="programme-cta" style="margin-bottom: 2rem;">
    <a href="#" class="btn btn-primary">Action</a>
    <p class="cta-note">Note explicative</p>
</div>
```
❌ Problèmes : Styles inline, classe spécifique non réutilisable

### ✅ MAINTENANT (Nouveau système - ✅ À utiliser)
```html
<div class="cta-box">
    <a href="#" class="btn btn-primary">Action</a>
    <p class="text-note">Note explicative</p>
</div>
```
✅ Avantages : Zéro style inline, composant universel, scalable

---

## 📦 COMPOSANTS DISPONIBLES

### 1. CTA Box
```html
<!-- CTA Standard -->
<div class="cta-box">
    <h3 class="cta-box__title">Titre accrocheur</h3>
    <p class="text-description">Description engageante</p>
    <div class="cta-box__buttons">
        <a href="#" class="btn btn-primary">Action principale</a>
        <a href="#" class="btn btn-secondary">Action secondaire</a>
    </div>
    <p class="text-note">Note en petit</p>
</div>

<!-- CTA Compact (pour bouton + note seulement) -->
<div class="cta-box cta-box--compact">
    <a href="#" class="btn btn-primary">💡 Action rapide</a>
    <p class="text-note">Courte explication</p>
</div>
```

### 2. Textes
```html
<!-- Note explicative (vert italique) -->
<p class="text-note">Information complémentaire en petit</p>

<!-- Description (bleu adouci) -->
<p class="text-description">Texte principal de description</p>
```

---

## 🛠️ UTILITAIRES

### Spacing
```html
<div class="mb-1">  <!-- margin-bottom: 1rem -->
<div class="mb-2">  <!-- margin-bottom: 1.5rem -->
<div class="mb-3">  <!-- margin-bottom: 2rem -->
<div class="mt-2">  <!-- margin-top: 1.5rem -->
<div class="my-3">  <!-- margin vertical: 2rem -->
<div class="p-2">   <!-- padding: 1.5rem -->
```

### Display
```html
<div class="flex">              <!-- display: flex -->
<div class="flex-center">       <!-- flex + centré -->
<div class="flex-column">       <!-- flex vertical -->
<div class="flex gap-2">        <!-- flex avec gap 1.5rem -->
```

### Texte
```html
<p class="text-center">   <!-- text-align: center -->
<p class="text-italic">   <!-- font-style: italic -->
<span class="text-bold">  <!-- font-weight: 600 -->
<div class="hidden">      <!-- display: none -->
```

---

## 📐 SPACING SCALE

```
--spacing-xs:  0.5rem  (8px)   → Classes: .p-0, .mb-0
--spacing-sm:  1rem    (16px)  → Classes: .p-1, .mb-1, .mt-1
--spacing-md:  1.5rem  (24px)  → Classes: .p-2, .mb-2, .mt-2
--spacing-lg:  2rem    (32px)  → Classes: .p-3, .mb-3, .mt-3
--spacing-xl:  3rem    (48px)  → Classes: .p-4, .mb-4, .mt-4
--spacing-2xl: 4rem    (64px)  → Classes: .mb-5, .mt-5
```

---

## 🎯 RÈGLES D'OR

### ✅ À FAIRE
1. **Utiliser les composants** : `.cta-box`, `.text-note`, `.text-description`
2. **Utiliser les utilitaires** : `.mb-2`, `.flex`, `.text-center`
3. **Composer les classes** : `<div class="cta-box mb-3">`

### ❌ À ÉVITER
1. **Styles inline** : ~~`style="margin-bottom: 2rem"`~~
2. **Nouvelles classes spécifiques** : ~~`.my-special-cta-note`~~
3. **Duplication** : ~~Copier-coller le CSS d'un composant~~

---

## 🔄 MIGRATION RAPIDE

### Remplacer les anciennes classes:

| Ancien | Nouveau | Action |
|--------|---------|--------|
| `.cta-note` | `.text-note` | Search & Replace |
| `.newsletter-note` | `.text-note` | Search & Replace |
| `.cta-description` | `.text-description` | Search & Replace |
| `.programme-cta` | `.cta-box.cta-box--compact` | Search & Replace |
| `style="display:none"` | `class="hidden"` | Search & Replace |

---

## 💡 EXEMPLES RÉELS

### Exemple 1: CTA avec bouton
```html
<div class="cta-box cta-box--compact">
    <a href="/proposez" class="btn btn-primary">💡 Faire une proposition</a>
    <p class="text-note">Ce programme s'enrichit de vos idées</p>
</div>
```

### Exemple 2: CTA complet
```html
<div class="cta-box">
    <h3 class="cta-box__title">Rencontrons-nous !</h3>
    <p class="text-description">
        Vous avez des questions ? Parlons-en autour d'un café.
    </p>
    <div class="cta-box__buttons">
        <a href="#contact" class="btn btn-primary">Nous contacter</a>
        <a href="#events" class="btn btn-secondary">Voir les événements</a>
    </div>
    <p class="text-note">Tous nos rendez-vous sont ouverts à tous</p>
</div>
```

### Exemple 3: Layout avec utilitaires
```html
<div class="flex flex-column gap-2 mb-4">
    <h2 class="text-center">Titre centré</h2>
    <p class="text-description">Description du contenu</p>
    <div class="flex-center gap-1">
        <button class="btn btn-primary">Action 1</button>
        <button class="btn btn-secondary">Action 2</button>
    </div>
</div>
```

---

## 🎨 COULEURS

```css
--coral:       #ec654f  /* CTA, accents */
--deep-green:  #65ae99  /* Notes, infos secondaires */
--dark-blue:   #004a6d  /* Textes principaux */
--yellow:      #fcc549  /* Highlights */
--cream:       #FAF5EE  /* Backgrounds */
```

---

## 📚 DOCUMENTATION COMPLÈTE

- **`AUDIT_CSS_COMPLET.md`** - Analyse des problèmes
- **`DESIGN_SYSTEM.md`** - Architecture complète
- **`REFACTORING_GUIDE.md`** - Guide de migration détaillé
- **`MIGRATION_DASHBOARD.md`** - Suivi de progression

---

## ❓ FAQ

**Q: Puis-je encore utiliser les anciennes classes ?**  
R: Oui, elles fonctionnent encore (rétrocompatibilité), mais migrez vers les nouvelles dès que possible.

**Q: Comment ajouter un nouveau CTA ?**  
R: Utilisez `.cta-box` (jamais de nouvelle classe spécifique).

**Q: Puis-je combiner les classes utilitaires ?**  
R: Oui ! C'est le but : `<div class="cta-box mb-3 text-center">`

**Q: Que faire si j'ai besoin d'un style unique ?**  
R: D'abord vérifier si les utilitaires peuvent le faire. Sinon, discuter avec l'équipe avant d'ajouter du CSS.

---

**Créé le :** Octobre 2025  
**Version :** 1.0  
**Statut :** ✅ Prêt à l'emploi

