<?php
/**
 * Script combiné : Correction config + Restauration données
 * 
 * Ce script corrige le fichier config.php ET restaure les données complètes
 * 
 * ⚠️ SUPPRIMEZ ce fichier après usage !
 */

echo '<h2>🔧 Correction complète : Config + Données</h2>';
echo '<hr>';

// ÉTAPE 1: Corriger config.php
echo '<h3>📝 Étape 1: Correction du fichier config.php</h3>';

$config_file = __DIR__ . '/config.php';

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
if (file_exists($config_file)) {
    $backup_file = $config_file . '.backup.' . date('Y-m-d-H-i-s');
    copy($config_file, $backup_file);
    echo '✅ Sauvegarde config.php créée<br>';
}

// Écrire le nouveau fichier
if (file_put_contents($config_file, $new_config_content)) {
    echo '✅ config.php corrigé avec succès<br>';
} else {
    echo '❌ Erreur lors de la correction de config.php<br>';
    exit;
}

// ÉTAPE 2: Inclure le fichier config pour avoir accès aux constantes
include_once $config_file;

if (!defined('DATA_PATH')) {
    echo '❌ Erreur : DATA_PATH toujours non défini après correction<br>';
    exit;
}

echo '✅ DATA_PATH défini : ' . DATA_PATH . '<br>';

// Créer le dossier s'il n'existe pas
if (!is_dir(DATA_PATH)) {
    if (mkdir(DATA_PATH, 0755, true)) {
        echo '✅ Dossier DATA_PATH créé<br>';
    }
} else {
    echo '✅ Dossier DATA_PATH existe<br>';
}

echo '<br>';

// ÉTAPE 3: Restaurer les données
echo '<h3>📊 Étape 2: Restauration des données complètes</h3>';

// Données complètes à restaurer
$site_content = [
    "hero" => [
        "title" => "Construisons ensemble le village vivant et partagé",
        "button_primary" => "Découvrir le programme",
        "button_secondary" => "Faire une proposition",
        "background_image" => "uploads/hero_1759748494_68e3a18ee0479.webp"
    ],
    "programme" => [
        "h2" => "Notre Programme",
        "h3" => "Osons intégrer vos idées",
        "title" => "Notre Programme",
        "subtitle" => "Osons intégrer vos idées",
        "description" => "Un programme co-construit avec les habitants",
        "proposals" => [
            [
                "id" => 1,
                "title" => "Protéger notre patrimoine naturel",
                "description" => "Protéger et valoriser notre patrimoine naturel pour les générations futures.",
                "icon" => "default",
                "color" => "#65ae99",
                "pillar" => "proteger",
                "citizen_proposal" => false,
                "items" => [
                    "Protection des espaces verts",
                    "Développement de la biodiversité",
                    "Gestion durable des ressources"
                ]
            ],
            [
                "id" => 2,
                "title" => "Tisser du lien social",
                "description" => "Créer des espaces de rencontre et de solidarité pour tous les habitants.",
                "icon" => "default",
                "color" => "#fcc549",
                "pillar" => "tisser",
                "citizen_proposal" => false,
                "items" => [
                    "Amélioration des équipements publics",
                    "Soutien aux associations",
                    "Événements communautaires"
                ]
            ],
            [
                "id" => 3,
                "title" => "Ouvrir la démocratie",
                "description" => "Assurer une gestion transparente et participative de la commune.",
                "icon" => "default",
                "color" => "#004a6d",
                "pillar" => "ouvrir",
                "citizen_proposal" => false,
                "items" => [
                    "Consultation citoyenne régulière",
                    "Transparence budgétaire",
                    "Communication municipale améliorée"
                ]
            ]
        ]
    ],
    "equipe" => [
        "title" => "Notre Équipe",
        "subtitle" => "Des citoyens engagés pour Saint-Paul-sur-Save",
        "members" => [
            [
                "id" => 1,
                "name" => "Pierre Vincenot",
                "role" => "Tête de liste",
                "bio" => "Engagé pour une démocratie participative",
                "image" => "uploads/member_1759785411_68e431c377a5f.webp"
            ]
        ]
    ],
    "rendez_vous" => [
        "title" => "Nos Rendez-vous",
        "subtitle" => "Rejoignez-nous pour construire l'avenir",
        "events" => [
            [
                "id" => 1,
                "title" => "Réunion publique",
                "date" => "2025-10-15",
                "time" => "18:30",
                "location" => "Salle des fêtes",
                "description" => "Présentation du programme et échanges"
            ]
        ]
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

// Sauvegarder l'ancien fichier
$site_content_file = DATA_PATH . '/site_content.json';
if (file_exists($site_content_file)) {
    $backup_file = $site_content_file . '.backup.' . date('Y-m-d-H-i-s');
    copy($site_content_file, $backup_file);
    echo '✅ Sauvegarde site_content.json créée<br>';
}

// Écrire les nouvelles données
if (file_put_contents($site_content_file, json_encode($site_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo '✅ site_content.json restauré avec les vraies données<br>';
} else {
    echo '❌ Erreur lors de la restauration des données<br>';
    exit;
}

echo '<br>';

// ÉTAPE 4: Résumé final
echo '<h3>🎉 Correction et restauration terminées !</h3>';
echo '<p><strong>✅ Config.php corrigé</strong></p>';
echo '<p><strong>✅ Données complètes restaurées</strong></p>';
echo '<p><strong>✅ Administration prête</strong></p>';

echo '<h4>📊 Données restaurées :</h4>';
echo '<ul>';
echo '<li>✅ Hero section avec titre et image</li>';
echo '<li>✅ Programme avec 3 propositions</li>';
echo '<li>✅ Équipe avec Pierre Vincenot</li>';
echo '<li>✅ Rendez-vous publics</li>';
echo '<li>✅ Charte et contact</li>';
echo '<li>✅ 4 citations avec images</li>';
echo '</ul>';

echo '<hr>';
echo '<h3>🚀 Vous pouvez maintenant :</h3>';
echo '<ul>';
echo '<li>✅ <a href="pages/schema_admin_new.php">Accéder à l\'administration complète</a></li>';
echo '<li>✅ Voir toutes vos données (programme, équipe, etc.)</li>';
echo '<li>✅ Modifier le contenu via l\'interface admin</li>';
echo '</ul>';

echo '<hr>';
echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">';
echo '<h4>⚠️ SÉCURITÉ IMPORTANTE :</h4>';
echo '<p><strong>SUPPRIMEZ ce fichier fix_and_restore.php maintenant !</strong></p>';
echo '</div>';

echo '<hr>';
echo '<p><small>Script de correction complète - Osons Saint-Paul 2026</small></p>';
?>
