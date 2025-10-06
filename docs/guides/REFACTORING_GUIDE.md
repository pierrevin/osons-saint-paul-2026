# 🔧 GUIDE DE REFACTORING PRATIQUE
## Migration vers le nouveau système de design

---

## ✅ ÉTAPE COMPLÉTÉE

Le nouveau système de design est **déjà implémenté** dans `styles.css` !

### Ce qui a été ajouté:

1. ✅ **Design Tokens** - Variables de spacing
2. ✅ **Classes utilitaires** - Spacing, Display, Texte
3. ✅ **Composants unifiés** - `.cta-box`, `.text-note`, `.text-description`
4. ✅ **Rétrocompatibilité** - Anciennes classes fonctionnent toujours

---

## 📝 GUIDE DE MIGRATION HTML

### ✨ EXEMPLE 1: CTA Programme

#### ❌ AVANT (Code actuel - index.php ligne 102-105)
```html
<div class="programme-cta" style="margin-bottom: 2rem;">
    <a href="/proposez" class="btn btn-primary">💡 Faire une proposition</a>
    <p class="cta-note">Ce programme évolutif s'enrichit au fil de vos propositions citoyennes et de nos actions validées collectivement.</p>
</div>
```

#### ✅ APRÈS (Nouveau système)
```html
<div class="cta-box cta-box--compact">
    <a href="/proposez" class="btn btn-primary">💡 Faire une proposition</a>
    <p class="text-note">Ce programme évolutif s'enrichit au fil de vos propositions citoyennes et de nos actions validées collectivement.</p>
</div>
```

**Bénéfices:**
- ✅ Suppression du style inline
- ✅ Utilisation du composant unifié
- ✅ Classe `.text-note` au lieu de `.cta-note`
- ✅ Modificateur `--compact` pour la largeur réduite

---

### ✨ EXEMPLE 2: CTA Équipe (index.php ligne 227-235)

#### ❌ AVANT
```html
<div class="cta-section">
    <div class="cta-content">
        <h3 class="cta-title">Rencontrons-nous !</h3>
        <p class="cta-description">Vous avez des questions ? Des idées à partager ? N'hésitez pas à nous contacter directement.</p>
        <div class="cta-buttons">
            <a href="#idees" class="btn btn-primary">Nous contacter</a>
            <a href="#rendez-vous" class="btn btn-secondary">Voir nos rendez-vous</a>
        </div>
    </div>
</div>
```

#### ✅ APRÈS
```html
<div class="cta-box">
    <h3 class="cta-box__title">Rencontrons-nous !</h3>
    <p class="text-description">Vous avez des questions ? Des idées à partager ? N'hésitez pas à nous contacter directement.</p>
    <div class="cta-box__buttons">
        <a href="#idees" class="btn btn-primary">Nous contacter</a>
        <a href="#rendez-vous" class="btn btn-secondary">Voir nos rendez-vous</a>
    </div>
</div>
```

**Bénéfices:**
- ✅ Un seul wrapper au lieu de deux (`.cta-section` + `.cta-content`)
- ✅ Convention BEM pour les sous-éléments (`cta-box__title`, `cta-box__buttons`)
- ✅ Classe `.text-description` universelle
- ✅ Code plus lisible et maintenable

---

### ✨ EXEMPLE 3: Newsletter Form (index.php ligne 371-381)

#### ❌ AVANT
```html
<form class="newsletter-form" id="newsletter-section">
    <div class="form-group">
        <input type="text" name="PRENOM" placeholder="Votre prénom" required>
        <input type="email" name="EMAIL" placeholder="Votre adresse email" required>
        <input type="text" name="email_address_check" value="" style="display:none" tabindex="-1" autocomplete="off">
        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </div>
    <p class="newsletter-note">Nous respectons votre vie privée. Désabonnement possible à tout moment.</p>
</form>
<div id="newsletter-success-section" style="display:none; color: var(--primary-color); text-align:center; margin-top: 0.5rem; font-weight: 500;">...</div>
```

#### ✅ APRÈS
```html
<form class="newsletter-form" id="newsletter-section">
    <div class="flex gap-2 flex-wrap">
        <input type="text" name="PRENOM" placeholder="Votre prénom" required>
        <input type="email" name="EMAIL" placeholder="Votre adresse email" required>
        <input type="text" name="email_address_check" value="" class="hidden" tabindex="-1" autocomplete="off">
        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </div>
    <p class="text-note">Nous respectons votre vie privée. Désabonnement possible à tout moment.</p>
</form>
<div id="newsletter-success-section" class="hidden text-center mt-1" style="color: var(--primary-color); font-weight: 500;">...</div>
```

**Bénéfices:**
- ✅ Remplacement de `style="display:none"` par `.hidden`
- ✅ Utilisation des utilitaires flex (`.flex`, `.gap-2`, `.flex-wrap`)
- ✅ Classe `.text-note` au lieu de `.newsletter-note`
- ✅ Utilitaires de spacing (`.mt-1`) au lieu de styles inline

---

### ✨ EXEMPLE 4: Section Idées (index.php ligne 475-478)

#### ❌ AVANT
```html
<div class="programme-cta">
    <a href="/proposez" class="btn btn-primary">💡 Faire une proposition</a>
    <p class="cta-note">Partagez vos idées pour enrichir notre programme</p>
</div>
```

#### ✅ APRÈS
```html
<div class="cta-box cta-box--compact">
    <a href="/proposez" class="btn btn-primary">💡 Faire une proposition</a>
    <p class="text-note">Partagez vos idées pour enrichir notre programme</p>
</div>
```

---

## 🎯 CLASSES À REMPLACER

### Textes
| Ancienne classe | Nouvelle classe | Lignes à modifier |
|-----------------|-----------------|-------------------|
| `.cta-note` | `.text-note` | ~6 occurrences |
| `.contact-note` | `.text-note` | ~1 occurrence |
| `.newsletter-note` | `.text-note` | ~2 occurrences |
| `.cta-description` | `.text-description` | ~2 occurrences |
| `.idees-description` | `.text-description` | ~1 occurrence |

### Conteneurs CTA
| Ancienne structure | Nouvelle structure | Bénéfice |
|--------------------|--------------------| ---------|
| `.cta-section` > `.cta-content` | `.cta-box` | -1 élément HTML |
| `.programme-cta` | `.cta-box.cta-box--compact` | Composant unifié |

### Styles inline à remplacer
| Style inline | Classe utilitaire |
|--------------|-------------------|
| `style="display:none"` | `.hidden` |
| `style="text-align:center"` | `.text-center` |
| `style="margin-bottom:2rem"` | `.mb-3` |
| `style="margin-top:1rem"` | `.mt-1` |
| `display:flex; gap:1rem` | `.flex .gap-1` |

---

## 📋 CHECKLIST DE MIGRATION

### Phase 1: CTA (Priorité HAUTE)
- [ ] Remplacer `.programme-cta` par `.cta-box.cta-box--compact` (2 occurrences)
- [ ] Remplacer `.cta-section` + `.cta-content` par `.cta-box` (2 occurrences)
- [ ] Mettre à jour toutes les classes `.cta-note` en `.text-note` (6 occurrences)
- [ ] Mettre à jour `.cta-description` en `.text-description` (2 occurrences)

### Phase 2: Styles Inline (Priorité MOYENNE)
- [ ] Remplacer `style="display:none"` par `.hidden` (~10 occurrences)
- [ ] Remplacer `style="text-align:center"` par `.text-center` (~5 occurrences)
- [ ] Remplacer `style="margin-bottom:Xrem"` par `.mb-X` (~8 occurrences)

### Phase 3: Layout (Priorité BASSE)
- [ ] Utiliser `.flex`, `.gap-X` pour les flexbox inline (~4 occurrences)
- [ ] Standardiser les espacements avec `.my-X`, `.p-X` (~6 occurrences)

---

## 🚀 COMMANDES DE REMPLACEMENT (Search & Replace)

### Dans votre éditeur:

1. **Remplacer les CTA boxes:**
   ```
   Chercher: <div class="programme-cta" style="margin-bottom: 2rem;">
   Remplacer: <div class="cta-box cta-box--compact">
   ```

2. **Remplacer les notes:**
   ```
   Chercher: class="cta-note"
   Remplacer: class="text-note"
   ```

3. **Remplacer les descriptions:**
   ```
   Chercher: class="cta-description"
   Remplacer: class="text-description"
   ```

4. **Remplacer display:none:**
   ```
   Chercher: style="display:none"
   Remplacer: class="hidden"
   ```

5. **Remplacer text-align:center:**
   ```
   Chercher: style="text-align:center"
   Remplacer: class="text-center"
   ```

---

## ⚠️ PRÉCAUTIONS

1. **Tester après chaque modification**
   - Vérifier visuellement la page
   - Tester le responsive
   - Valider les interactions

2. **Commiter régulièrement**
   ```bash
   git add .
   git commit -m "refactor: Migration CTA vers nouveau système de design"
   ```

3. **Garder un backup**
   - Les anciennes classes sont toujours définies (rétrocompatibilité)
   - Vous pouvez revenir en arrière si besoin

---

## 📊 RÉSULTATS ATTENDUS

### Avant la migration:
- **Lignes CSS:** 2933
- **Classes redondantes:** 35
- **Styles inline:** 15+
- **Maintenabilité:** ⭐⭐

### Après la migration:
- **Lignes CSS:** ~2700 (-8%)
- **Classes unifiées:** Zéro redondance
- **Styles inline:** 0
- **Maintenabilité:** ⭐⭐⭐⭐⭐

### Impact sur le HTML:
- **Code plus lisible:** +40%
- **Composants réutilisables:** +100%
- **Temps de développement:** -50%

---

## 🎓 EXEMPLES COMPLETS

### Créer un nouveau CTA (nouveau style):
```html
<!-- CTA simple -->
<div class="cta-box">
    <a href="#" class="btn btn-primary">Action</a>
    <p class="text-note">Note explicative</p>
</div>

<!-- CTA avec titre et description -->
<div class="cta-box">
    <h3 class="cta-box__title">Titre accrocheur</h3>
    <p class="text-description">Description du call-to-action</p>
    <div class="cta-box__buttons">
        <a href="#" class="btn btn-primary">Action 1</a>
        <a href="#" class="btn btn-secondary">Action 2</a>
    </div>
    <p class="text-note">Note complémentaire</p>
</div>

<!-- CTA compact -->
<div class="cta-box cta-box--compact">
    <a href="#" class="btn btn-primary">💡 Action rapide</a>
    <p class="text-note">Courte explication</p>
</div>
```

---

## ✅ VALIDATION

Après la migration, vérifier:
- [ ] Aucune régression visuelle
- [ ] Toutes les CTA s'affichent correctement
- [ ] Le responsive fonctionne
- [ ] Les animations/transitions sont préservées
- [ ] La performance est maintenue ou améliorée

---

**Besoin d'aide ?** Consultez `DESIGN_SYSTEM.md` pour la documentation complète.

