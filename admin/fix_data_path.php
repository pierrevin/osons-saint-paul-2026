<?php
/**
 * Script de correction DATA_PATH - Correction finale
 * 
 * Ce script corrige le fichier config.php pour définir correctement DATA_PATH
 * 
 * ⚠️ SUPPRIMEZ ce fichier après usage !
 */

echo '<h2>🔧 Correction DATA_PATH - Version finale</h2>';
echo '<hr>';

$config_file = __DIR__ . '/config.php';

// Vérifier si config.php existe
if (!file_exists($config_file)) {
    echo '❌ <strong>Erreur :</strong> Le fichier config.php n\'existe pas !';
    exit;
}

// Créer le nouveau contenu correct
$new_config_content = '<?php
// Définition des constantes de base
define(\'ROOT_PATH\', __DIR__ . \'/..\');
define(\'DATA_PATH\', ROOT_PATH . \'/data-osons\');
define(\'UPLOADS_PATH\', ROOT_PATH . \'/uploads\');
define(\'IMAGES_PATH\', ROOT_PATH . \'/uploads\');

// Configuration des logs
$log_file = DATA_PATH . \'/admin_log.json\';

// Fonctions utilitaires
function saveJsonFile($filename, $data) {
    global $log_file;
    $file_path = DATA_PATH . \'/\' . $filename;
    
    // Créer une sauvegarde
    $backup_path = DATA_PATH . \'/backups/\' . $filename . \'.\' . date(\'Y-m-d-H-i-s\') . \'.json\';
    if (file_exists($file_path)) {
        copy($file_path, $backup_path);
    }
    
    return file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function loadJsonFile($filename) {
    $file_path = DATA_PATH . \'/\' . $filename;
    if (file_exists($file_path)) {
        return json_decode(file_get_contents($file_path), true);
    }
    return [];
}

// Fonction d\'authentification
function check_auth() {
    if (!isset($_SESSION[\'admin_logged_in\']) || $_SESSION[\'admin_logged_in\'] !== true) {
        header(\'Location: ../login.php\');
        exit;
    }
}
?>';

// Sauvegarder l'ancien fichier
$backup_file = $config_file . '.backup.' . date('Y-m-d-H-i-s');
copy($config_file, $backup_file);

// Écrire le nouveau fichier
if (file_put_contents($config_file, $new_config_content)) {
    echo '✅ <strong>Fichier config.php corrigé avec succès !</strong><br>';
    echo '✅ Sauvegarde créée : ' . basename($backup_file) . '<br><br>';
    
    // Vérifier que DATA_PATH est maintenant défini
    include_once $config_file;
    
    if (defined('DATA_PATH')) {
        echo '✅ DATA_PATH défini : ' . DATA_PATH . '<br>';
        
        // Créer le dossier s'il n'existe pas
        if (!is_dir(DATA_PATH)) {
            if (mkdir(DATA_PATH, 0755, true)) {
                echo '✅ Dossier DATA_PATH créé<br>';
            }
        } else {
            echo '✅ Dossier DATA_PATH existe<br>';
        }
        
        // Créer site_content.json s'il n'existe pas
        $site_content_file = DATA_PATH . '/site_content.json';
        if (!file_exists($site_content_file)) {
            $empty_content = [
                'hero' => ['title' => '', 'subtitle' => '', 'background_image' => ''],
                'programme' => ['title' => '', 'subtitle' => '', 'proposals' => []],
                'equipe' => ['title' => '', 'subtitle' => '', 'members' => []],
                'rendez_vous' => ['title' => '', 'subtitle' => '', 'events' => []],
                'charte' => ['title' => '', 'content' => ''],
                'contact' => ['title' => '', 'subtitle' => '', 'content' => ''],
                'citations' => [
                    'citation1' => ['text' => '', 'author' => '', 'background_image' => ''],
                    'citation2' => ['text' => '', 'author' => '', 'background_image' => ''],
                    'citation3' => ['text' => '', 'author' => '', 'background_image' => ''],
                    'citation4' => ['text' => '', 'author' => '', 'background_image' => '']
                ]
            ];
            
            if (file_put_contents($site_content_file, json_encode($empty_content, JSON_PRETTY_PRINT))) {
                echo '✅ site_content.json créé<br>';
            }
        } else {
            echo '✅ site_content.json existe<br>';
        }
    }
    
    echo '<hr>';
    echo '<h3>🎉 Correction terminée avec succès !</h3>';
    echo '<p><strong>Vous pouvez maintenant :</strong></p>';
    echo '<ul>';
    echo '<li>✅ <a href="pages/schema_admin_new.php">Accéder à l\'administration</a></li>';
    echo '<li>✅ Toutes les fonctionnalités devraient fonctionner</li>';
    echo '</ul>';
    
} else {
    echo '❌ <strong>Erreur :</strong> Impossible de modifier config.php<br>';
    echo 'Vérifiez les permissions du fichier.';
}

echo '<hr>';
echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">';
echo '<h4>⚠️ SÉCURITÉ IMPORTANTE :</h4>';
echo '<p><strong>SUPPRIMEZ ce fichier fix_data_path.php maintenant !</strong></p>';
echo '</div>';

echo '<hr>';
echo '<p><small>Script de correction finale - Osons Saint-Paul 2026</small></p>';
?>
