<?php
/**
 * Script de traitement en lot pour optimiser toutes les images de la galerie
 * Usage: php tools/batch_optimize_gallery.php
 */

require_once __DIR__ . '/../admin/includes/image_processor.php';

class GalleryBatchOptimizer {
    
    private $processor;
    private $galleryDir;
    private $optimizedDir;
    private $stats = [
        'processed' => 0,
        'skipped' => 0,
        'errors' => 0,
        'total_size_before' => 0,
        'total_size_after' => 0
    ];
    
    public function __construct() {
        $this->processor = new ImageProcessor();
        $this->galleryDir = __DIR__ . '/../uploads/gallery/';
        $this->optimizedDir = __DIR__ . '/../uploads/gallery_optimized/';
        
        // Créer le dossier optimisé s'il n'existe pas
        if (!is_dir($this->optimizedDir)) {
            mkdir($this->optimizedDir, 0755, true);
            echo "📁 Dossier optimisé créé : {$this->optimizedDir}\n";
        }
    }
    
    public function processAll() {
        echo "🚀 Début de l'optimisation en lot de la galerie\n";
        echo "📂 Dossier source : {$this->galleryDir}\n";
        echo "📂 Dossier optimisé : {$this->optimizedDir}\n\n";
        
        if (!is_dir($this->galleryDir)) {
            echo "❌ Erreur : Le dossier galerie n'existe pas\n";
            return false;
        }
        
        $files = scandir($this->galleryDir);
        $imageFiles = array_filter($files, function($file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) && $file !== '.' && $file !== '..';
        });
        
        if (empty($imageFiles)) {
            echo "ℹ️  Aucune image trouvée dans la galerie\n";
            return true;
        }
        
        echo "📊 " . count($imageFiles) . " images trouvées\n\n";
        
        foreach ($imageFiles as $file) {
            $this->processImage($file);
        }
        
        $this->displayStats();
        return true;
    }
    
    private function processImage($filename) {
        $sourcePath = $this->galleryDir . $filename;
        $optimizedFilename = $this->generateOptimizedFilename($filename);
        $targetPath = $this->optimizedDir . $optimizedFilename;
        
        // Vérifier si l'image optimisée existe déjà
        if (file_exists($targetPath)) {
            echo "⏭️  Déjà optimisée : $filename\n";
            $this->stats['skipped']++;
            return;
        }
        
        // Créer un fichier temporaire pour simuler un upload
        $tempFile = [
            'name' => $filename,
            'tmp_name' => $sourcePath,
            'size' => filesize($sourcePath),
            'error' => UPLOAD_ERR_OK
        ];
        
        $this->stats['total_size_before'] += $tempFile['size'];
        
        echo "🔄 Traitement : $filename (" . $this->formatBytes($tempFile['size']) . ")";
        
        try {
            // Traiter avec le preset "member" pour les photos d'équipe
            $result = $this->processor->processWithPreset($tempFile, $this->optimizedDir, 'member', 'gallery');
            
            if ($result['success']) {
                $this->stats['total_size_after'] += $result['size'];
                $compression = round((1 - ($result['size'] / $tempFile['size'])) * 100, 1);
                echo " ✅ " . $this->formatBytes($result['size']) . " (-{$compression}%)\n";
                $this->stats['processed']++;
            } else {
                echo " ❌ Erreur : " . $result['error'] . "\n";
                $this->stats['errors']++;
            }
            
        } catch (Exception $e) {
            echo " ❌ Exception : " . $e->getMessage() . "\n";
            $this->stats['errors']++;
        }
    }
    
    private function generateOptimizedFilename($originalFilename) {
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        
        // Convertir en WebP
        return $basename . '_optimized.webp';
    }
    
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    private function displayStats() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 STATISTIQUES D'OPTIMISATION\n";
        echo str_repeat("=", 50) . "\n";
        echo "✅ Images traitées : {$this->stats['processed']}\n";
        echo "⏭️  Images ignorées : {$this->stats['skipped']}\n";
        echo "❌ Erreurs : {$this->stats['errors']}\n";
        echo "📦 Taille avant : " . $this->formatBytes($this->stats['total_size_before']) . "\n";
        echo "📦 Taille après : " . $this->formatBytes($this->stats['total_size_after']) . "\n";
        
        if ($this->stats['total_size_before'] > 0) {
            $totalCompression = round((1 - ($this->stats['total_size_after'] / $this->stats['total_size_before'])) * 100, 1);
            echo "💾 Compression totale : -{$totalCompression}%\n";
        }
        
        echo "\n🎉 Optimisation terminée !\n";
        echo "📂 Images optimisées disponibles dans : {$this->optimizedDir}\n";
    }
    
    /**
     * Nettoie les anciennes images optimisées
     */
    public function cleanOldOptimized() {
        if (!is_dir($this->optimizedDir)) {
            return;
        }
        
        $files = scandir($this->optimizedDir);
        $deleted = 0;
        
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && is_file($this->optimizedDir . $file)) {
                unlink($this->optimizedDir . $file);
                $deleted++;
            }
        }
        
        echo "🧹 $deleted anciennes images optimisées supprimées\n";
    }
}

// Exécution du script
if (php_sapi_name() === 'cli') {
    $optimizer = new GalleryBatchOptimizer();
    
    // Vérifier les arguments
    $args = $argv ?? [];
    if (in_array('--clean', $args)) {
        $optimizer->cleanOldOptimized();
    }
    
    $optimizer->processAll();
} else {
    echo "Ce script doit être exécuté en ligne de commande\n";
}
?>
