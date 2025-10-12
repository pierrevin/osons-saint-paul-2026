<?php
/**
 * Correction rapide post-déploiement OVH
 * 
 * Ce script corrige les problèmes après un déploiement Git automatique
 * qui a écrasé les fichiers de configuration.
 * 
 * ⚠️ SUPPRIMEZ ce fichier après usage !
 */

echo '<h2>🚀 Correction rapide post-déploiement</h2>';
echo '<hr>';

// ÉTAPE 1: Corriger config.php
echo '<h3>📝 Correction config.php</h3>';

$config_file = __DIR__ . '/config.php';

// Contenu correct pour config.php
$config_content = '<?php
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
if (file_put_contents($config_file, $config_content)) {
    echo '✅ config.php corrigé<br>';
} else {
    echo '❌ Erreur config.php<br>';
    exit;
}

// ÉTAPE 2: Inclure config pour avoir les constantes
include_once $config_file;

if (!defined('DATA_PATH')) {
    echo '❌ DATA_PATH non défini<br>';
    exit;
}

echo '✅ DATA_PATH défini : ' . DATA_PATH . '<br>';

// Créer le dossier data-osons
if (!is_dir(DATA_PATH)) {
    if (mkdir(DATA_PATH, 0755, true)) {
        echo '✅ Dossier data-osons créé<br>';
    }
} else {
    echo '✅ Dossier data-osons existe<br>';
}

// ÉTAPE 3: Créer users.json
echo '<br><h3>👥 Création users.json</h3>';

$users_data = [
    "users" => [
        [
            "id" => 1,
            "username" => "admin",
            "password_hash" => '$2y$12$Gqg/nThgiHTiZEhGf22GUubuSqQjb5mb5ofd3RR/BEuPY4vDS71sa',
            "role" => "admin",
            "email" => "pierre.vincenot@gmail.com",
            "created_at" => "2025-10-02T08:15:00Z",
            "last_login" => null,
            "active" => true
        ],
        [
            "id" => 2,
            "username" => "editeur",
            "password_hash" => '$2y$12$3jUoFwWQyKJLmj3jC1cQxeU7Nyg02KtCiWgFPSSQIZk6oJolPml/i',
            "role" => "editeur",
            "email" => "bonjour@osonssaintpaul.fr",
            "created_at" => "2025-10-02T08:15:00Z",
            "last_login" => null,
            "active" => true
        ],
        [
            "id" => 3,
            "username" => "vincenot_editeur",
            "password_hash" => '$2y$12$8aOPoXFsox1PA4SgH0XUXOtyR3Gl6AVcsL74UCWlyFmMscQv8jKna',
            "role" => "editeur",
            "email" => "pierrevincenot@immediatlab.fr",
            "created_at" => "2025-10-02T08:55:12Z",
            "last_login" => null,
            "active" => true
        ]
    ],
    "settings" => [
        "max_login_attempts" => 5,
        "lockout_duration" => 900,
        "session_timeout" => 3600,
        "password_min_length" => 8,
        "require_2fa" => false
    ]
];

$users_file = __DIR__ . '/users.json';
if (file_put_contents($users_file, json_encode($users_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo '✅ users.json créé avec 3 utilisateurs<br>';
} else {
    echo '❌ Erreur users.json<br>';
}

// ÉTAPE 4: Créer les logs
echo '<br><h3>📊 Création logs</h3>';

$logs_dir = __DIR__ . '/logs';
if (!is_dir($logs_dir)) {
    if (mkdir($logs_dir, 0755, true)) {
        echo '✅ Dossier logs créé<br>';
    }
} else {
    echo '✅ Dossier logs existe<br>';
}

// Créer les fichiers de logs vides
$log_files = ['security.log', 'email_logs.log', 'image_processor.log'];
foreach ($log_files as $log_file) {
    $log_path = $logs_dir . '/' . $log_file;
    if (!file_exists($log_path)) {
        if (touch($log_path)) {
            chmod($log_path, 644);
            echo "✅ $log_file créé<br>";
        }
    } else {
        echo "✅ $log_file existe<br>";
    }
}

// ÉTAPE 5: Créer site_content.json
echo '<br><h3>📄 Création site_content.json</h3>';

$site_content = [
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

$site_content_file = DATA_PATH . '/site_content.json';
if (file_put_contents($site_content_file, json_encode($site_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo '✅ site_content.json créé avec données complètes<br>';
} else {
    echo '❌ Erreur site_content.json<br>';
}

echo '<hr>';
echo '<h3>🎉 Correction rapide terminée !</h3>';
echo '<p><strong>Vous pouvez maintenant :</strong></p>';
echo '<ul>';
echo '<li>✅ <a href="login.php">Se connecter à l\'admin</a></li>';
echo '<li>✅ <a href="pages/schema_admin_new.php">Accéder à l\'administration</a></li>';
echo '<li>✅ Utiliser les identifiants : admin, editeur, vincenot_editeur</li>';
echo '</ul>';

echo '<hr>';
echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">';
echo '<h4>⚠️ SÉCURITÉ IMPORTANTE :</h4>';
echo '<p><strong>SUPPRIMEZ ce fichier quick_fix.php maintenant !</strong></p>';
echo '</div>';

echo '<hr>';
echo '<p><small>Correction rapide post-déploiement - Osons Saint-Paul 2026</small></p>';
?>
