# 🖼️ GUIDE D'UTILISATION - Outil de Recadrage d'Images

## ✅ IMPLÉMENTÉ ET PRÊT À L'EMPLOI !

---

## 🚀 COMMENT UTILISER

### 1. Aller dans l'admin
```
http://localhost:8000/admin/pages/schema_admin.php
```

### 2. Sélectionner une section (ex: Hero)

### 3. Cliquer sur "Parcourir" pour l'image de fond

### 4. **UN MODAL S'OUVRE AUTOMATIQUEMENT** ✨
```
┌─────────────────────────────────────────┐
│  Recadrer l'image Hero                  │
│  ────────────────────────────────────   │
│                                         │
│  [Image avec zone de crop ajustable]   │
│                                         │
│  Dimensions : 1920 × 1080 px            │
│                                         │
│  [Annuler]  [Valider et optimiser]     │
└─────────────────────────────────────────┘
```

### 5. Ajuster le cadrage
- **Déplacer** : Cliquez et glissez dans la zone
- **Redimensionner** : Tirez les coins
- **Zoom** : Molette de la souris
- **Réinitialiser** : Bouton avec icône ↺

### 6. Cliquer sur "Valider et optimiser"

### 7. Cliquer sur "Sauvegarder" dans le formulaire

### 8. ✅ L'image est recadrée, optimisée et publiée !

---

## 🎨 FORMATS AUTOMATIQUES

Le système applique **automatiquement** le bon ratio selon la section :

### Hero
- **Ratio :** 16:9 (Format paysage)
- **Conseils :** Paysage horizontal, espace pour le texte au centre

### Citations
- **Ratio :** 16:5 (Format panoramique)
- **Conseils :** Bannière large, évitez les détails importants en haut/bas

### Membres (à venir)
- **Ratio :** 3:4 (Format portrait)
- **Conseils :** Cadrez le visage au centre, laissez de l'espace au-dessus

---

## ⚡ OPTIMISATION AUTOMATIQUE

Après validation du crop :

```
Image croppée (ex: 1920x1080, 850 KB JPEG)
        ↓
🔄 TRAITEMENT BACKEND
        ↓
Image finale (1920x1080, ~350 KB WebP)

GAIN : 60% plus légère ! 🚀
```

---

## 📊 RÉSULTATS ATTENDUS

### Pour 1MB original :
```
1. Upload → Modal de crop s'ouvre
2. Crop manuel → Validation
3. Backend optimise → 
   ✅ Conversion WebP
   ✅ Compression 85%
   ✅ ~350 KB final
4. Image publiée sur le site
```

---

## 🎯 FONCTIONNALITÉS

✅ **Modal automatique** lors de l'upload  
✅ **Prévisualisation temps réel**  
✅ **Dimensions affichées** pendant le crop  
✅ **Bouton réinitialiser**  
✅ **Ratio automatique** selon la section  
✅ **Instructions contextuelles**  
✅ **Optimisation backend** transparente  
✅ **Conversion WebP** automatique  
✅ **Fallback JPEG** si WebP échoue  

---

## 🛠️ DÉPANNAGE

### Le modal ne s'ouvre pas ?
- Vérifiez que JavaScript est activé
- Regardez la console navigateur (F12)
- Vérifiez que Cropper.js est chargé

### L'image n'est pas sauvegardée ?
- Consultez `admin/logs/image_processor.log`
- Vérifiez les permissions : `chmod 755 uploads/`
- Cliquez bien sur "Sauvegarder" après validation du crop

### Le fichier est vide (0B) ?
- Le nouveau code a un **fallback JPEG automatique**
- Consultez les logs pour voir si le fallback s'est déclenché
- L'image sera en .jpg au lieu de .webp si WebP échoue

---

## 📝 LOGS

Après chaque upload, vérifiez :
```bash
tail -20 admin/logs/image_processor.log
```

**Log de succès typique :**
```
[INFO] Début traitement: cropped_xxx.jpg
[INFO] Chargement de l'image (type: jpg)
[INFO] Dimensions originales: 1920x1080
[INFO] Préparation pour WebP
[INFO] Fichier sauvegardé avec succès: 325.1 KB
[SUCCESS] Image traitée avec succès | Taille: 325.1 KB | Compression: 61.8% | Temps: 750ms
```

---

## 🎉 VOUS AVEZ MAINTENANT

✅ Outil de recadrage professionnel  
✅ Optimisation automatique  
✅ Conversion WebP  
✅ Interface intuitive  
✅ Logs détaillés  
✅ Gestion d'erreurs robuste  

**Testez-le dès maintenant dans l'admin !** 🚀

