<?php
header('Content-Type: application/json');

try {
    // Priorité aux images optimisées
    $optimizedDir = __DIR__ . '/uploads/gallery_optimized/';
    $fallbackDir = __DIR__ . '/uploads/gallery/';
    $images = [];
    
    // D'abord, chercher dans le dossier optimisé
    if (is_dir($optimizedDir)) {
        $files = scandir($optimizedDir);
        $allowedExtensions = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $optimizedDir . $file;
            
            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                
                if (in_array($extension, $allowedExtensions)) {
                    $images[] = [
                        'name' => pathinfo($file, PATHINFO_FILENAME),
                        'path' => 'uploads/gallery_optimized/' . $file,
                        'size' => filesize($filePath),
                        'modified' => filemtime($filePath),
                        'optimized' => true
                    ];
                }
            }
        }
    }
    
    // Si aucune image optimisée trouvée, fallback vers la galerie originale
    if (empty($images) && is_dir($fallbackDir)) {
        $files = scandir($fallbackDir);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $fallbackDir . $file;
            
            if (is_file($filePath)) {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                
                if (in_array($extension, $allowedExtensions)) {
                    $images[] = [
                        'name' => pathinfo($file, PATHINFO_FILENAME),
                        'path' => 'uploads/gallery/' . $file,
                        'size' => filesize($filePath),
                        'modified' => filemtime($filePath),
                        'optimized' => false
                    ];
                }
            }
        }
    }
    
    // Trier par ordre alphabétique (nom de fichier)
    usort($images, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    
    echo json_encode($images);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
