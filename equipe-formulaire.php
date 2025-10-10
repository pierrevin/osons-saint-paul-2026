<?php
/**
 * Formulaire temporaire pour l'équipe
 * Page publique sans login - remplissage direct des données équipe
 */

session_start();

// Charger les données existantes
require_once __DIR__ . '/admin/config.php';
$dataFile = DATA_PATH . '/site_content.json';
$content = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
if (!is_array($content)) { $content = []; }

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_member') {
        try {
            // Validation des données
            $name = trim($_POST['name'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $age = trim($_POST['age'] ?? '');
            $description = trim($_POST['description'] ?? '');
            
            if (empty($name) || empty($role)) {
                throw new Exception('Nom et fonction sont requis');
            }
            
            if (strlen($description) > 150) {
                throw new Exception('La description ne doit pas dépasser 150 caractères');
            }
            
            // Gestion de la photo (depuis la galerie)
            $photo = null;
            if (!empty($_POST['selected_image_path'])) {
                $originalPath = trim($_POST['selected_image_path']);
                // Traiter l'image sélectionnée : recadrage et optimisation
                $photo = processSelectedImage($originalPath);
            }
            
            if (!$photo) {
                throw new Exception('Une photo est requise');
            }
            
            // Ajouter le membre
            $members = $content['equipe']['members'] ?? [];
            $id = uniqid('member_', true);
            
            $newMember = [
                'id' => $id,
                'name' => $name,
                'role' => $role,
                'age' => $age,
                'description' => $description,
                'photo' => $photo,
                'image' => $photo // Compatibilité
            ];
            
            $members[] = $newMember;
            $content['equipe']['members'] = $members;
            
            // Sauvegarder
            file_put_contents($dataFile, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            $success = "Membre ajouté avec succès !";
            
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    // Traitement AJAX pour l'upload d'image croppée
    if ($action === 'upload_cropped_image') {
        header('Content-Type: application/json');
        
        try {
            if (!isset($_FILES['cropped_image'])) {
                throw new Exception('Aucune image reçue');
            }
            
            $photoPath = handleCroppedImageUpload($_FILES['cropped_image']);
            
            echo json_encode([
                'success' => true,
                'image_path' => $photoPath
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        
        exit;
    }
}

function handleImageUpload($file) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($extension, $allowedExtensions)) {
        throw new Exception('Format de fichier non autorisé');
    }
    
    $filename = 'member_' . time() . '_' . uniqid() . '.webp';
    $filepath = $uploadDir . $filename;
    
    // Convertir en WebP et redimensionner
    if (extension_loaded('gd')) {
        $image = null;
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            case 'gif':
                $image = imagecreatefromgif($file['tmp_name']);
                break;
            case 'webp':
                $image = imagecreatefromwebp($file['tmp_name']);
                break;
        }
        
        if ($image) {
            // Redimensionner à 600x800 (ratio 3:4)
            $resized = imagecreatetruecolor(600, 800);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, 600, 800, imagesx($image), imagesy($image));
            
            // Sauvegarder en WebP
            imagewebp($resized, $filepath, 85);
            imagedestroy($image);
            imagedestroy($resized);
            
            return 'uploads/' . $filename;
        }
    }
    
    // Fallback : copie simple
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/' . $filename;
    }
    
    throw new Exception('Erreur lors de l\'upload');
}

function handleCroppedImageUpload($file) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // L'image croppée vient déjà optimisée du JavaScript
    $filename = 'member_' . time() . '_' . uniqid() . '.webp';
    $filepath = $uploadDir . $filename;
    
    // Traitement avec GD pour optimisation WebP
    if (extension_loaded('gd')) {
        $image = imagecreatefromjpeg($file['tmp_name']);
        
        if ($image) {
            // Redimensionner si nécessaire (600x800 max)
            $resized = imagecreatetruecolor(600, 800);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, 600, 800, imagesx($image), imagesy($image));
            
            // Optimisation WebP avec qualité 85%
            imagewebp($resized, $filepath, 85);
            imagedestroy($image);
            imagedestroy($resized);
            
            return 'uploads/' . $filename;
        }
    }
    
    // Fallback : déplacer le fichier tel quel
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/' . $filename;
    }
    
    throw new Exception('Erreur lors du traitement de l\'image');
}

function processSelectedImage($originalPath) {
    // Si l'image est déjà optimisée (dans gallery_optimized), la copier directement
    if (strpos($originalPath, 'gallery_optimized') !== false) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fullOriginalPath = __DIR__ . '/' . $originalPath;
        
        if (!file_exists($fullOriginalPath)) {
            throw new Exception('Image optimisée non trouvée');
        }
        
        // Copier l'image optimisée avec un nouveau nom
        $filename = 'member_' . time() . '_' . uniqid() . '.webp';
        $filepath = $uploadDir . $filename;
        
        if (copy($fullOriginalPath, $filepath)) {
            return 'uploads/' . $filename;
        }
        
        throw new Exception('Erreur lors de la copie de l\'image optimisée');
    }
    
    // Si l'image n'est pas optimisée, utiliser le processeur d'images
    require_once __DIR__ . '/admin/includes/image_processor.php';
    
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fullOriginalPath = __DIR__ . '/' . $originalPath;
    
    if (!file_exists($fullOriginalPath)) {
        throw new Exception('Image originale non trouvée');
    }
    
    // Créer un fichier temporaire pour simuler un upload
    $tempFile = [
        'name' => basename($originalPath),
        'tmp_name' => $fullOriginalPath,
        'size' => filesize($fullOriginalPath),
        'error' => UPLOAD_ERR_OK
    ];
    
    $processor = new ImageProcessor();
    $result = $processor->processWithPreset($tempFile, $uploadDir, 'member', 'member');
    
    if ($result['success']) {
        return 'uploads/' . $result['filename'];
    }
    
    throw new Exception('Erreur lors du traitement de l\'image : ' . ($result['error'] ?? 'Erreur inconnue'));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire Équipe - Osons Saint-Paul</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Cropper.js pour le recadrage d'images -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Lato', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #faf5ee 0%, #ffffff 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .header h1 {
            font-family: 'Caveat', cursive;
            font-size: 3rem;
            color: #ec654f;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .form-container {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            border: 1px solid rgba(236, 101, 79, 0.2);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ec654f;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .char-counter {
            font-size: 0.9rem;
            color: #666;
            text-align: right;
            margin-top: 0.25rem;
        }
        
        .char-counter.warning {
            color: #ec654f;
        }
        
        .photo-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .photo-section h3 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .photo-upload {
            border: 2px dashed #ec654f;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .photo-upload:hover {
            border-color: #d55a47;
            background: #fafafa;
        }
        
        .photo-upload input {
            display: none;
        }
        
        .photo-upload-icon {
            font-size: 3rem;
            color: #ec654f;
            margin-bottom: 1rem;
        }
        
        .photo-upload-text {
            color: #666;
            font-weight: 500;
        }
        
        .btn {
            background: #ec654f;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn:hover {
            background: #d55a47;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(236, 101, 79, 0.3);
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .current-members {
            margin-top: 3rem;
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .current-members h2 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        .member-card {
            background: #f8f9fa;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        
        .member-photo {
            width: 100%;
            height: 200px;
            background: #ddd;
            position: relative;
            overflow: hidden;
        }
        
        .member-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .member-info {
            padding: 1rem;
        }
        
        .member-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.25rem;
        }
        
        .member-role {
            color: #ec654f;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .member-description {
            font-size: 0.85rem;
            color: #666;
            line-height: 1.4;
        }
        
        /* Modal cropper */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.8);
        }
        
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .modal-header h3 {
            color: #333;
            margin: 0;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #ec654f;
        }
        
        .cropper-container {
            max-height: 400px;
            margin-bottom: 1.5rem;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        /* Styles pour la galerie d'images */
        .photo-gallery {
            margin-bottom: 2rem;
        }
        
        .photo-gallery h4 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1rem;
        }
        
        .gallery-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 3px solid transparent;
            width: 100%;
            max-width: none;
        }
        
        .gallery-item:hover {
            transform: scale(1.1);
            border-color: #ec654f;
            z-index: 20;
            box-shadow: 0 12px 35px rgba(0,0,0,0.4);
        }
        
        .gallery-item:hover img {
            transform: scale(1.4);
        }
        
        .gallery-item.selected {
            border-color: #ec654f;
            box-shadow: 0 0 0 2px rgba(236, 101, 79, 0.3);
        }
        
        .gallery-item img {
            width: 100%;
            height: 213px; /* 160px * 4/3 = 213px pour respecter le ratio 3:4 */
            object-fit: contain; /* Affiche l'image en intégralité */
            object-position: center;
            display: block;
            transition: transform 0.3s ease;
            background: #f8f9fa; /* Fond gris clair pour les espaces vides */
        }
        
        .gallery-item-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 213px; /* Même hauteur que l'image pour respecter le format 3:4 */
            background: rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item.selected .gallery-item-overlay {
            opacity: 1;
            background: rgba(236, 101, 79, 0.7);
        }
        
        .gallery-item-overlay i {
            color: white;
            font-size: 1.5rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .gallery-item:hover .gallery-item-overlay i {
            opacity: 1;
        }
        
        .gallery-item.selected .gallery-item-overlay i {
            opacity: 1;
        }
        
        .photo-divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
        }
        
        .photo-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
        }
        
        .photo-divider span {
            background: white;
            padding: 0 1rem;
            color: #666;
            font-weight: 600;
        }
        
        .selected-photo {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 10px;
            border: 2px solid #ec654f;
        }
        
        .selected-photo h4 {
            color: #333;
            margin-bottom: 1rem;
        }
        
        .selected-photo-preview {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .selected-photo-preview img {
            width: 100px;
            height: 133px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
        
        .btn-remove-photo {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        
        .btn-remove-photo:hover {
            background: #c82333;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem 0.5rem;
            }
            
            .container {
                max-width: 100%;
                padding: 0 0.5rem;
            }
            
            .form-container {
                padding: 1.5rem 1rem;
                border-radius: 15px;
            }
            
            .header {
                margin-bottom: 2rem;
            }
            
            .header h1 {
                font-size: 2.2rem;
            }
            
            .header p {
                font-size: 1rem;
            }
            
            .form-group {
                margin-bottom: 1.2rem;
            }
            
            .form-group input,
            .form-group textarea {
                padding: 0.9rem;
                font-size: 16px; /* Évite le zoom sur iOS */
            }
            
            .photo-section {
                padding: 1rem;
                margin: 1rem 0;
            }
            
            .gallery-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                width: 100%;
                margin: 0;
                padding: 0;
            }
            
            .gallery-item {
                width: 100%;
                max-width: none;
            }
            
            .gallery-item img {
                width: 100%;
                height: auto;
                aspect-ratio: 3/4; /* Force le ratio 3:4 sur mobile */
                object-fit: contain;
                object-position: center;
                background: #f8f9fa;
            }
            
            .gallery-item-overlay {
                height: 100%; /* S'adapte automatiquement au ratio de l'image */
                background: rgba(0, 0, 0, 0.2); /* Overlay plus discret */
            }
            
            .gallery-item:hover .gallery-item-overlay {
                background: rgba(0, 0, 0, 0.4); /* Overlay plus visible au survol */
            }
            
            .selected-photo {
                padding: 1rem;
            }
            
            .selected-photo-preview {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
                text-align: center;
            }
            
            .selected-photo-preview img {
                width: 80px;
                height: 107px; /* 80px * 4/3 = 107px */
            }
            
            .btn {
                padding: 1rem 1.5rem;
                font-size: 1rem;
                width: 100%;
                justify-content: center;
            }
            
            .members-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .member-card {
                margin-bottom: 1rem;
            }
            
            .member-photo {
                height: 250px;
            }
            
            .modal-content {
                margin: 1% auto;
                padding: 1rem;
                width: 95%;
                max-height: 95vh;
            }
            
            .modal-header {
                margin-bottom: 1rem;
            }
            
            .modal-header h3 {
                font-size: 1.2rem;
            }
            
            .cropper-container {
                max-height: 300px;
            }
            
            .modal-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            
        .modal-actions .btn {
            width: 100%;
        }
    }
    
    /* Modal de prévisualisation d'image */
    .preview-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        cursor: pointer;
    }
    
    .preview-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 90%;
        max-height: 90%;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }
    
    .preview-modal img {
        width: 100%;
        height: auto;
        display: block;
        max-height: 80vh;
        object-fit: contain;
    }
    
    .preview-modal-close {
        position: absolute;
        top: 10px;
        right: 15px;
        color: white;
        font-size: 30px;
        font-weight: bold;
        cursor: pointer;
        z-index: 1001;
        background: rgba(0,0,0,0.5);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .preview-modal-close:hover {
        background: rgba(0,0,0,0.8);
    }
    
    .gallery-item {
        cursor: pointer;
    }

        @media (max-width: 480px) {
            .gallery-grid {
                grid-template-columns: 1fr;
                gap: 0.8rem;
                width: 100%;
                margin: 0;
                padding: 0;
            }
            
            .gallery-item img {
                width: 100%;
                height: auto;
                aspect-ratio: 3/4; /* Force le ratio 3:4 sur smartphone */
                object-fit: contain;
                object-position: center;
                background: #f8f9fa;
            }
            
            .gallery-item-overlay {
                height: 100%; /* S'adapte automatiquement au ratio de l'image */
                background: rgba(0, 0, 0, 0.15); /* Overlay encore plus discret */
            }
            
            .gallery-item:hover .gallery-item-overlay {
                background: rgba(0, 0, 0, 0.35); /* Overlay plus visible au survol */
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .form-container {
                padding: 1rem 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Photo et petite phrase</h1>
            <p>Remplissez ce formulaire pour apparaître dans la section équipe</p>
        </div>
        
        <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" id="equipe-form">
                <input type="hidden" name="action" value="add_member">
                
                <div class="form-group">
                    <label for="name">Prénom et Nom *</label>
                    <input type="text" id="name" name="name" required placeholder="Marie Dupont">
                </div>
                
                <div class="form-group">
                    <label for="role">Fonction *</label>
                    <input type="text" id="role" name="role" required placeholder="Conseillère Éducation">
                </div>
                
                <div class="form-group">
                    <label for="age">Âge</label>
                    <input type="number" id="age" name="age" placeholder="35">
                </div>
                
                <div class="form-group">
                    <label for="description">Présentation / Motivations *</label>
                    <textarea id="description" name="description" required placeholder="Une phrase pour vous présenter et expliquer vos motivations..." maxlength="150"></textarea>
                    <div class="char-counter" id="char-counter">0 / 150 caractères</div>
                </div>
                
                <div class="photo-section">
                    <h3><i class="fas fa-camera"></i> Photo</h3>
                    
                    <!-- Galerie d'images disponibles -->
                    <div class="photo-gallery" id="photoGallery">
                        <h4>Choisir parmi les photos disponibles :</h4>
                        <div class="gallery-grid" id="galleryGrid">
                            <!-- Les images seront chargées via JavaScript -->
                        </div>
                    </div>

                    <!-- Aperçu de la photo sélectionnée -->
                    <div class="selected-photo" id="selectedPhoto" style="display: none;">
                        <h4>Photo sélectionnée :</h4>
                        <div class="selected-photo-preview">
                            <img id="selectedPhotoPreview" src="" alt="Aperçu">
                            <button type="button" class="btn-remove-photo" onclick="clearSelectedPhoto()">
                                <i class="fas fa-times"></i> Changer
                            </button>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn" style="width: 100%; justify-content: center;">
                    <i class="fas fa-plus"></i> Ajouter à l'équipe
                </button>
            </form>
        </div>
    </div>
    
    <!-- Modal de prévisualisation d'image -->
    <div id="previewModal" class="preview-modal">
        <div class="preview-modal-content">
            <span class="preview-modal-close">&times;</span>
            <img id="previewImage" src="" alt="Aperçu">
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        // Compteur de caractères
        const description = document.getElementById('description');
        const charCounter = document.getElementById('char-counter');
        
        description.addEventListener('input', function() {
            const length = this.value.length;
            charCounter.textContent = `${length} / 150 caractères`;
            
            if (length > 120) {
                charCounter.classList.add('warning');
            } else {
                charCounter.classList.remove('warning');
            }
        });
        
        // Variables pour la galerie
        let selectedImagePath = null;
        
        // Charger la galerie d'images au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            loadImageGallery();
        });
        
        function loadImageGallery() {
            const galleryGrid = document.getElementById('galleryGrid');
            
            // Charger les images via AJAX
            fetch('load_gallery_images.php')
                .then(response => response.json())
                .then(images => {
                    if (images.length === 0) {
                        galleryGrid.innerHTML = '<p style="text-align: center; color: #666; padding: 2rem;">Aucune image disponible dans la galerie. Veuillez uploader une nouvelle photo.</p>';
                        return;
                    }
                    
                    galleryGrid.innerHTML = images.map(image => `
                        <div class="gallery-item" onclick="selectImage('${image.path}', '${image.name}')" ondblclick="previewImage('${image.path}')">
                            <img src="${image.path}" alt="${image.name}" loading="lazy">
                            <div class="gallery-item-overlay">
                                <i class="fas fa-plus"></i>
                            </div>
                        </div>
                    `).join('');
                })
                .catch(error => {galleryGrid.innerHTML = '<p style="text-align: center; color: #dc3545; padding: 2rem;">Erreur lors du chargement de la galerie.</p>';
                });
        }
        
        function selectImage(imagePath, imageName) {
            // Désélectionner toutes les images
            document.querySelectorAll('.gallery-item').forEach(item => {
                item.classList.remove('selected');
                // Remettre l'icône plus
                const icon = item.querySelector('.gallery-item-overlay i');
                if (icon) {
                    icon.className = 'fas fa-plus';
                }
            });
            
            // Sélectionner l'image cliquée
            event.currentTarget.classList.add('selected');
            
            // Changer l'icône en check
            const selectedIcon = event.currentTarget.querySelector('.gallery-item-overlay i');
            if (selectedIcon) {
                selectedIcon.className = 'fas fa-check';
            }
            
            // Stocker le chemin de l'image
            selectedImagePath = imagePath;
            
            // Afficher l'aperçu
            const selectedPhoto = document.getElementById('selectedPhoto');
            const selectedPhotoPreview = document.getElementById('selectedPhotoPreview');
            
            selectedPhotoPreview.src = imagePath;
            selectedPhoto.style.display = 'block';
            
            // Ajouter un champ caché pour l'image sélectionnée
            let hiddenInput = document.getElementById('selected-image-path');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'selected_image_path';
                hiddenInput.id = 'selected-image-path';
                document.getElementById('equipe-form').appendChild(hiddenInput);
            }
            hiddenInput.value = imagePath;}
        
        function previewImage(imagePath) {
            const modal = document.getElementById('previewModal');
            const previewImg = document.getElementById('previewImage');
            
            previewImg.src = imagePath;
            modal.style.display = 'block';
        }
        
        function closePreviewModal() {
            const modal = document.getElementById('previewModal');
            modal.style.display = 'none';
        }
        
        function clearSelectedPhoto() {
            selectedImagePath = null;
            
            // Désélectionner toutes les images de la galerie
            document.querySelectorAll('.gallery-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            // Masquer l'aperçu
            document.getElementById('selectedPhoto').style.display = 'none';
            
            // Supprimer le champ caché
            const hiddenInput = document.getElementById('selected-image-path');
            if (hiddenInput) {
                hiddenInput.remove();
            }
        }
        
        // Gestion du modal de prévisualisation
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('previewModal');
            const closeBtn = document.querySelector('.preview-modal-close');
            
            // Fermer le modal en cliquant sur la croix
            closeBtn.addEventListener('click', closePreviewModal);
            
            // Fermer le modal en cliquant en dehors
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closePreviewModal();
                }
            });
            
            // Fermer le modal avec la touche Échap
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.style.display === 'block') {
                    closePreviewModal();
                }
            });
        });
    </script>
</body>
</html>
