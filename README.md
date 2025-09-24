# Site Web "Osons Saint-Paul 2026"

## 📋 Description du Projet

Site de campagne one-page pour la liste citoyenne "Osons Saint-Paul" aux élections municipales 2026. Ce site incarne les valeurs de participation, transparence, proximité et modernité, avec un design inspiré du concept de "carte postale" pour créer une expérience humaine et engageante.

## 🎨 Concept Créatif

**Le fil rouge : La Carte Postale**
- Invitation personnelle au dialogue et à la co-construction
- Design avec effets de cartes et textures papier subtiles
- Typographie inspirée de l'écriture manuscrite pour certains accents
- Ton général évoquant l'échange et la convivialité

## 🎯 Fonctionnalités Principales

### ✅ Sections Implémentées

1. **Header Fixe** - Navigation fluide avec logo et menu
2. **Hero Banner** - Section d'accueil avec call-to-action
3. **Programme Interactif** - Système de filtres et cartes postales animées
4. **Votre Avis Compte** - Section d'engagement citoyen
5. **Agenda des Rencontres** - Timeline des événements
6. **Notre Équipe** - Galerie de portraits interactifs
7. **Footer Complet** - Newsletter, contacts et réseaux sociaux

### 🎨 Identité Visuelle

**Palette de Couleurs :**
- 🧡 Corail `#F2775A` (Titres, Slogans)
- 💛 Jaune `#F5C15B` (Boutons, Pictos, Badges)
- 💙 Bleu clair `#7DBCD5` (Fonds secondaires)
- 🌿 Vert profond `#2F6E4F` (Détails écologiques)
- 🔵 Bleu nuit `#2C3E50` (Texte & Contraste)
- ⚪ Blanc cassé `#FAF5EE` (Fond principal)

**Typographie :**
- **Titres** : Merriweather (Serif moderne)
- **Corps de texte** : Lato (Sans-serif lisible)
- **Accents** : Caveat (Script élégant)

## 🚀 Installation et Utilisation

### Prérequis
- Navigateur web moderne (Chrome, Firefox, Safari, Edge)
- Serveur web local (optionnel pour le développement)

### Installation Simple
1. Téléchargez tous les fichiers dans un dossier
2. Ouvrez `index.html` dans votre navigateur
3. Le site est prêt à être utilisé !

### Pour un Serveur Local (Recommandé)
```bash
# Avec Python 3
python -m http.server 8000

# Avec Node.js (si vous avez npx)
npx serve .

# Avec PHP
php -S localhost:8000
```

Puis ouvrez `http://localhost:8000` dans votre navigateur.

## 📱 Responsive Design

Le site est entièrement optimisé pour :
- **Mobile First** - Design pensé d'abord pour mobile
- **Tablettes** - Adaptation fluide des grilles
- **Desktop** - Expérience enrichie avec animations

## ♿ Accessibilité

Conformité aux normes WCAG 2.1 AA :
- Navigation au clavier
- Contraste des couleurs optimisé
- Attributs ARIA appropriés
- Support des lecteurs d'écran
- Respect des préférences de mouvement réduit

## ⚡ Performance

Optimisations incluses :
- Images optimisées et lazy loading
- CSS et JS minifiables
- Polices Google Fonts optimisées
- Transitions GPU-accélérées
- Code JavaScript efficace avec debouncing

## 🔧 Personnalisation

### Modifier les Couleurs
Éditez les variables CSS dans `styles.css` :
```css
:root {
    --coral: #F2775A;
    --yellow: #F5C15B;
    --light-blue: #7DBCD5;
    /* ... autres couleurs */
}
```

### Ajouter des Propositions
Dans `index.html`, ajoutez une nouvelle carte dans `.propositions-grid` :
```html
<div class="proposition-card" data-category="votre-categorie">
    <!-- Contenu de la carte -->
</div>
```

### Modifier l'Équipe
Remplacez les images et textes dans la section `.team-grid`.

## 📊 Analytics et Tracking

Le site inclut des hooks pour l'intégration d'analytics :
- Google Analytics (à configurer)
- Tracking des interactions importantes
- Mesure de performance

## 🔗 Intégrations Possibles

- **Newsletter** : Intégration Typeform, Mailchimp, ou autre service
- **Réseaux Sociaux** : Liens vers vos comptes
- **Contact** : Formulaire de contact ou lien vers votre système
- **Événements** : Intégration calendrier Google ou autre

## 📝 Contenu à Personnaliser

### Informations à Remplacer :
1. **Contact** : Email et téléphone de campagne
2. **Réseaux Sociaux** : Liens vers vos comptes
3. **Événements** : Dates, lieux et descriptions réels
4. **Équipe** : Photos et présentations des candidats
5. **Propositions** : Contenu détaillé de votre programme
6. **Logo** : Remplacez `Ofeuille.png` par votre logo final

## 🛠️ Développement

### Structure des Fichiers
```
/
├── index.html          # Page principale
├── styles.css          # Styles CSS
├── script.js           # JavaScript interactif
├── Ofeuille.png        # Logo (à remplacer)
└── README.md           # Documentation
```

### Technologies Utilisées
- **HTML5** - Structure sémantique
- **CSS3** - Styles avec variables CSS et Grid/Flexbox
- **JavaScript ES6+** - Interactions et animations
- **Google Fonts** - Typographie web
- **Font Awesome** - Icônes

## 🎯 Prochaines Étapes

1. **Personnaliser le contenu** avec vos informations réelles
2. **Remplacer les images** par vos photos et logo
3. **Configurer les intégrations** (newsletter, analytics)
4. **Tester sur différents appareils** et navigateurs
5. **Mettre en ligne** sur votre hébergement

## 📞 Support

Pour toute question ou personnalisation supplémentaire, n'hésitez pas à nous contacter.

---

**Osons Saint-Paul 2026** - Construisons ensemble le village vivant et partagé ! 🏘️✨
