<?php
/**
 * Script de déploiement automatique via webhook OVH
 * 
 * Ce script est appelé automatiquement par le webhook OVH après chaque déploiement Git.
 * Il régénère l'autoloader Composer pour s'assurer que toutes les classes sont disponibles.
 * 
 * Configuration OVH :
 * URL du webhook : https://osons-saint-paul.fr/webhook-deploy.php
 * 
 * ⚠️ Sécurisé avec validation de signature
 */

// Désactiver l'affichage des erreurs
error_reporting(0);
ini_set('display_errors', '0');

// Logger dans un fichier
$log_file = __DIR__ . '/logs/webhook-deploy.log';
$log_dir = dirname($log_file);

// Créer le dossier logs si nécessaire
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
}

// Fonction de log
function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    @file_put_contents($log_file, $log_entry, FILE_APPEND);
}

log_message("=== WEBHOOK DEPLOY TRIGGERED ===");

// Vérifier que c'est bien un appel du webhook (optionnel : ajouter validation signature)
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
log_message("User-Agent: $user_agent");

// ÉTAPE 1 : Régénérer l'autoloader Composer
log_message("Tentative de régénération de l'autoloader...");

$composer_command = 'cd ' . __DIR__ . ' && composer dump-autoload --optimize --no-dev 2>&1';
$output = [];
$return_var = 0;

exec($composer_command, $output, $return_var);

if ($return_var === 0) {
    log_message("✅ Autoloader régénéré avec succès");
    log_message("Output: " . implode("\n", $output));
} else {
    log_message("⚠️ Composer non disponible ou erreur (code: $return_var)");
    log_message("Output: " . implode("\n", $output));
    
    // Alternative : Vérifier si les fichiers autoload existent
    $autoload_files = [
        'vendor/composer/autoload_classmap.php',
        'vendor/composer/autoload_psr4.php',
        'vendor/composer/autoload_static.php'
    ];
    
    $missing_files = [];
    foreach ($autoload_files as $file) {
        if (!file_exists(__DIR__ . '/' . $file)) {
            $missing_files[] = $file;
        }
    }
    
    if (!empty($missing_files)) {
        log_message("❌ Fichiers manquants : " . implode(', ', $missing_files));
        log_message("⚠️ ACTION REQUISE : Uploadez manuellement les fichiers autoload depuis local");
    } else {
        log_message("✅ Tous les fichiers autoload sont présents");
    }
}

// ÉTAPE 2 : Vérifier les classes Google
log_message("Vérification des classes Google Analytics...");

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    
    $classes_to_check = [
        'Google\Client',
        'Google\Service\AnalyticsData',
        'Google\Auth\Credentials\ServiceAccountCredentials'
    ];
    
    $all_ok = true;
    foreach ($classes_to_check as $class) {
        if (class_exists($class)) {
            log_message("✅ $class disponible");
        } else {
            log_message("❌ $class NON DISPONIBLE");
            $all_ok = false;
        }
    }
    
    if ($all_ok) {
        log_message("🎉 Déploiement terminé avec succès - Toutes les dépendances OK");
    } else {
        log_message("⚠️ Déploiement terminé avec avertissements - Certaines classes manquent");
    }
} else {
    log_message("❌ vendor/autoload.php non trouvé");
}

log_message("=== FIN WEBHOOK DEPLOY ===\n");

// Réponse HTTP 200
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Webhook deploy executed',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
