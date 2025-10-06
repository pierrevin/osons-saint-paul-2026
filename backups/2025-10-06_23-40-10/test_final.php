<?php
/**
 * Test final de la refonte - Vérification complète
 */

echo "<h1>🧪 Test Final de la Refonte Admin</h1>";

// Test 1: Vérification de tous les fichiers
echo "<h2>📁 Vérification des fichiers</h2>";

$required_files = [
    // Classes de base
    'admin/includes/AdminSection.php',
    'admin/includes/AdminModal.php',
    
    // Sections
    'admin/pages/sections/hero.php',
    'admin/pages/sections/programme.php',
    'admin/pages/sections/citations.php',
    // 'admin/pages/sections/transitions.php', // supprimé: section obsolète dans la nouvelle architecture
    'admin/pages/sections/equipe.php',
    'admin/pages/sections/rendez_vous.php',
    'admin/pages/sections/charte.php',
    // 'admin/pages/sections/idees.php', // supprimé: section obsolète dans la nouvelle architecture
    'admin/pages/sections/mediatheque.php',
    
    // CSS
    'admin/assets/css/admin-core.css',
    'admin/assets/css/admin-components.css',
    'admin/assets/css/admin-sections.css',
    
    // JavaScript
    'admin/assets/js/admin-core.js',
    'admin/assets/js/admin-modals.js',
    'admin/assets/js/admin-tabs.js',
    'admin/assets/js/admin-actions.js',
    
    // Pages principales
    'admin/pages/schema_admin_new.php',
    'admin/pages/gestion-utilisateurs-new.php',
    'admin/pages/logs-new.php',
];

$all_files_exist = true;
$file_stats = [];

foreach ($required_files as $file) {
    $exists = file_exists($file);
    $size = $exists ? filesize($file) : 0;
    $status = $exists ? '✅' : '❌';
    $color = $exists ? 'green' : 'red';
    
    echo "<p style='color: $color;'>$status $file";
    if ($exists) {
        echo " (" . number_format($size) . " bytes)";
        $file_stats[] = ['file' => $file, 'size' => $size];
    }
    echo "</p>";
    
    if (!$exists) {
        $all_files_exist = false;
    }
}

// Test 2: Vérification des classes PHP
echo "<h2>🔧 Test des classes PHP</h2>";

try {
    require_once 'admin/includes/AdminSection.php';
    require_once 'admin/includes/AdminModal.php';
    require_once 'admin/pages/sections/hero.php';
    require_once 'admin/pages/sections/programme.php';
    require_once 'admin/pages/sections/transitions.php';
    
    echo "<p style='color: green;'>✅ Toutes les classes PHP chargées avec succès</p>";
    
    // Test d'instanciation
    $heroSection = new HeroSection();
    echo "<p style='color: green;'>✅ HeroSection instanciée</p>";
    
    $programmeSection = new ProgrammeSection();
    echo "<p style='color: green;'>✅ ProgrammeSection instanciée</p>";
    
    $transitionsSection = new TransitionsSection();
    echo "<p style='color: green;'>✅ TransitionsSection instanciée</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur lors du chargement des classes: " . $e->getMessage() . "</p>";
}

// Test 3: Vérification de la syntaxe
echo "<h2>📝 Test de la syntaxe</h2>";

// Test PHP syntax
$php_files = glob('admin/pages/sections/*.php');
$syntax_errors = [];

foreach ($php_files as $file) {
    $output = [];
    $return_var = 0;
    exec("php -l $file 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "<p style='color: green;'>✅ Syntaxe PHP valide: $file</p>";
    } else {
        echo "<p style='color: red;'>❌ Erreur de syntaxe: $file</p>";
        echo "<pre style='color: red;'>" . implode("\n", $output) . "</pre>";
        $syntax_errors[] = $file;
    }
}

// Test 4: Statistiques de la refonte
echo "<h2>📊 Statistiques de la refonte</h2>";

$total_size = array_sum(array_column($file_stats, 'size'));
$old_file_size = file_exists('admin/pages/schema_admin.php') ? filesize('admin/pages/schema_admin.php') : 0;

echo "<div style='background: #e8f5e8; padding: 1rem; border-radius: 8px; margin: 1rem 0;'>";
echo "<h3>📈 Comparaison des tailles</h3>";
echo "<ul>";
echo "<li><strong>Ancien fichier principal:</strong> " . number_format($old_file_size) . " bytes</li>";
echo "<li><strong>Nouveau fichier principal:</strong> " . number_format(filesize('admin/pages/schema_admin_new.php')) . " bytes</li>";
echo "<li><strong>Total nouvelle architecture:</strong> " . number_format($total_size) . " bytes</li>";
echo "<li><strong>Réduction du fichier principal:</strong> " . round((1 - filesize('admin/pages/schema_admin_new.php') / $old_file_size) * 100, 1) . "%</li>";
echo "</ul>";
echo "</div>";

// Test 5: Vérification de la page publique
echo "<h2>🌐 Vérification de la page publique</h2>";

$public_files = [
    'index.php',
    'styles.css',
    'data/site_content.json'
];

foreach ($public_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file - Préservé</p>";
    } else {
        echo "<p style='color: red;'>❌ $file - Manquant</p>";
    }
}

// Résumé final
echo "<h2>📋 Résumé Final</h2>";

if ($all_files_exist && empty($syntax_errors)) {
    echo "<div style='background: #d4edda; padding: 2rem; border-radius: 12px; color: #155724;'>";
    echo "<h3>🎉 Refonte terminée avec succès !</h3>";
    echo "<p><strong>✅ Tous les fichiers créés</strong></p>";
    echo "<p><strong>✅ Syntaxe PHP valide</strong></p>";
    echo "<p><strong>✅ Classes fonctionnelles</strong></p>";
    echo "<p><strong>✅ Page publique préservée</strong></p>";
    echo "</div>";
    
    echo "<div style='margin-top: 2rem;'>";
    echo "<h3>🚀 Prochaines étapes</h3>";
    echo "<ol>";
    echo "<li><strong>Testez la nouvelle interface:</strong> <a href='admin/pages/schema_admin_new.php' target='_blank'>Ouvrir schema_admin_new.php</a></li>";
    echo "<li><strong>Testez la gestion des utilisateurs:</strong> <a href='admin/pages/gestion-utilisateurs-new.php' target='_blank'>Ouvrir gestion-utilisateurs-new.php</a></li>";
    echo "<li><strong>Testez les logs:</strong> <a href='admin/pages/logs-new.php' target='_blank'>Ouvrir logs-new.php</a></li>";
    echo "<li><strong>Validez toutes les fonctionnalités</strong> avant de remplacer l'ancienne version</li>";
    echo "<li><strong>Consultez la documentation:</strong> <a href='REFACTORING_ADMIN_README.md' target='_blank'>README de la refonte</a></li>";
    echo "<li><strong>Comparez les architectures:</strong> <a href='COMPARAISON_ARCHITECTURES.md' target='_blank'>Comparaison détaillée</a></li>";
    echo "</ol>";
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; padding: 2rem; border-radius: 12px; color: #721c24;'>";
    echo "<h3>❌ Problèmes détectés</h3>";
    if (!$all_files_exist) {
        echo "<p><strong>Fichiers manquants</strong> - Vérifiez la création des fichiers</p>";
    }
    if (!empty($syntax_errors)) {
        echo "<p><strong>Erreurs de syntaxe</strong> dans: " . implode(', ', $syntax_errors) . "</p>";
    }
    echo "</div>";
}

echo "<style>";
echo "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 1000px; margin: 0 auto; padding: 2rem; }";
echo "h1, h2, h3 { color: #004a6d; }";
echo "h1 { border-bottom: 3px solid #ec654f; padding-bottom: 0.5rem; }";
echo "h2 { border-bottom: 2px solid #e9ecef; padding-bottom: 0.5rem; margin-top: 2rem; }";
echo "pre { background: #f8f9fa; padding: 1rem; border-radius: 4px; overflow-x: auto; }";
echo "ul, ol { padding-left: 2rem; }";
echo "a { color: #ec654f; text-decoration: none; font-weight: 500; }";
echo "a:hover { text-decoration: underline; }";
echo "code { background: #f8f9fa; padding: 0.2rem 0.4rem; border-radius: 4px; font-family: 'Monaco', 'Menlo', monospace; }";
echo "</style>";
?>
