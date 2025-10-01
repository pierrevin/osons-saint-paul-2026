<?php
/**
 * Test du processeur d'images sécurisé
 * Lance un diagnostic complet du système
 */

require_once __DIR__ . '/../admin/includes/image_processor.php';

echo "=== TEST DU PROCESSEUR D'IMAGES ===\n\n";

try {
    // Créer une instance
    $processor = new ImageProcessor(85, 1920, 1080);
    echo "✅ ImageProcessor initialisé\n\n";
    
    // Test de santé
    echo "--- Health Check ---\n";
    $health = $processor->healthCheck();
    
    foreach ($health as $check => $result) {
        $status = is_bool($result) ? ($result ? '✅' : '❌') : $result;
        $check_name = str_replace('_', ' ', ucfirst($check));
        echo "$check_name: $status\n";
    }
    
    echo "\n";
    
    // Vérifier les extensions critiques
    $critical_checks = ['gd_available', 'jpeg_support'];
    $all_good = true;
    
    foreach ($critical_checks as $check) {
        if (!$health[$check]) {
            echo "❌ CRITICAL: $check manquant\n";
            $all_good = false;
        }
    }
    
    // Warnings pour fonctionnalités optionnelles
    if (!$health['webp_support']) {
        echo "⚠️  WARNING: WebP non supporté - fallback vers JPEG\n";
    }
    
    echo "\n";
    
    if ($all_good) {
        echo "✅ Toutes les vérifications critiques sont OK\n";
        echo "📁 Le système est prêt à traiter des images\n";
    } else {
        echo "❌ Certaines fonctionnalités critiques sont manquantes\n";
        echo "🔧 Veuillez installer l'extension GD pour PHP\n";
    }
    
    echo "\n--- Limites système ---\n";
    echo "Memory Limit: " . $health['memory_limit'] . "\n";
    echo "Upload Max Filesize: " . $health['upload_max_filesize'] . "\n";
    echo "Post Max Size: " . $health['post_max_size'] . "\n";
    
    echo "\n--- Dossiers ---\n";
    $uploads_dir = __DIR__ . '/../uploads';
    $logs_dir = __DIR__ . '/../admin/logs';
    
    echo "Uploads dir writable: " . (is_writable($uploads_dir) ? '✅' : '❌') . "\n";
    echo "Logs dir writable: " . ($health['log_dir_writable'] ? '✅' : '❌') . "\n";
    
    echo "\n=== FIN DU TEST ===\n";
    
} catch (Exception $e) {
    echo "❌ ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}
?>

