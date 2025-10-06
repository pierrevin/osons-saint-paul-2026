# 🔍 DIAGNOSTIC - Modal de Crop ne s'ouvre pas

## 🚨 PROBLÈME IDENTIFIÉ

Le modal de crop ne s'ouvre pas lors de l'upload d'image, mais les photos s'uploadent bien.

---

## 🧪 ÉTAPES DE DIAGNOSTIC

### 1. **Testez d'abord le modal isolé**
```
http://localhost:8000/test-crop-modal.html
```
- Sélectionnez une image
- Le modal devrait s'ouvrir automatiquement
- Si ça marche → Le problème est dans l'admin
- Si ça ne marche pas → Problème avec Cropper.js

### 2. **Vérifiez la console navigateur (F12)**
Dans l'admin, ouvrez la console et regardez les messages :

**Messages attendus :**
```
🔧 Configuration des listeners de crop...
Hero input trouvé: <input id="hero_background_image" ...>
```

**Messages d'erreur possibles :**
```
❌ Cropper.js non chargé !
❌ Éléments du modal manquants !
❌ Erreur lecture fichier
```

### 3. **Testez l'upload dans l'admin**
1. Allez sur `http://localhost:8000/admin/pages/schema_admin.php`
2. Section Hero → Image de fond
3. Cliquez "Parcourir" et sélectionnez une image
4. **Regardez la console** pour voir les messages

---

## 🔧 SOLUTIONS POSSIBLES

### **Solution 1 : Cropper.js non chargé**
Si vous voyez `❌ Cropper.js non chargé !` :

**Vérifiez la connexion internet** - Les CDN peuvent être bloqués.

**Alternative : Télécharger Cropper.js localement**
```bash
# Dans le dossier admin/assets/
mkdir -p js css
wget https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js -O js/cropper.min.js
wget https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css -O css/cropper.min.css
```

Puis modifier dans `schema_admin.php` :
```html
<!-- Remplacer les CDN par : -->
<link rel="stylesheet" href="../assets/css/cropper.min.css">
<script src="../assets/js/cropper.min.js"></script>
```

### **Solution 2 : Éléments du modal manquants**
Si vous voyez `❌ Éléments du modal manquants !` :

Le modal HTML n'est pas dans la page. Vérifiez que le code du modal est bien présent dans `schema_admin.php` vers la ligne 5186.

### **Solution 3 : JavaScript bloqué**
Si aucun message n'apparaît dans la console :

1. **Vérifiez que JavaScript est activé**
2. **Désactivez les bloqueurs de pub** (AdBlock, uBlock, etc.)
3. **Testez dans un autre navigateur**

### **Solution 4 : Conflit avec onchange existant**
Si le modal ne s'ouvre pas mais que l'upload fonctionne :

Le code a été modifié pour remplacer l'`onchange` existant, mais il peut y avoir un conflit.

**Test manuel :**
1. Ouvrez la console (F12)
2. Tapez : `openCropModal(new File([''], 'test.jpg', {type: 'image/jpeg'}), document.getElementById('hero_background_image'), 'hero')`
3. Si le modal s'ouvre → Le problème est dans l'événement
4. Si le modal ne s'ouvre pas → Problème dans la fonction

---

## 🎯 TEST RAPIDE

**Dans la console de l'admin, tapez :**
```javascript
// Test 1 : Vérifier que les éléments existent
console.log('Modal:', document.getElementById('cropImageModal'));
console.log('Hero input:', document.getElementById('hero_background_image'));
console.log('Cropper:', typeof Cropper);

// Test 2 : Vérifier les listeners
const heroInput = document.getElementById('hero_background_image');
console.log('onchange:', heroInput.onchange);

// Test 3 : Forcer l'ouverture du modal
document.getElementById('cropImageModal').style.display = 'flex';
```

---

## 📊 RÉSULTATS ATTENDUS

### ✅ **Si tout fonctionne :**
```
🔧 Configuration des listeners de crop...
Hero input trouvé: <input...>
📸 Hero image sélectionnée: mon-image.jpg
🚀 openCropModal appelée avec: {file: "mon-image.jpg", preset: "hero", inputElement: <input...>}
📋 Configuration crop: {ratio: 1.7777777777777777, title: "Recadrer l'image Hero", ...}
🔍 Éléments trouvés: {modalTitle: true, modalInstructions: true, modalImage: true, modal: true}
📝 Titre et instructions mis à jour
📖 Fichier lu, taille: 1234567
👁️ Modal affiché
🎨 Initialisation de Cropper.js...
✅ Cropper.js initialisé avec succès
```

### ❌ **Si ça ne marche pas :**
```
🔧 Configuration des listeners de crop...
Hero input trouvé: <input...>
📸 Hero image sélectionnée: mon-image.jpg
❌ Cropper.js non chargé !
```

---

## 🚀 PROCHAINES ÉTAPES

1. **Testez le modal isolé** (`test-crop-modal.html`)
2. **Regardez la console** dans l'admin
3. **Partagez les messages** que vous voyez
4. **Appliquez la solution** correspondante

---

## 📞 RAPPORT DE BUG

Si le problème persiste, partagez :

1. **Messages de la console** (copier-coller)
2. **Navigateur utilisé** (Chrome, Firefox, Safari, etc.)
3. **Résultat du test isolé** (`test-crop-modal.html`)
4. **Screenshot** de la console si possible

---

**🎯 L'objectif : Identifier précisément où ça bloque pour appliquer la bonne solution !**
