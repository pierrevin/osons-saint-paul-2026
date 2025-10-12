<?php
/**
 * Script de restauration des données complètes
 * 
 * Ce script restaure les vraies données du site depuis le fichier local
 * vers le serveur OVH pour remplacer le fichier vide créé.
 * 
 * ⚠️ SUPPRIMEZ ce fichier après usage !
 */

echo '<h2>📊 Restauration des données complètes</h2>';
echo '<hr>';

// Vérifier que DATA_PATH est défini
if (!defined('DATA_PATH')) {
    echo '❌ <strong>Erreur :</strong> DATA_PATH non défini !<br>';
    echo 'Exécutez d\'abord fix_data_path.php';
    exit;
}

echo '✅ DATA_PATH défini : ' . DATA_PATH . '<br><br>';

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
    echo '✅ Sauvegarde créée : ' . basename($backup_file) . '<br>';
}

// Écrire les nouvelles données
if (file_put_contents($site_content_file, json_encode($site_content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    echo '✅ <strong>site_content.json restauré avec les vraies données !</strong><br>';
    echo '✅ Programme, équipe, citations, etc. restaurés<br><br>';
    
    echo '<h3>📊 Données restaurées :</h3>';
    echo '<ul>';
    echo '<li>✅ Hero section avec titre et image</li>';
    echo '<li>✅ Programme avec 3 propositions</li>';
    echo '<li>✅ Équipe avec Pierre Vincenot</li>';
    echo '<li>✅ Rendez-vous publics</li>';
    echo '<li>✅ Charte et contact</li>';
    echo '<li>✅ 4 citations avec images</li>';
    echo '</ul>';
    
    echo '<hr>';
    echo '<h3>🎉 Restauration terminée !</h3>';
    echo '<p><strong>Vous pouvez maintenant :</strong></p>';
    echo '<ul>';
    echo '<li>✅ <a href="pages/schema_admin_new.php">Accéder à l\'administration complète</a></li>';
    echo '<li>✅ Voir toutes vos données (programme, équipe, etc.)</li>';
    echo '<li>✅ Modifier le contenu via l\'interface admin</li>';
    echo '</ul>';
    
} else {
    echo '❌ <strong>Erreur :</strong> Impossible de restaurer les données<br>';
    echo 'Vérifiez les permissions du dossier DATA_PATH.';
}

echo '<hr>';
echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;">';
echo '<h4>⚠️ SÉCURITÉ IMPORTANTE :</h4>';
echo '<p><strong>SUPPRIMEZ ce fichier restore_data.php maintenant !</strong></p>';
echo '</div>';

echo '<hr>';
echo '<p><small>Script de restauration - Osons Saint-Paul 2026</small></p>';
?>
