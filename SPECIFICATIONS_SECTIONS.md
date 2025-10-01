# 📋 SPÉCIFICATIONS DES SECTIONS ADMIN

## 🎯 SECTION PROGRAMME (✅ IMPLÉMENTÉE)

### **Actions disponibles :**
1. **Gérer Section** → Modal pour modifier H2, H3, description
2. **Ajouter Proposition** → Modal avec formulaire complet
3. **Modifier Proposition** → Modal d'édition pour chaque proposition
4. **Supprimer Proposition** → Confirmation + suppression

### **Blocs de gestion :**

#### **📝 En-tête de section :**
- **H2** : Titre principal ("Notre programme")
- **H3** : Sous-titre ("Osons intégrer vos idées") 
- **Description** : Texte introductif

#### **📋 Propositions :**
- **Titre** : Nom de la proposition
- **Description** : Explication détaillée
- **Pilier** : Sélection déroulante avec 4 options :
  - 🛡️ Osons protéger (#2d5a3d)
  - 🤝 Osons tisser des liens (#4a7c59)
  - 🎨 Osons dessiner (#6b8e23)
  - 🔓 Osons ouvrir (#8fbc8f)
- **Couleur** : Automatique selon le pilier sélectionné
- **Proposition citoyenne** : Checkbox pour marquer comme proposition citoyenne
- **Points clés** : Liste des actions (un par ligne)

---

## 🏠 SECTION HERO (✅ IMPLÉMENTÉE)

### **Actions disponibles :**
1. **Modifier** → Modal pour tous les éléments Hero

### **Blocs de gestion :**
- **Titre principal** : H1 de la page
- **Sous-titre** : Texte d'accompagnement (si applicable)
- **Description** : Paragraphe explicatif (si applicable)
- **Bouton principal** : Texte du CTA principal
- **Bouton secondaire** : Texte du CTA secondaire
- **Image de fond** : Upload d'image avec conversion WebP

---

## 👥 SECTION ÉQUIPE (✅ FONCTIONNELLE)

### **Actions disponibles :**
1. **Gérer** → Redirection vers page équipe complète
2. **Ajouter Membre** → Modal d'ajout
3. **Modifier Membre** → Modal d'édition
4. **Supprimer Membre** → Confirmation + suppression

### **Blocs de gestion :**
- **Nom complet** : Prénom + Nom
- **Rôle/Fonction** : Poste occupé
- **Citation** : Phrase personnelle
- **Photo** : Upload d'image de profil

---

## 💬 SECTIONS CITATIONS (✅ IMPLÉMENTÉES)

### **Actions disponibles :**
1. **Modifier** → Modal pour chaque citation

### **Blocs de gestion :**
- **Citation 1** : Programme → Équipe
- **Citation 2** : Équipe → Rendez-vous  
- **Citation 3** : Rendez-vous → Charte
- **Citation 4** : Charte → Idées

**Pour chaque citation :**
- **Texte** : Contenu de la citation
- **Auteur** : Source de la citation
- **Image de fond** : Upload d'image avec conversion WebP

---

## 📅 SECTION RENDEZ-VOUS (✅ IMPLÉMENTÉE)

### **Actions disponibles :**
1. **Gérer Section** → Modal pour H2, H3
2. **Ajouter Événement** → Modal d'ajout
3. **Modifier Événement** → Modal d'édition
4. **Supprimer Événement** → Confirmation + suppression

### **Blocs de gestion :**

#### **📝 En-tête de section :**
- **H2** : Titre principal
- **H3** : Sous-titre

#### **📅 Événements :**
- **Titre** : Nom de l'événement
- **Date** : Date et heure (format ISO)
- **Lieu** : Adresse ou lieu
- **Description** : Détails de l'événement
- **Tri automatique** : Affichage des 3 prochains événements
- **Bouton "Voir plus"** : Pour afficher tous les événements futurs

---

## 🤝 SECTION CHARTE (✅ IMPLÉMENTÉE)

### **Actions disponibles :**
1. **Gérer Section** → Modal pour H2, H3, introduction
2. **Ajouter Principe** → Modal d'ajout
3. **Modifier Principe** → Modal d'édition
4. **Supprimer Principe** → Confirmation + suppression

### **Blocs de gestion :**

#### **📝 En-tête de section :**
- **H2** : Titre principal
- **H3** : Sous-titre
- **Texte d'introduction** : Paragraphes introductifs
- **Texte de mise en avant** : Texte en gras (introduction)

#### **📜 Principes :**
- **Titre** : Nom du principe
- **Description** : Explication détaillée
- **Thématique** : Source ou référence (optionnel)
- **Ordre** : Numérotation automatique

---

## 💡 SECTION IDÉES (⚠️ PARTIELLEMENT IMPLÉMENTÉE)

### **État actuel :**
- ✅ **Formulaire de contact** : Fonctionnel (nom, email, sujet, message)
- ❌ **Gestion admin des soumissions** : Non implémenté (pas de CRUD)
- ❌ **Suivi des idées** : Non implémenté

### **Actions disponibles :**
- Formulaire public fonctionnel
- Envoi d'emails de confirmation

### **À implémenter :**
- Interface admin pour gérer les soumissions
- Système de statuts (Nouvelle, En cours, Réalisée, Rejetée)
- Catégorisation des idées

---

## 🖼️ SECTION MÉDIATHÈQUE (⚠️ BASIQUE)

### **État actuel :**
- ✅ **Lien Google Drive** : Configuré et fonctionnel
- ❌ **Gestion de fichiers intégrée** : Non implémentée
- ❌ **Upload direct** : Non implémenté

### **Actions disponibles :**
- Redirection vers Google Drive externe

### **À implémenter :**
- Système d'upload de fichiers intégré
- Galerie de photos/vidéos
- Organisation par catégories
- Prévisualisation des médias

---

## 🎨 PATTERNS COMMUNS

### **Structure des modals :**
1. **En-tête** : Titre + bouton fermer
2. **Corps** : Formulaire organisé en sections
3. **Actions** : Boutons Annuler/Sauvegarder

### **Types de champs :**
- **Texte** : Input simple
- **Texte long** : Textarea
- **Sélection** : Select avec options
- **Couleur** : Input color
- **Fichier** : Input file
- **Checkbox** : Case à cocher
- **Date** : Input date

### **Validation :**
- **Champs obligatoires** : Marqués avec *
- **Validation côté client** : JavaScript
- **Validation côté serveur** : PHP
- **Messages d'erreur** : Affichage clair

### **UX/UI :**
- **Modals responsives** : S'adaptent à tous les écrans
- **Fermeture intuitive** : Escape, clic overlay, bouton X
- **Feedback visuel** : États de chargement, confirmations
- **Navigation fluide** : Pas de rechargement de page
