<?php
/**
 * Script d'optimisation automatique pour les nouvelles images uploadées
 * À exécuter via cron ou manuellement pour traiter les nouvelles images
 */

require_once __DIR__ . '/../admin/includes/image_processor.php';

class AutoImageOptimizer {
    
    private $processor;
    private $galleryDir;
    private $optimizedDir;
    private $logFile;
    
    public function __construct() {
        $this->processor = new ImageProcessor();
        $this->galleryDir = __DIR__ . '/../uploads/gallery/';
        $this->optimizedDir = __DIR__ . '/../uploads/gallery_optimized/';
        $this->logFile = __DIR__ . '/../logs/auto_optimization.log';
        
        // Créer les dossiers nécessaires
        if (!is_dir($this->optimizedDir)) {
            mkdir($this->optimizedDir, 0755, true);
        }
        
        if (!is_dir(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }
    
    /**
     * Traite toutes les nouvelles images non optimisées
     */
    public function processNewImages() {
        $this->log("Début de l'optimisation automatique");
        
        if (!is_dir($this->galleryDir)) {
            $this->log("Dossier galerie non trouvé : {$this->galleryDir}");
            return false;
        }
        
        $files = scandir($this->galleryDir);
        $imageFiles = array_filter($files, function($file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) && $file !== '.' && $file !== '..';
        });
        
        $processed = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($imageFiles as $file) {
            $optimizedFilename = $this->generateOptimizedFilename($file);
            $optimizedPath = $this->optimizedDir . $optimizedFilename;
            
            // Vérifier si l'image optimisée existe déjà
            if (file_exists($optimizedPath)) {
                $skipped++;
                continue;
            }
            
            $sourcePath = $this->galleryDir . $file;
            
            try {
                $result = $this->processImage($sourcePath, $optimizedPath);
                
                if ($result) {
                    $processed++;
                    $this->log("Image optimisée : $file");
                } else {
                    $errors++;
                    $this->log("Erreur lors de l'optimisation : $file", 'ERROR');
                }
                
            } catch (Exception $e) {
                $errors++;
                $this->log("Exception lors de l'optimisation de $file : " . $e->getMessage(), 'ERROR');
            }
        }
        
        $this->log("Optimisation terminée - Traitées: $processed, Ignorées: $skipped, Erreurs: $errors");
        
        return [
            'processed' => $processed,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }
    
    /**
     * Traite une image spécifique
     */
    private function processImage($sourcePath, $targetPath) {
        // Créer un fichier temporaire pour simuler un upload
        $tempFile = [
            'name' => basename($sourcePath),
            'tmp_name' => $sourcePath,
            'size' => filesize($sourcePath),
            'error' => UPLOAD_ERR_OK
        ];
        
        // Traiter avec le preset "member" pour les photos d'équipe
        $result = $this->processor->processWithPreset($tempFile, $this->optimizedDir, 'member', 'gallery');
        
        return $result['success'];
    }
    
    /**
     * Génère le nom de fichier optimisé
     */
    private function generateOptimizedFilename($originalFilename) {
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        
        // Convertir en WebP
        return $basename . '_optimized.webp';
    }
    
    /**
     * Nettoie les images optimisées orphelines
     */
    public function cleanOrphanedOptimized() {
        if (!is_dir($this->optimizedDir)) {
            return 0;
        }
        
        $optimizedFiles = scandir($this->optimizedDir);
        $deleted = 0;
        
        foreach ($optimizedFiles as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            // Extraire le nom original (enlever _optimized.webp)
            $originalName = str_replace('_optimized.webp', '', $file);
            $originalExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            $originalExists = false;
            foreach ($originalExtensions as $ext) {
                if (file_exists($this->galleryDir . $originalName . '.' . $ext)) {
                    $originalExists = true;
                    break;
                }
            }
            
            if (!$originalExists) {
                $optimizedPath = $this->optimizedDir . $file;
                if (unlink($optimizedPath)) {
                    $deleted++;
                    $this->log("Image optimisée orpheline supprimée : $file");
                }
            }
        }
        
        return $deleted;
    }
    
    /**
     * Système de logging
     */
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message\n";
        
        @file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        // Afficher aussi dans la console si en CLI
        if (php_sapi_name() === 'cli') {
            echo $logEntry;
        }
    }
    
    /**
     * Vérifie l'état du système
     */
    public function healthCheck() {
        $checks = [
            'gallery_dir_exists' => is_dir($this->galleryDir),
            'optimized_dir_exists' => is_dir($this->optimizedDir),
            'gallery_writable' => is_writable($this->galleryDir),
            'optimized_writable' => is_writable($this->optimizedDir),
            'processor_available' => class_exists('ImageProcessor')
        ];
        
        return $checks;
    }
}

// Exécution du script
if (php_sapi_name() === 'cli') {
    $optimizer = new AutoImageOptimizer();
    
    $args = $argv ?? [];
    
    if (in_array('--clean', $args)) {
        $deleted = $optimizer->cleanOrphanedOptimized();
        echo "🧹 $deleted images optimisées orphelines supprimées\n";
    } elseif (in_array('--health', $args)) {
        $health = $optimizer->healthCheck();
        echo "🏥 Vérification de santé du système :\n";
        foreach ($health as $check => $status) {
            echo "  $check: " . ($status ? '✅' : '❌') . "\n";
        }
    } else {
        $result = $optimizer->processNewImages();
        echo "📊 Résultat : " . json_encode($result) . "\n";
    }
} else {
    echo "Ce script doit être exécuté en ligne de commande\n";
}
?>
