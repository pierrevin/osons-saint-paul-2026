<?php
/**
 * Page d'administration pour l'optimisation de la galerie d'images
 */

require_once __DIR__ . '/../../admin/includes/user_manager.php';
require_once __DIR__ . '/../../tools/batch_optimize_gallery.php';
require_once __DIR__ . '/../../tools/auto_optimize_new_images.php';

// Vérifier l'authentification
if (!UserManager::isAuthenticated()) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$messageType = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'batch_optimize':
                $optimizer = new GalleryBatchOptimizer();
                $result = $optimizer->processAll();
                $message = "Optimisation en lot terminée avec succès !";
                $messageType = 'success';
                break;
                
            case 'clean_optimized':
                $optimizer = new GalleryBatchOptimizer();
                $optimizer->cleanOldOptimized();
                $message = "Nettoyage des anciennes images optimisées terminé !";
                $messageType = 'success';
                break;
                
            case 'auto_optimize':
                $autoOptimizer = new AutoImageOptimizer();
                $result = $autoOptimizer->processNewImages();
                $message = "Optimisation automatique terminée : " . json_encode($result);
                $messageType = 'success';
                break;
                
            case 'clean_orphaned':
                $autoOptimizer = new AutoImageOptimizer();
                $deleted = $autoOptimizer->cleanOrphanedOptimized();
                $message = "$deleted images optimisées orphelines supprimées !";
                $messageType = 'success';
                break;
                
            default:
                throw new Exception('Action non reconnue');
        }
    } catch (Exception $e) {
        $message = "Erreur : " . $e->getMessage();
        $messageType = 'error';
    }
}

// Obtenir les statistiques
function getGalleryStats() {
    $galleryDir = __DIR__ . '/../../uploads/gallery/';
    $optimizedDir = __DIR__ . '/../../uploads/gallery_optimized/';
    
    $stats = [
        'original_count' => 0,
        'original_size' => 0,
        'optimized_count' => 0,
        'optimized_size' => 0
    ];
    
    // Compter les images originales
    if (is_dir($galleryDir)) {
        $files = scandir($galleryDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $stats['original_count']++;
                    $stats['original_size'] += filesize($galleryDir . $file);
                }
            }
        }
    }
    
    // Compter les images optimisées
    if (is_dir($optimizedDir)) {
        $files = scandir($optimizedDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, ['webp', 'jpg', 'jpeg', 'png', 'gif'])) {
                    $stats['optimized_count']++;
                    $stats['optimized_size'] += filesize($optimizedDir . $file);
                }
            }
        }
    }
    
    return $stats;
}

function formatBytes($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

$stats = getGalleryStats();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Optimisation Galerie - Admin</title>
    <link href="../assets/css/admin.css" rel="stylesheet">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #ec654f;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .action-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .action-card h3 {
            margin-bottom: 1rem;
            color: #333;
        }
        
        .action-card p {
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .btn {
            background: #ec654f;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        
        .btn:hover {
            background: #d55a47;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
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
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .info-box h4 {
            color: #0066cc;
            margin-bottom: 0.5rem;
        }
        
        .info-box p {
            color: #333;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>🖼️ Optimisation Galerie d'Images</h1>
            <a href="../index.php" class="btn btn-secondary">← Retour Dashboard</a>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>
        
        <div class="info-box">
            <h4>💡 Comment ça marche ?</h4>
            <p>Ce système optimise automatiquement les images de la galerie pour améliorer les performances. Les images sont converties en WebP, redimensionnées et compressées. Le formulaire équipe utilise ensuite ces images optimisées pour un traitement plus rapide.</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['original_count'] ?></div>
                <div class="stat-label">Images originales</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= formatBytes($stats['original_size']) ?></div>
                <div class="stat-label">Taille originale</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['optimized_count'] ?></div>
                <div class="stat-label">Images optimisées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= formatBytes($stats['optimized_size']) ?></div>
                <div class="stat-label">Taille optimisée</div>
            </div>
        </div>
        
        <div class="actions-grid">
            <div class="action-card">
                <h3>🚀 Optimisation en lot</h3>
                <p>Traite toutes les images de la galerie et les optimise (WebP, redimensionnement, compression).</p>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="batch_optimize">
                    <button type="submit" class="btn">Optimiser toutes les images</button>
                </form>
            </div>
            
            <div class="action-card">
                <h3>🔄 Optimisation automatique</h3>
                <p>Traite uniquement les nouvelles images qui n'ont pas encore été optimisées.</p>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="auto_optimize">
                    <button type="submit" class="btn">Optimiser les nouvelles</button>
                </form>
            </div>
            
            <div class="action-card">
                <h3>🧹 Nettoyer les orphelines</h3>
                <p>Supprime les images optimisées dont l'original n'existe plus.</p>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="clean_orphaned">
                    <button type="submit" class="btn btn-warning">Nettoyer</button>
                </form>
            </div>
            
            <div class="action-card">
                <h3>🗑️ Vider le cache optimisé</h3>
                <p>Supprime toutes les images optimisées (nécessite une nouvelle optimisation).</p>
                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer toutes les images optimisées ?')">
                    <input type="hidden" name="action" value="clean_optimized">
                    <button type="submit" class="btn btn-secondary">Vider le cache</button>
                </form>
            </div>
        </div>
        
        <div class="info-box">
            <h4>📋 Recommandations</h4>
            <ul style="margin: 0; padding-left: 1.5rem;">
                <li>Exécutez l'optimisation en lot une seule fois pour traiter toutes les images existantes</li>
                <li>Utilisez l'optimisation automatique régulièrement pour les nouvelles images</li>
                <li>Le nettoyage des orphelines peut être fait périodiquement</li>
                <li>Les images optimisées sont automatiquement utilisées par le formulaire équipe</li>
            </ul>
        </div>
    </div>
</body>
</html>
