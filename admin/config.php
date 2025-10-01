<?php
// Configuration de l'interface admin

// Configuration de base
define('ADMIN_TITLE', 'Administration - Osons Saint-Paul');
define('SITE_URL', 'https://votre-site.com');
define('ADMIN_EMAIL', 'admin@osonssaintpaul.fr');

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('DATA_PATH', ROOT_PATH . '/data');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('IMAGES_PATH', UPLOADS_PATH . '/images');

// Créer les dossiers s'ils n'existent pas
$directories = [DATA_PATH, UPLOADS_PATH, IMAGES_PATH];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Configuration de sécurité
define('SESSION_TIMEOUT', 3600); // 1 heure
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Configuration des images
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('IMAGE_QUALITY', 85);

// Configuration de l'upload
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Fonctions utilitaires
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function log_activity($action, $details = '') {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user' => $_SESSION['admin_user'] ?? 'Unknown',
        'action' => $action,
        'details' => $details,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ];
    
    $log_file = DATA_PATH . '/admin_log.json';
    $logs = [];
    
    if (file_exists($log_file)) {
        $logs = json_decode(file_get_contents($log_file), true) ?: [];
    }
    
    $logs[] = $log_entry;
    
    // Garder seulement les 1000 dernières entrées
    if (count($logs) > 1000) {
        $logs = array_slice($logs, -1000);
    }
    
    file_put_contents($log_file, json_encode($logs, JSON_PRETTY_PRINT));
}

function get_json_data($filename) {
    $file_path = DATA_PATH . '/' . $filename;
    if (file_exists($file_path)) {
        return json_decode(file_get_contents($file_path), true) ?: [];
    }
    return [];
}

function save_json_data($filename, $data) {
    $file_path = DATA_PATH . '/' . $filename;
    $backup_path = DATA_PATH . '/backups/' . $filename . '.' . date('Y-m-d-H-i-s') . '.json';
    
    // VALIDATION ET SÉCURISATION
    // 1. Vérifier que les données ne sont pas vides
    if (empty($data)) {
        error_log("ERREUR: Tentative de sauvegarde de données vides pour $filename");
        return false;
    }
    
    // 2. Créer une sauvegarde automatique avant modification
    if (file_exists($file_path)) {
        $backup_dir = dirname($backup_path);
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        if (!copy($file_path, $backup_path)) {
            error_log("ERREUR: Impossible de créer la sauvegarde pour $filename");
            return false;
        }
    }
    
    // 3. Convertir les données en JSON avec validation
    $json_data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // 4. Vérifier que l'encodage JSON a réussi
    if ($json_data === false) {
        error_log("ERREUR: Échec de l'encodage JSON pour $filename");
        return false;
    }
    
    // 5. Valider que le JSON est syntaxiquement correct
    $test_decode = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("ERREUR: JSON invalide généré pour $filename - " . json_last_error_msg());
        return false;
    }
    
    // 6. Écrire dans le fichier avec vérification
    if (file_put_contents($file_path, $json_data) === false) {
        error_log("ERREUR: Impossible d'écrire dans $filename");
        return false;
    }
    
    // 7. Vérifier que le fichier a été écrit correctement
    if (!file_exists($file_path) || filesize($file_path) === 0) {
        error_log("ERREUR: Fichier $filename vide ou inexistant après écriture");
        return false;
    }
    
    // 8. Test final : lire et décoder le fichier pour vérifier l'intégrité
    $final_test = json_decode(file_get_contents($file_path), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("ERREUR: Fichier $filename corrompu après écriture");
        return false;
    }
    
    return true;
}

function check_auth() {
    // AUTHENTIFICATION TEMPORAIREMENT DÉSACTIVÉE
    // TODO: Réactiver l'authentification plus tard
    return true;
    
    // Démarrer la session si elle n'est pas déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login');
        exit;
    }
    
    // Vérifier le timeout de session
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: login?timeout=1');
        exit;
    }
    
    // Mettre à jour le temps de dernière activité
    $_SESSION['login_time'] = time();
}

function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}


function get_image_info($file_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $info = getimagesize($file_path);
    if (!$info) {
        return false;
    }
    
    return [
        'width' => $info[0],
        'height' => $info[1],
        'type' => $info[2],
        'mime' => $info['mime'],
        'size' => filesize($file_path)
    ];
}

function resize_image($source_path, $destination_path, $max_width, $max_height) {
    $image_info = get_image_info($source_path);
    if (!$image_info) {
        return false;
    }
    
    $source_width = $image_info['width'];
    $source_height = $image_info['height'];
    
    // Calculer les nouvelles dimensions en gardant les proportions
    $ratio = min($max_width / $source_width, $max_height / $source_height);
    $new_width = intval($source_width * $ratio);
    $new_height = intval($source_height * $ratio);
    
    // Créer l'image source selon le type
    switch ($image_info['type']) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }
    
    if (!$source_image) {
        return false;
    }
    
    // Créer l'image de destination
    $destination_image = imagecreatetruecolor($new_width, $new_height);
    
    // Préserver la transparence pour PNG et GIF
    if ($image_info['type'] == IMAGETYPE_PNG || $image_info['type'] == IMAGETYPE_GIF) {
        imagealphablending($destination_image, false);
        imagesavealpha($destination_image, true);
        $transparent = imagecolorallocatealpha($destination_image, 255, 255, 255, 127);
        imagefilledrectangle($destination_image, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Redimensionner
    imagecopyresampled($destination_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height);
    
    // Sauvegarder selon le type
    $result = false;
    switch ($image_info['type']) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($destination_image, $destination_path, IMAGE_QUALITY);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($destination_image, $destination_path);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($destination_image, $destination_path);
            break;
    }
    
    // Libérer la mémoire
    imagedestroy($source_image);
    imagedestroy($destination_image);
    
    return $result;
}
?>
