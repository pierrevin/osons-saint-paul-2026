<?php
/**
 * Script de correction rapide pour config.php
 * 
 * Ce script ajoute la fonction check_auth() manquante au fichier config.php
 * qui cause l'erreur 500 dans l'administration.
 * 
 * ⚠️ SUPPRIMEZ ce fichier après usage !
 */

echo '<h2>🔧 Correction config.php</h2>';
echo '<hr>';

$config_file = __DIR__ . '/config.php';

// Vérifier si config.php existe
if (!file_exists($config_file)) {
    echo '❌ <strong>Erreur :</strong> Le fichier config.php n\'existe pas !<br>';
    echo 'Le script de setup initial n\'a pas fonctionné correctement.<br>';
    echo 'Veuillez relancer le script setup_initial.php d\'abord.';
    exit;
}

// Lire le contenu actuel
$config_content = file_get_contents($config_file);

// Vérifier si la fonction check_auth existe déjà
if (strpos($config_content, 'function check_auth') !== false) {
    echo '✅ <strong>Fonction check_auth déjà présente</strong><br>';
    echo 'Le fichier config.php semble correct.<br>';
    echo 'L\'erreur 500 vient peut-être d\'autre chose.<br><br>';
    
    echo '<h3>🔍 Vérifications supplémentaires :</h3>';
    
    // Vérifier DATA_PATH
    if (defined('DATA_PATH')) {
        echo '✅ DATA_PATH défini : ' . DATA_PATH . '<br>';
        
        // Vérifier si le dossier existe
        if (is_dir(DATA_PATH)) {
            echo '✅ Dossier DATA_PATH existe<br>';
            
            // Vérifier site_content.json
            $site_content_file = DATA_PATH . '/site_content.json';
            if (file_exists($site_content_file)) {
                echo '✅ site_content.json existe<br>';
            } else {
                echo '❌ site_content.json manquant<br>';
                echo 'Création d\'un fichier vide...<br>';
                
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
                    echo '✅ site_content.json créé avec structure de base<br>';
                } else {
                    echo '❌ Impossible de créer site_content.json<br>';
                }
            }
        } else {
            echo '❌ Dossier DATA_PATH n\'existe pas : ' . DATA_PATH . '<br>';
            echo 'Création du dossier...<br>';
            if (mkdir(DATA_PATH, 0755, true)) {
                echo '✅ Dossier DATA_PATH créé<br>';
            } else {
                echo '❌ Impossible de créer le dossier DATA_PATH<br>';
            }
        }
    } else {
        echo '❌ DATA_PATH non défini<br>';
    }
    
} else {
    echo '🔧 <strong>Ajout de la fonction check_auth...</strong><br>';
    
    // Fonction à ajouter
    $function_to_add = "\n\n// Fonction d'authentification\nfunction check_auth() {\n    if (!isset(\$_SESSION['admin_logged_in']) || \$_SESSION['admin_logged_in'] !== true) {\n        header('Location: ../login.php');\n        exit;\n    }\n}";
    
    // Ajouter la fonction
    $new_content = $config_content . $function_to_add;
    
    if (file_put_contents($config_file, $new_content)) {
        echo '✅ <strong>Fonction check_auth ajoutée avec succès !</strong><br>';
        echo 'Le fichier config.php a été corrigé.<br><br>';
        
        echo '<h3>🎉 Correction terminée</h3>';
        echo '<p><strong>Vous pouvez maintenant :</strong></p>';
        echo '<ul>';
        echo '<li>✅ <a href="pages/schema_admin_new.php">Accéder à l\'administration</a></li>';
        echo '<li>✅ Tester toutes les fonctionnalités admin</li>';
        echo '</ul>';
        
    } else {
        echo '❌ <strong>Erreur :</strong> Impossible de modifier config.php<br>';
        echo 'Vérifiez les permissions du fichier.';
    }
}

echo '<hr>';
echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">';
echo '<h4>⚠️ SÉCURITÉ IMPORTANTE :</h4>';
echo '<p><strong>SUPPRIMEZ ce fichier fix_config.php maintenant !</strong></p>';
echo '<p>Ce script ne doit plus être accessible après correction.</p>';
echo '</div>';

echo '<hr>';
echo '<p><small>Script de correction - Osons Saint-Paul 2026</small></p>';
?>
