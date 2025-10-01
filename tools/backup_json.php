<?php
/**
 * Script de sauvegarde automatique du JSON
 * À exécuter avant toute modification du site_content.json
 */

function backupJSON() {
    $jsonFile = __DIR__ . '/data/site_content.json';
    $backupDir = __DIR__ . '/backups/';
    
    // Créer le dossier backups s'il n'existe pas
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    // Nom du fichier de sauvegarde avec timestamp
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $backupDir . "site_content_backup_{$timestamp}.json";
    
    // Vérifier que le fichier JSON existe
    if (!file_exists($jsonFile)) {
        return ['success' => false, 'error' => 'Fichier JSON introuvable'];
    }
    
    // Créer la sauvegarde
    if (copy($jsonFile, $backupFile)) {
        return ['success' => true, 'backup_file' => $backupFile];
    } else {
        return ['success' => false, 'error' => 'Impossible de créer la sauvegarde'];
    }
}

// Fonction pour restaurer depuis une sauvegarde
function restoreFromBackup($backupFile) {
    $jsonFile = __DIR__ . '/data/site_content.json';
    
    if (!file_exists($backupFile)) {
        return ['success' => false, 'error' => 'Fichier de sauvegarde introuvable'];
    }
    
    if (copy($backupFile, $jsonFile)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => 'Impossible de restaurer la sauvegarde'];
    }
}

// Fonction pour lister les sauvegardes disponibles
function listBackups() {
    $backupDir = __DIR__ . '/backups/';
    
    if (!is_dir($backupDir)) {
        return [];
    }
    
    $backups = [];
    $files = glob($backupDir . 'site_content_backup_*.json');
    
    foreach ($files as $file) {
        $backups[] = [
            'file' => basename($file),
            'path' => $file,
            'date' => date('Y-m-d H:i:s', filemtime($file)),
            'size' => filesize($file)
        ];
    }
    
    // Trier par date décroissante
    usort($backups, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return $backups;
}

// Si appelé en ligne de commande
if (php_sapi_name() === 'cli') {
    $action = $argv[1] ?? 'backup';
    
    switch ($action) {
        case 'backup':
            $result = backupJSON();
            if ($result['success']) {
                echo "✅ Sauvegarde créée : " . $result['backup_file'] . "\n";
            } else {
                echo "❌ Erreur : " . $result['error'] . "\n";
            }
            break;
            
        case 'list':
            $backups = listBackups();
            echo "📋 Sauvegardes disponibles :\n";
            foreach ($backups as $backup) {
                echo "- {$backup['file']} ({$backup['date']}) - " . number_format($backup['size']) . " bytes\n";
            }
            break;
            
        case 'restore':
            $backupFile = $argv[2] ?? '';
            if (empty($backupFile)) {
                echo "❌ Usage: php backup_json.php restore <fichier_backup>\n";
                exit(1);
            }
            $result = restoreFromBackup(__DIR__ . '/backups/' . $backupFile);
            if ($result['success']) {
                echo "✅ Sauvegarde restaurée avec succès\n";
            } else {
                echo "❌ Erreur : " . $result['error'] . "\n";
            }
            break;
            
        default:
            echo "Usage: php backup_json.php [backup|list|restore]\n";
    }
}
?>
