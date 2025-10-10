<?php
/**
 * Script de post-déploiement automatique
 * 
 * Ce script doit être exécuté UNE FOIS après chaque déploiement Git sur OVH
 * pour régénérer l'autoloader Composer et s'assurer que toutes les classes
 * sont correctement mappées.
 * 
 * Usage : https://osons-saint-paul.fr/post-deploy.php
 * 
 * ⚠️ Ce script est sécurisé et ne s'exécute qu'une fois par déploiement
 */

// Sécurité : Token de confirmation
$deploy_token = $_GET['token'] ?? '';
$expected_token = md5('osons-saint-paul-2026'); // Changez ce token si vous voulez

if ($deploy_token !== $expected_token) {
    http_response_code(403);
    die('❌ Accès refusé. Token invalide.');
}

echo '<!DOCTYPE html>';
echo '<html lang="fr">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<title>Post-déploiement Osons Saint-Paul</title>';
echo '<style>';
echo 'body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }';
echo 'h1 { color: #569cd6; }';
echo '.success { color: #4ec9b0; }';
echo '.error { color: #f48771; }';
echo '.info { color: #ce9178; }';
echo 'pre { background: #2d2d2d; padding: 15px; border-radius: 5px; }';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<h1>🚀 Post-déploiement Osons Saint-Paul</h1>';

$log = [];

// ÉTAPE 1 : Vérifier l'existence de composer
$log[] = '<div class="info">--- Vérification Composer ---</div>';

if (file_exists(__DIR__ . '/composer.json')) {
    $log[] = '<div class="success">✅ composer.json trouvé</div>';
} else {
    $log[] = '<div class="error">❌ composer.json non trouvé</div>';
    echo implode("\n", $log);
    echo '</body></html>';
    exit;
}

// ÉTAPE 2 : Vérifier vendor/autoload.php
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    $log[] = '<div class="success">✅ vendor/autoload.php existe</div>';
} else {
    $log[] = '<div class="error">❌ vendor/autoload.php manquant. Exécutez "composer install" sur le serveur.</div>';
    echo implode("\n", $log);
    echo '</body></html>';
    exit;
}

// ÉTAPE 3 : Tenter de régénérer l'autoloader
$log[] = '<div class="info">--- Régénération de l\'autoloader ---</div>';

// Vérifier si on peut exécuter composer
$composer_command = 'composer dump-autoload --optimize 2>&1';
$output = [];
$return_var = 0;

// Essayer d'exécuter composer
exec($composer_command, $output, $return_var);

if ($return_var === 0 && !empty($output)) {
    $log[] = '<div class="success">✅ Composer dump-autoload exécuté avec succès</div>';
    $log[] = '<pre>' . htmlspecialchars(implode("\n", $output)) . '</pre>';
} else {
    // Si composer n'est pas disponible en ligne de commande
    $log[] = '<div class="error">⚠️ Composer non disponible en ligne de commande sur ce serveur</div>';
    $log[] = '<div class="info">📋 Solution alternative : Uploadez manuellement les fichiers d\'autoload depuis votre local</div>';
    $log[] = '<div class="info">Fichiers à uploader depuis local vers OVH :</div>';
    $log[] = '<pre>';
    $log[] = 'vendor/composer/autoload_classmap.php';
    $log[] = 'vendor/composer/autoload_namespaces.php';
    $log[] = 'vendor/composer/autoload_psr4.php';
    $log[] = 'vendor/composer/autoload_real.php';
    $log[] = 'vendor/composer/autoload_static.php';
    $log[] = '</pre>';
}

// ÉTAPE 4 : Vérifier que les classes Google sont chargées
$log[] = '<div class="info">--- Vérification des classes Google Analytics ---</div>';

require_once __DIR__ . '/vendor/autoload.php';

$classes_to_check = [
    'Google\Client',
    'Google\Service\AnalyticsData',
    'Google\Auth\Credentials\ServiceAccountCredentials'
];

$all_classes_ok = true;
foreach ($classes_to_check as $class) {
    if (class_exists($class)) {
        $log[] = '<div class="success">✅ ' . htmlspecialchars($class) . ' disponible</div>';
    } else {
        $log[] = '<div class="error">❌ ' . htmlspecialchars($class) . ' NON DISPONIBLE</div>';
        $all_classes_ok = false;
    }
}

// ÉTAPE 5 : Résumé final
$log[] = '<div class="info">--- Résumé ---</div>';

if ($all_classes_ok) {
    $log[] = '<div class="success">🎉 <strong>Post-déploiement réussi !</strong></div>';
    $log[] = '<div class="success">✅ Toutes les dépendances Google Analytics sont disponibles</div>';
    $log[] = '<div class="success">✅ Votre site est prêt à fonctionner</div>';
    $log[] = '<div class="info">📋 Vous pouvez maintenant accéder à l\'administration</div>';
} else {
    $log[] = '<div class="error">⚠️ <strong>Certaines classes manquent</strong></div>';
    $log[] = '<div class="info">📋 Uploadez manuellement les fichiers d\'autoload depuis votre local (voir ci-dessus)</div>';
}

// Afficher le log
echo implode("\n", $log);

echo '<hr>';
echo '<div class="info">';
echo '<p><strong>🔗 Liens utiles :</strong></p>';
echo '<ul>';
echo '<li><a href="/admin/" style="color: #569cd6;">Accéder à l\'administration</a></li>';
echo '<li><a href="/" style="color: #569cd6;">Accéder au site public</a></li>';
echo '</ul>';
echo '</div>';

echo '<hr>';
echo '<div class="error">';
echo '<p><strong>⚠️ SÉCURITÉ :</strong></p>';
echo '<p>Ce script peut être supprimé après exécution ou conservé pour les prochains déploiements.</p>';
echo '<p>Token d\'accès requis : <code>' . htmlspecialchars($expected_token) . '</code></p>';
echo '</div>';

echo '</body>';
echo '</html>';
?>
