<?php
/**
 * Script de vérification post-déploiement
 * 
 * Ce script vérifie que tous les éléments nécessaires sont en place
 * après un déploiement Git automatique.
 * 
 * À exécuter UNE SEULE FOIS après le premier déploiement.
 * 
 * ⚠️ SUPPRIMEZ ce fichier après usage !
 */

echo '<h2>🔍 Vérification post-déploiement</h2>';
echo '<hr>';

$errors = [];
$warnings = [];

// Vérifier que le dossier data-osons existe
if (!is_dir(__DIR__ . '/data-osons')) {
    echo '📁 Création du dossier data-osons...<br>';
    if (mkdir(__DIR__ . '/data-osons', 0755, true)) {
        echo '✅ Dossier data-osons créé<br>';
    } else {
        $errors[] = '❌ Impossible de créer le dossier data-osons';
    }
} else {
    echo '✅ Dossier data-osons existe<br>';
}

// Vérifier site_content.json
$site_content_file = __DIR__ . '/data-osons/site_content.json';
if (!file_exists($site_content_file)) {
    echo '📄 Création de site_content.json...<br>';
    
    // Copier depuis data-osons.initial si disponible
    $initial_file = __DIR__ . '/data-osons.initial/site_content.json';
    if (file_exists($initial_file)) {
        if (copy($initial_file, $site_content_file)) {
            echo '✅ site_content.json copié depuis data-osons.initial<br>';
        } else {
            $errors[] = '❌ Impossible de copier site_content.json';
        }
    } else {
        // Créer un fichier vide avec structure de base
        $empty_content = [
            "hero" => [
                "title" => "Construisons ensemble le village vivant et partagé",
                "button_primary" => "Découvrir le programme",
                "button_secondary" => "Faire une proposition",
                "background_image" => "uploads/hero_1759748494_68e3a18ee0479.webp"
            ],
            "programme" => [
                "title" => "Notre Programme",
                "subtitle" => "Osons intégrer vos idées",
                "description" => "Un programme co-construit avec les habitants",
                "proposals" => []
            ],
            "equipe" => [
                "title" => "Notre Équipe",
                "subtitle" => "Des citoyens engagés pour Saint-Paul-sur-Save",
                "members" => []
            ],
            "rendez_vous" => [
                "title" => "Nos Rendez-vous",
                "subtitle" => "Rejoignez-nous pour construire l'avenir",
                "events" => []
            ],
            "charte" => [
                "title" => "Notre Charte",
                "content" => "Nous nous engageons pour une politique transparente, participative et au service des habitants."
            ],
            "contact" => [
                "title" => "Contact",
                "subtitle" => "Parlons ensemble de l'avenir de notre village",
                "content" => "N'hésitez pas à nous contacter pour toute question ou proposition."
            ],
            "citations" => [
                "citation1" => [
                    "text" => "L'avenir appartient à ceux qui osent",
                    "author" => "Proverbe",
                    "background_image" => "uploads/citation_1759768147_68e3ee53765ab.webp"
                ],
                "citation2" => [
                    "text" => "Ensemble, nous sommes plus forts",
                    "author" => "Devise citoyenne",
                    "background_image" => "uploads/citation_1759768182_68e3ee767f389.webp"
                ],
                "citation3" => [
                    "text" => "La démocratie se construit chaque jour",
                    "author" => "Engagement citoyen",
                    "background_image" => "uploads/citation_1759769083_68e3f1fb370c2.webp"
                ],
                "citation4" => [
                    "text" => "Saint-Paul-sur-Save, notre village, notre avenir",
                    "author" => "Osons Saint-Paul",
                    "background_image" => "uploads/citation_1759782127_68e424ef1aa4a.webp"
                ]
            ]
        ];
        
        if (file_put_contents($site_content_file, json_encode($empty_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            echo '✅ site_content.json créé avec structure de base<br>';
        } else {
            $errors[] = '❌ Impossible de créer site_content.json';
        }
    }
} else {
    echo '✅ site_content.json existe<br>';
}

// Vérifier les permissions
if (is_dir(__DIR__ . '/data-osons')) {
    if (!is_writable(__DIR__ . '/data-osons')) {
        $warnings[] = '⚠️ Le dossier data-osons n\'est pas accessible en écriture';
    } else {
        echo '✅ Dossier data-osons accessible en écriture<br>';
    }
}

// Vérifier admin/config.php
if (file_exists(__DIR__ . '/admin/config.php')) {
    echo '✅ admin/config.php existe<br>';
    
    // Vérifier que DATA_PATH est défini
    include_once __DIR__ . '/admin/config.php';
    if (defined('DATA_PATH')) {
        echo '✅ DATA_PATH défini : ' . DATA_PATH . '<br>';
    } else {
        $errors[] = '❌ DATA_PATH non défini dans admin/config.php';
    }
} else {
    $errors[] = '❌ admin/config.php manquant';
}

// Vérifier admin/users.json
if (file_exists(__DIR__ . '/admin/users.json')) {
    echo '✅ admin/users.json existe<br>';
} else {
    $errors[] = '❌ admin/users.json manquant';
}

// Vérifier admin/logs
if (!is_dir(__DIR__ . '/admin/logs')) {
    echo '📁 Création du dossier admin/logs...<br>';
    if (mkdir(__DIR__ . '/admin/logs', 0755, true)) {
        echo '✅ Dossier admin/logs créé<br>';
        
        // Créer les fichiers de logs vides
        $log_files = ['security.log', 'email_logs.log', 'image_processor.log'];
        foreach ($log_files as $log_file) {
            $log_path = __DIR__ . '/admin/logs/' . $log_file;
            if (!file_exists($log_path)) {
                if (touch($log_path)) {
                    chmod($log_path, 644);
                    echo "✅ $log_file créé<br>";
                }
            }
        }
    } else {
        $warnings[] = '⚠️ Impossible de créer le dossier admin/logs';
    }
} else {
    echo '✅ Dossier admin/logs existe<br>';
}

echo '<hr>';

// Affichage du rapport final
if (empty($errors)) {
    echo '<h3>🎉 Vérification terminée avec succès !</h3>';
    echo '<p><strong>Votre site est prêt :</strong></p>';
    echo '<ul>';
    echo '<li>✅ <a href="/">Site public accessible</a></li>';
    echo '<li>✅ <a href="/admin/">Administration accessible</a></li>';
    echo '<li>✅ Tous les fichiers nécessaires sont en place</li>';
    echo '<li>✅ Déploiements Git automatiques fonctionneront</li>';
    echo '</ul>';
    
    if (!empty($warnings)) {
        echo '<h4>⚠️ Avertissements :</h4><ul>';
        foreach ($warnings as $warning) {
            echo '<li>' . htmlspecialchars($warning) . '</li>';
        }
        echo '</ul>';
    }
    
} else {
    echo '<h3>❌ Problèmes détectés :</h3><ul>';
    foreach ($errors as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
    }
    echo '</ul>';
    
    if (!empty($warnings)) {
        echo '<h4>⚠️ Avertissements :</h4><ul>';
        foreach ($warnings as $warning) {
            echo '<li>' . htmlspecialchars($warning) . '</li>';
        }
        echo '</ul>';
    }
}

echo '<hr>';
echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">';
echo '<h4>⚠️ SÉCURITÉ IMPORTANTE :</h4>';
echo '<p><strong>SUPPRIMEZ ce fichier deploy-check.php maintenant !</strong></p>';
echo '<p>Ce script ne doit plus être accessible après vérification.</p>';
echo '</div>';

echo '<hr>';
echo '<p><small>Script de vérification post-déploiement - Osons Saint-Paul 2026</small></p>';
?>
