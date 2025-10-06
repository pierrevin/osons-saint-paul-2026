<?php
/**
 * Script de test d'intégrité du JSON
 * Vérifie et répare automatiquement le fichier site_content.json
 */

require_once __DIR__ . '/admin/config.php';

function testJSONIntegrity() {
    $jsonFile = DATA_PATH . '/site_content.json';
    $results = [
        'file_exists' => false,
        'file_readable' => false,
        'file_size' => 0,
        'json_valid' => false,
        'structure_valid' => false,
        'errors' => [],
        'warnings' => []
    ];
    
    echo "🔍 Test d'intégrité du fichier JSON...\n\n";
    
    // 1. Vérifier l'existence du fichier
    if (!file_exists($jsonFile)) {
        $results['errors'][] = "Le fichier JSON n'existe pas";
        return $results;
    }
    $results['file_exists'] = true;
    
    // 2. Vérifier la lisibilité
    if (!is_readable($jsonFile)) {
        $results['errors'][] = "Le fichier JSON n'est pas lisible";
        return $results;
    }
    $results['file_readable'] = true;
    
    // 3. Vérifier la taille
    $results['file_size'] = filesize($jsonFile);
    if ($results['file_size'] === 0) {
        $results['errors'][] = "Le fichier JSON est vide";
        return $results;
    }
    
    if ($results['file_size'] < 100) {
        $results['warnings'][] = "Le fichier JSON semble trop petit (" . $results['file_size'] . " bytes)";
    }
    
    // 4. Tester la validité JSON
    $content = file_get_contents($jsonFile);
    $data = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $results['errors'][] = "Erreur JSON: " . json_last_error_msg();
        return $results;
    }
    $results['json_valid'] = true;
    
    // 5. Vérifier la structure
    $requiredSections = ['hero', 'programme', 'equipe', 'rendez_vous', 'charte', 'citations'];
    $missingSections = [];
    
    foreach ($requiredSections as $section) {
        if (!isset($data[$section])) {
            $missingSections[] = $section;
        }
    }
    
    if (!empty($missingSections)) {
        $results['errors'][] = "Sections manquantes: " . implode(', ', $missingSections);
        return $results;
    }
    
    $results['structure_valid'] = true;
    
    // 6. Vérifications supplémentaires
    if (empty($data['programme']['proposals'])) {
        $results['warnings'][] = "Aucune proposition dans le programme";
    }
    
    if (empty($data['equipe']['members'])) {
        $results['warnings'][] = "Aucun membre dans l'équipe";
    }
    
    if (empty($data['rendez_vous']['events'])) {
        $results['warnings'][] = "Aucun événement dans les rendez-vous";
    }
    
    return $results;
}

function repairJSON() {
    echo "🔧 Tentative de réparation du JSON...\n";
    
    // Charger le contenu actuel
    $jsonFile = DATA_PATH . '/site_content.json';
    $content = file_get_contents($jsonFile);
    
    // Essayer de réparer les erreurs JSON communes
    $content = str_replace(["\n", "\r"], '', $content);
    $content = preg_replace('/,(\s*[}\]])/', '$1', $content);
    $content = preg_replace('/([{\[])\s*,\s*/', '$1', $content);
    
    // Sauvegarder la version réparée
    $backupFile = DATA_PATH . '/backups/site_content_broken_' . date('Y-m-d_H-i-s') . '.json';
    copy($jsonFile, $backupFile);
    
    file_put_contents($jsonFile, $content);
    
    echo "✅ Version réparée sauvegardée\n";
    echo "📁 Sauvegarde de l'ancienne version: " . basename($backupFile) . "\n";
}

function restoreFromTemplate() {
    echo "🔄 Restauration depuis le template...\n";
    
    // Template JSON minimal mais fonctionnel
    $template = [
        'hero' => [
            'title' => 'Construisons ensemble le village vivant et partagé',
            'button_primary' => 'Découvrir le programme',
            'button_secondary' => 'Faire une proposition',
            'background_image' => 'Images/hero_test.png'
        ],
        'programme' => [
            'h2' => 'Notre Programme',
            'h3' => 'Osons intégrer vos idées',
            'proposals' => []
        ],
        'equipe' => [
            'h2' => 'Notre Équipe',
            'h3' => 'Osez nous aborder',
            'members' => []
        ],
        'rendez_vous' => [
            'h2' => 'Nos rendez-vous',
            'h3' => 'Osons échanger',
            'events' => []
        ],
        'charte' => [
            'h2' => 'Notre charte',
            'h3' => 'Nos engagements',
            'principles' => []
        ],
        'citations' => [
            'citation1' => ['text' => '', 'author' => '', 'background_image' => 'Images/hero_test.png'],
            'citation2' => ['text' => '', 'author' => '', 'background_image' => 'Images/hero_test.png'],
            'citation3' => ['text' => '', 'author' => '', 'background_image' => 'Images/hero_test.png'],
            'citation4' => ['text' => '', 'author' => '', 'background_image' => 'Images/hero_test.png']
        ]
    ];
    
    $jsonFile = DATA_PATH . '/site_content.json';
    $backupFile = DATA_PATH . '/backups/site_content_corrupted_' . date('Y-m-d_H-i-s') . '.json';
    
    // Sauvegarder l'ancienne version
    if (file_exists($jsonFile)) {
        copy($jsonFile, $backupFile);
    }
    
    // Écrire le template
    file_put_contents($jsonFile, json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    echo "✅ Template restauré\n";
    echo "📁 Ancienne version sauvegardée: " . basename($backupFile) . "\n";
}

// Exécution du script
if (php_sapi_name() === 'cli') {
    $action = $argv[1] ?? 'test';
    
    switch ($action) {
        case 'test':
            $results = testJSONIntegrity();
            
            echo "📊 Résultats du test:\n";
            echo "- Fichier existe: " . ($results['file_exists'] ? '✅' : '❌') . "\n";
            echo "- Fichier lisible: " . ($results['file_readable'] ? '✅' : '❌') . "\n";
            echo "- Taille: " . number_format($results['file_size']) . " bytes\n";
            echo "- JSON valide: " . ($results['json_valid'] ? '✅' : '❌') . "\n";
            echo "- Structure valide: " . ($results['structure_valid'] ? '✅' : '❌') . "\n";
            
            if (!empty($results['errors'])) {
                echo "\n❌ Erreurs:\n";
                foreach ($results['errors'] as $error) {
                    echo "  - $error\n";
                }
            }
            
            if (!empty($results['warnings'])) {
                echo "\n⚠️  Avertissements:\n";
                foreach ($results['warnings'] as $warning) {
                    echo "  - $warning\n";
                }
            }
            
            if ($results['structure_valid'] && empty($results['errors'])) {
                echo "\n🎉 Le fichier JSON est en bon état!\n";
            } else {
                echo "\n💡 Utilisez 'php test_json_integrity.php repair' pour réparer\n";
                echo "💡 Utilisez 'php test_json_integrity.php restore' pour restaurer depuis le template\n";
            }
            break;
            
        case 'repair':
            repairJSON();
            break;
            
        case 'restore':
            restoreFromTemplate();
            break;
            
        default:
            echo "Usage: php test_json_integrity.php [test|repair|restore]\n";
    }
}
?>
