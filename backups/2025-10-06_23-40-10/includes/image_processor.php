<?php
/**
 * Processeur d'images automatique - VERSION SÉCURISÉE
 * Compression, conversion WebP, gestion d'erreurs robuste
 */

class ImageProcessor {
    
    private $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $max_file_size = 10 * 1024 * 1024; // 10MB (avant optimisation)
    private $quality = 90; // Qualité de compression WebP (augmentée)
    private $max_width = 1920;
    private $max_height = 1080;
    private $log_file;
    private $errors = [];
    
    // Presets d'optimisation (sans crop - l'utilisateur crop avant)
    private $optimization_presets = [
        'hero' => [
            'max_width' => 2560,
            'max_height' => 960, // aligné avec 16:6 environ
            'quality' => 90,
            'description' => 'Image hero - haute qualité - Panoramique (16:6)'
        ],
        'citation' => [
            'max_width' => 1920,
            'max_height' => 1440, // 4:3
            'quality' => 90,
            'description' => 'Image de transition (4:3) - lisibilité renforcée'
        ],
        'member' => [
            'max_width' => 600,
            'max_height' => 800,
            'quality' => 90,
            'description' => 'Photo membre - qualité max - Format 3:4 portrait'
        ],
        'standard' => [
            'max_width' => 1200,
            'max_height' => 1200,
            'quality' => 85,
            'description' => 'Image standard'
        ]
    ];
    
    public function __construct($quality = 85, $max_width = 1920, $max_height = 1080) {
        $this->quality = $quality;
        $this->max_width = $max_width;
        $this->max_height = $max_height;
        $this->log_file = __DIR__ . '/../logs/image_processor.log';
        
        // Vérifier les dépendances système
        $this->checkSystemRequirements();
    }
    
    /**
     * Traite une image avec un preset d'optimisation
     */
    public function processWithPreset($uploaded_file, $target_dir, $preset_name, $prefix = 'img') {
        if (!isset($this->optimization_presets[$preset_name])) {
            return ['success' => false, 'error' => "Preset '$preset_name' non trouvé"];
        }
        
        $preset = $this->optimization_presets[$preset_name];
        $this->logInfo("Utilisation du preset: $preset_name - " . $preset['description']);
        
        // Appliquer les paramètres du preset
        $original_quality = $this->quality;
        $original_max_width = $this->max_width;
        $original_max_height = $this->max_height;
        
        $this->quality = $preset['quality'];
        $this->max_width = $preset['max_width'];
        $this->max_height = $preset['max_height'];
        
        // Traiter l'image
        $result = $this->processImage($uploaded_file, $target_dir, $prefix);
        
        // Restaurer les paramètres
        $this->quality = $original_quality;
        $this->max_width = $original_max_width;
        $this->max_height = $original_max_height;
        
        if ($result['success']) {
            $result['preset_used'] = $preset_name;
            $result['preset_description'] = $preset['description'];
        }
        
        return $result;
    }
    
    /**
     * Vérifie que le système peut traiter les images
     */
    private function checkSystemRequirements() {
        if (!extension_loaded('gd')) {
            $this->logError('CRITICAL: Extension GD non disponible');
            throw new Exception('Extension GD requise pour le traitement des images');
        }
        
        if (!function_exists('imagewebp')) {
            $this->logWarning('WebP non supporté - fallback vers JPEG');
        }
        
        // Vérifier les limites de mémoire
        $memory_limit = ini_get('memory_limit');
        $this->logInfo("Limite mémoire PHP: $memory_limit");
    }
    
    /**
     * Traite une image uploadée - VERSION SÉCURISÉE
     */
    public function processImage($uploaded_file, $target_dir, $prefix = 'processed') {
        $this->errors = [];
        $start_time = microtime(true);
        
        $this->logInfo("Début traitement: " . $uploaded_file['name']);
        
        // Vérifications de base
        $validation = $this->validateFile($uploaded_file);
        if (!$validation['valid']) {
            $this->logError("Validation échouée: " . $validation['error']);
            return ['success' => false, 'error' => $validation['error'], 'errors' => $this->errors];
        }
        
        // Créer le dossier de destination
        if (!is_dir($target_dir)) {
            if (!mkdir($target_dir, 0755, true)) {
                $error = "Impossible de créer le dossier: $target_dir";
                $this->logError($error);
                return ['success' => false, 'error' => $error];
            }
        }
        
        // Vérifier les permissions
        if (!is_writable($target_dir)) {
            $error = "Dossier non accessible en écriture: $target_dir";
            $this->logError($error);
            return ['success' => false, 'error' => $error];
        }
        
        // Générer un nom de fichier unique
        $extension = strtolower(pathinfo($uploaded_file['name'], PATHINFO_EXTENSION));
        $use_webp = function_exists('imagewebp');
        $output_ext = $use_webp ? '.webp' : '.jpg';
        $filename = $prefix . '_' . time() . '_' . uniqid() . $output_ext;
        $target_path = $target_dir . '/' . $filename;
        
        // Sauvegarder temporairement l'image originale si échec
        $backup_path = null;
        
        try {
            // Augmenter la limite de mémoire si nécessaire
            $this->increaseMemoryLimit($uploaded_file['size']);
            
            // Charger l'image selon son type
            $this->logInfo("Chargement de l'image (type: $extension)");
            $image = $this->loadImage($uploaded_file['tmp_name'], $extension);
            
            if (!$image) {
                throw new Exception("Impossible de charger l'image. Format peut-être corrompu.");
            }
            
            $original_width = imagesx($image);
            $original_height = imagesy($image);
            $this->logInfo("Dimensions originales: {$original_width}x{$original_height}");
            
            // Redimensionner si nécessaire
            $resized_image = $this->resizeImage($image);
            $new_width = imagesx($resized_image);
            $new_height = imagesy($resized_image);
            
            if ($new_width !== $original_width || $new_height !== $original_height) {
                $this->logInfo("Image redimensionnée: {$new_width}x{$new_height}");
            }
            
            // Convertir et sauvegarder
            $this->logInfo("Sauvegarde en " . ($use_webp ? "WebP" : "JPEG"));
            $save_result = $this->saveOptimized($resized_image, $target_path, $use_webp);
            
            // FALLBACK : Si WebP échoue, essayer JPEG
            if ((!$save_result || !file_exists($target_path) || filesize($target_path) === 0) && $use_webp) {
                $this->logWarning("WebP a échoué, fallback vers JPEG");
                
                // Supprimer le fichier vide si existe
                if (file_exists($target_path)) {
                    @unlink($target_path);
                }
                
                // Changer extension et réessayer en JPEG
                $target_path = str_replace('.webp', '.jpg', $target_path);
                $filename = str_replace('.webp', '.jpg', $filename);
                $save_result = $this->saveOptimized($resized_image, $target_path, false);
            }
            
            // Nettoyer la mémoire
            imagedestroy($resized_image);
            if ($resized_image !== $image) {
            imagedestroy($image);
            }
            
            // Vérifier que le fichier a bien été créé et n'est pas vide
            if (!$save_result || !file_exists($target_path) || filesize($target_path) === 0) {
                throw new Exception("Échec de sauvegarde - fichier vide ou invalide");
            }
            
            $final_size = filesize($target_path);
            $processing_time = round((microtime(true) - $start_time) * 1000, 2);
            $compression_ratio = round((1 - ($final_size / $uploaded_file['size'])) * 100, 1);
            
            $this->logSuccess(
                "Image traitée avec succès: $filename | " .
                "Taille: " . $this->formatBytes($final_size) . " | " .
                "Compression: {$compression_ratio}% | " .
                "Temps: {$processing_time}ms"
            );
            
                return [
                    'success' => true,
                    'filename' => $filename,
                    'path' => $target_path,
                'size' => $final_size,
                'original_size' => $uploaded_file['size'],
                'compression_ratio' => $compression_ratio,
                'dimensions' => "{$new_width}x{$new_height}",
                'format' => $use_webp ? 'webp' : 'jpeg',
                'processing_time_ms' => $processing_time
            ];
            
        } catch (Exception $e) {
            $error = "Erreur lors du traitement: " . $e->getMessage();
            $this->logError($error);
            
            // Nettoyer le fichier corrompu s'il existe
            if (file_exists($target_path)) {
                unlink($target_path);
                $this->logInfo("Fichier corrompu supprimé: $target_path");
            }
            
            return [
                'success' => false,
                'error' => $error,
                'errors' => $this->errors,
                'debug_info' => [
                    'uploaded_size' => $uploaded_file['size'],
                    'extension' => $extension,
                    'memory_limit' => ini_get('memory_limit')
                ]
            ];
        }
    }
    
    /**
     * Valide le fichier uploadé - VERSION AMÉLIORÉE
     */
    private function validateFile($file) {
        // Vérifier que c'est bien un upload
        if (!isset($file['tmp_name']) || !isset($file['error'])) {
            return ['valid' => false, 'error' => 'Fichier uploadé invalide'];
        }
        
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => 'Fichier trop grand (limite serveur)',
                UPLOAD_ERR_FORM_SIZE => 'Fichier trop grand (limite formulaire)',
                UPLOAD_ERR_PARTIAL => 'Upload incomplet',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier uploadé',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire sur le disque',
                UPLOAD_ERR_EXTENSION => 'Extension PHP a stoppé l\'upload'
            ];
            
            $error = $error_messages[$file['error']] ?? 'Erreur d\'upload inconnue';
            return ['valid' => false, 'error' => $error];
        }
        
        // Vérifier que le fichier existe
        if (!file_exists($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Fichier temporaire introuvable'];
        }
        
        // Vérifier la taille
        if ($file['size'] === 0) {
            return ['valid' => false, 'error' => 'Fichier vide'];
        }
        
        if ($file['size'] > $this->max_file_size) {
            $max_size = $this->formatBytes($this->max_file_size);
            return ['valid' => false, 'error' => "Fichier trop grand (max: $max_size)"];
        }
        
        // Vérifier le type MIME (sécurité)
        if (!function_exists('finfo_open')) {
            $this->logWarning('finfo_open non disponible - validation MIME limitée');
        } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowed_mimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ];
        
            if (!in_array($mime_type, $allowed_mimes)) {
                return ['valid' => false, 'error' => "Type de fichier non autorisé: $mime_type"];
            }
        }
        
        // Vérifier l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowed_types)) {
            return ['valid' => false, 'error' => "Extension non autorisée: $extension"];
        }
        
        // Vérifier que c'est une vraie image (sécurité anti-injection)
        $image_info = @getimagesize($file['tmp_name']);
        if ($image_info === false) {
            return ['valid' => false, 'error' => 'Fichier corrompu ou n\'est pas une image'];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Charge une image selon son type
     */
    private function loadImage($file_path, $extension) {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg($file_path);
            case 'png':
                return imagecreatefrompng($file_path);
            case 'gif':
                return imagecreatefromgif($file_path);
            case 'webp':
                return imagecreatefromwebp($file_path);
            default:
                return false;
        }
    }
    
    /**
     * Redimensionne l'image si nécessaire
     */
    private function resizeImage($image) {
        $original_width = imagesx($image);
        $original_height = imagesy($image);
        
        // Si l'image est déjà plus petite que les dimensions max, la retourner telle quelle
        if ($original_width <= $this->max_width && $original_height <= $this->max_height) {
            return $image;
        }
        
        // Calculer les nouvelles dimensions en gardant les proportions
        $ratio = min($this->max_width / $original_width, $this->max_height / $original_height);
        $new_width = intval($original_width * $ratio);
        $new_height = intval($original_height * $ratio);
        
        // Créer une nouvelle image redimensionnée
        $resized = imagecreatetruecolor($new_width, $new_height);
        
        // Préserver la transparence pour PNG
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefill($resized, 0, 0, $transparent);
        
        // Redimensionner
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
        
        // Nettoyer l'image originale
        imagedestroy($image);
        
        return $resized;
    }
    
    /**
     * Sauvegarde l'image optimisée avec vérifications et gestion PNG
     */
    private function saveOptimized($image, $file_path, $use_webp = true) {
        // Vérifier que l'image est valide
        if (!is_resource($image) && !($image instanceof \GdImage)) {
            $this->logError("Ressource image invalide pour la sauvegarde");
            return false;
        }
        
        // Vérifier les permissions du dossier
        $dir = dirname($file_path);
        if (!is_writable($dir)) {
            $this->logError("Dossier non accessible en écriture: $dir");
            return false;
        }
        
        // Pour WebP : Convertir en true color sans transparence (fix pour PNG)
        if ($use_webp && function_exists('imagewebp')) {
            $this->logInfo("Préparation pour WebP (suppression transparence PNG si présente)");
            
            // Créer une image RGB sans alpha
            $width = imagesx($image);
            $height = imagesy($image);
            $rgb_image = imagecreatetruecolor($width, $height);
            
            // Remplir avec du blanc (au lieu de transparent)
            $white = imagecolorallocate($rgb_image, 255, 255, 255);
            imagefill($rgb_image, 0, 0, $white);
            
            // Copier l'image par-dessus
            imagecopy($rgb_image, $image, 0, 0, 0, 0, $width, $height);
            
            // Sauvegarder l'image RGB en WebP
            $result = @imagewebp($rgb_image, $file_path, $this->quality);
            
            // Nettoyer l'image temporaire
            imagedestroy($rgb_image);
            
            if (!$result) {
                $this->logError("imagewebp() a retourné false malgré préparation RGB");
                return false;
            }
        } else {
            // Sauvegarder en JPEG
            $result = @imagejpeg($image, $file_path, $this->quality);
            if (!$result) {
                $this->logError("imagejpeg() a retourné false");
                return false;
            }
        }
        
        // Double vérification : le fichier existe et n'est pas vide
        if (file_exists($file_path)) {
            clearstatcache(true, $file_path); // Forcer rafraîchissement du cache
            $filesize = filesize($file_path);
            
            if ($filesize === 0 || $filesize === false) {
                $this->logError("Fichier créé mais vide (0 octets)");
                @unlink($file_path);
                return false;
            }
            
            $this->logInfo("Fichier sauvegardé avec succès: " . $this->formatBytes($filesize));
            return true;
        }
        
        $this->logError("Fichier non créé sur le disque");
        return false;
    }
    
    /**
     * Augmente la limite de mémoire si nécessaire
     */
    private function increaseMemoryLimit($file_size) {
        $current_limit = ini_get('memory_limit');
        $current_bytes = $this->parseMemoryLimit($current_limit);
        
        // Estimer la mémoire nécessaire (image * 5 pour sécurité)
        $needed_memory = $file_size * 5;
        
        if ($current_bytes < $needed_memory) {
            $new_limit = ceil($needed_memory / 1024 / 1024) . 'M';
            @ini_set('memory_limit', $new_limit);
            $this->logInfo("Limite mémoire augmentée: $current_limit → $new_limit");
        }
    }
    
    /**
     * Parse une limite de mémoire PHP en octets
     */
    private function parseMemoryLimit($limit) {
        if ($limit == -1) {
            return PHP_INT_MAX;
        }
        
        $unit = strtolower(substr($limit, -1));
        $value = (int) $limit;
        
        switch ($unit) {
            case 'g': $value *= 1024;
            case 'm': $value *= 1024;
            case 'k': $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Formate une taille en octets de manière lisible
     */
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * SYSTÈME DE LOGGING COMPLET
     */
    private function log($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$level] $message\n";
        
        // Créer le dossier de logs si nécessaire
        $log_dir = dirname($this->log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        // Écrire dans le fichier de log
        @file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // Ajouter aux erreurs si c'est une erreur
        if ($level === 'ERROR' || $level === 'CRITICAL') {
            $this->errors[] = $message;
        }
    }
    
    private function logInfo($message) {
        $this->log('INFO', $message);
    }
    
    private function logWarning($message) {
        $this->log('WARNING', $message);
    }
    
    private function logError($message) {
        $this->log('ERROR', $message);
    }
    
    private function logSuccess($message) {
        $this->log('SUCCESS', $message);
    }
    
    /**
     * Récupère les erreurs accumulées
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Test de santé du système
     */
    public function healthCheck() {
        $checks = [];
        
        // Extension GD
        $checks['gd_available'] = extension_loaded('gd');
        $checks['webp_support'] = function_exists('imagewebp');
        $checks['jpeg_support'] = function_exists('imagejpeg');
        $checks['png_support'] = function_exists('imagepng');
        
        // Limites système
        $checks['memory_limit'] = ini_get('memory_limit');
        $checks['upload_max_filesize'] = ini_get('upload_max_filesize');
        $checks['post_max_size'] = ini_get('post_max_size');
        
        // Dossier de logs
        $log_dir = dirname($this->log_file);
        $checks['log_dir_writable'] = is_writable($log_dir) || @mkdir($log_dir, 0755, true);
        
        return $checks;
    }
    
    /**
     * Crée une miniature
     */
    public function createThumbnail($source_path, $target_dir, $max_width = 300, $max_height = 200) {
        if (!file_exists($source_path)) {
            return false;
        }
        
        $extension = strtolower(pathinfo($source_path, PATHINFO_EXTENSION));
        $image = $this->loadImage($source_path, $extension);
        
        if (!$image) {
            return false;
        }
        
        $original_width = imagesx($image);
        $original_height = imagesy($image);
        
        // Calculer les dimensions de la miniature
        $ratio = min($max_width / $original_width, $max_height / $original_height);
        $thumb_width = intval($original_width * $ratio);
        $thumb_height = intval($original_height * $ratio);
        
        // Créer la miniature
        $thumbnail = imagecreatetruecolor($thumb_width, $thumb_height);
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        
        // Redimensionner
        imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $original_width, $original_height);
        
        // Générer le nom de fichier de la miniature
        $thumb_filename = 'thumb_' . basename($source_path, '.webp') . '.webp';
        $thumb_path = $target_dir . '/' . $thumb_filename;
        
        // Sauvegarder
        $result = $this->saveOptimized($thumbnail, $thumb_path, true);
        
        // Nettoyer
        imagedestroy($image);
        imagedestroy($thumbnail);
        
        return $result ? $thumb_filename : false;
    }
}
?>
