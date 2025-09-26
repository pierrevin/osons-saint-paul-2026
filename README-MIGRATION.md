# 🚀 Plan de Migration - Site Osons Saint-Paul

## 📋 Besoins identifiés

### Fonctionnalités à développer
- [ ] **Interface admin** : Modification des contenus (textes + images)
- [ ] **CRUD Propositions** : Ajout/modification des propositions du programme avec tous les champs
- [ ] **CRUD Équipe** : Gestion des 20+ membres d'équipe
- [ ] **CRUD Rendez-vous** : Gestion des événements/rencontres
- [ ] **Formulaire contact** : Rendre fonctionnel avec envoi email
- [ ] **Boutons actifs** : Lier tous les boutons aux actions
- [ ] **Popup cookies** : Consentement RGPD
- [ ] **Images WebP** : Conversion et optimisation

### Hébergement disponible
- Hébergement OVH existant

---

## 🔍 Comparatif des solutions techniques

### 1. WordPress (Classique) - ⭐ **RECOMMANDÉ**

**Points forts :**
- ✅ Admin prêt à l'emploi, éditeur convivial
- ✅ Modèles de contenu avec CPT + ACF en 1 jour
- ✅ Plugins fiables : formulaire, cookies, WebP, SEO
- ✅ Hébergement simple sur OVH mutualisé
- ✅ **Design 100% fidèle** : CSS/HTML/JS identiques

**Points faibles :**
- ⚠️ Sécurité/MAJ à maintenir
- ⚠️ Performances à optimiser (cache, CDN)

**Architecture proposée :**
```
wp-content/themes/osons-saint-paul/
├── style.css (styles actuels + enqueue WP)
├── index.php (template principal)
├── functions.php (CPT + enqueue)
├── template-parts/ (sections modulaires)
├── assets/ (JS/images actuels)
└── admin/ (interface édition)
```

**Modèles de contenu :**
- **Propositions** : titre, description, catégorie, tag citoyenne, image, ordre
- **Équipe** : nom, rôle, photo, bio, ordre (grille 5 colonnes)
- **Rendez-vous** : titre, description, date, lieu, lien, statut
- **Pages statiques** : Hero, Charte, Transitions

**Estimation :** 1-3 jours initial + maintenance légère

---

### 2. Headless CMS + Front moderne (Strapi + Next.js)

**Points forts :**
- ✅ Schémas de contenu propres, API, workflow validation
- ✅ Front ultra-performant, WebP/optimisation, SEO top
- ✅ Évolutif (multilingue, futur 2026+)

**Points faibles :**
- ⚠️ Nécessite serveur Node (OVH VPS)
- ⚠️ Pipeline de déploiement plus complexe
- ⚠️ Mise en place plus longue

**Hébergement :**
- Strapi sur OVH VPS (ou Render/Scalingo)
- Front sur Vercel/Netlify/OVH Static + CDN

**Estimation :** 4-7 jours initial + CI/CD

---

### 3. Git-based CMS (Netlify CMS/Decap CMS)

**Points forts :**
- ✅ Reste en "pur" front, admin simple
- ✅ Déploiement gratuit/peu coûteux, très rapide

**Points faibles :**
- ❌ Moins adapté aux contenus relationnels complexes
- ❌ Media management limité
- ❌ Filtres/requêtes complexes difficiles

**Quand choisir :** Petits sites éditoriaux simples

---

### 4. Supabase "from scratch" + Admin personnalisé

**Points forts :**
- ✅ Base Postgres, Auth, Storage, API auto
- ✅ Front actuel conservé, mini-backoffice sur-mesure

**Points faibles :**
- ⚠️ Admin à construire (temps)
- ⚠️ Gestion RGPD, emailing à intégrer
- ⚠️ Moins "out-of-the-box" que WP/Strapi

**Hébergement :**
- Supabase hébergé + Front sur OVH/Vercel

---

## 🎯 Solution retenue : WordPress

### Pourquoi WordPress ?
1. **Fidélité design garantie** : CSS/HTML/JS identiques
2. **Rapidité de mise en place** : 1-3 jours vs 4-7 jours
3. **Hébergement OVH** : Utilisation de l'existant
4. **Maintenance simple** : Interface familière
5. **Évolutivité** : Possibilité de migrer vers headless plus tard

### Plan de migration

#### Phase 1 : Structure de base
- [ ] Création du thème WordPress personnalisé
- [ ] Conversion HTML → Templates PHP
- [ ] Intégration CSS (styles actuels)
- [ ] Adaptation JavaScript (filtres, breadcrumb, transitions)

#### Phase 2 : Modèles de contenu
- [ ] CPT Propositions (avec filtres dynamiques)
- [ ] CPT Équipe (grille 5 colonnes)
- [ ] CPT Rendez-vous
- [ ] Pages statiques (Hero, Charte, Transitions)

#### Phase 3 : Interface admin
- [ ] ACF pour édition visuelle
- [ ] Upload images avec redimensionnement
- [ ] Gestion couleurs thématiques
- [ ] Configuration boutons/liens

#### Phase 4 : Fonctionnalités
- [ ] Formulaire contact (Gravity Forms/CF7 + SMTP OVH)
- [ ] WebP automatique (plugin + optimisation)
- [ ] Cookies RGPD (tarteaucitron.js)
- [ ] Cache et performance (WP Rocket)
- [ ] SEO (Yoast/RankMath)

#### Phase 5 : Tests et déploiement
- [ ] Tests de fidélité pixel-perfect
- [ ] Migration des données actuelles
- [ ] Tests fonctionnels complets
- [ ] Déploiement sur OVH

### Plugins recommandés
- **ACF Pro** : Champs personnalisés avancés
- **Gravity Forms** : Formulaires robustes
- **WP Rocket** : Cache et performance
- **Imagify/ShortPixel** : WebP et optimisation images
- **tarteaucitron.js** : RGPD cookies
- **Yoast SEO** : Optimisation SEO
- **Wordfence** : Sécurité
- **UpdraftPlus** : Sauvegardes

### Structure technique finale
```
OVH Mutualisé (PHP 8.x, MariaDB)
├── WordPress Core
├── Thème osons-saint-paul (personnalisé)
├── Plugins (ACF, Gravity Forms, etc.)
├── Cache (WP Rocket)
└── CDN (OVH CDN ou Cloudflare)
```

---

## 📝 Notes de développement

### Fidélité du design
- **CSS identique** : Reprise exacte des styles actuels
- **HTML structuré** : Templates PHP reproduisant la structure
- **JavaScript préservé** : Tous les scripts fonctionnels
- **Responsive intact** : Tous les media queries conservés

### Performance
- **WebP automatique** : Conversion + balises `<picture>`
- **Cache** : WP Rocket + CDN
- **Optimisation** : Images lazy loading, minification
- **SEO** : Métas automatiques, sitemap, schema.org

### Sécurité
- **Wordfence** : Protection contre attaques
- **Sauvegardes** : UpdraftPlus (quotidiennes)
- **MAJ** : WordPress + plugins à jour
- **HTTPS** : Certificat SSL OVH

---

## 🚀 Prochaines étapes

1. **Validation** du choix WordPress
2. **Création** de la structure de base du thème
3. **Migration** progressive des sections
4. **Tests** de fidélité et fonctionnalités
5. **Déploiement** sur OVH

---

*Document créé le : $(date)*
*Dernière mise à jour : $(date)*
