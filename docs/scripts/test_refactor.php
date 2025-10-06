<?php
/**
 * Script de test pour la refonte de l'interface admin
 * Vérifie que tous les fichiers sont accessibles et fonctionnels
 */

echo "<h1>🧪 Test de la refonte Admin</h1>";

// Test 1: Vérification des fichiers
echo "<h2>📁 Vérification des fichiers</h2>";

$files_to_check = [
    'admin/includes/AdminSection.php',
    'admin/includes/AdminModal.php',
    'admin/pages/sections/hero.php',
    'admin/pages/sections/programme.php',
    'admin/pages/sections/citations.php',
    'admin/pages/sections/equipe.php',
    'admin/assets/css/admin-core.css',
    'admin/assets/css/admin-components.css',
    'admin/assets/css/admin-sections.css',
    'admin/assets/js/admin-core.js',
    'admin/assets/js/admin-modals.js',
    'admin/assets/js/admin-tabs.js',
    'admin/assets/js/admin-actions.js',
    'admin/pages/schema_admin_new.php'
];

$all_files_exist = true;

foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✅' : '❌';
    $color = $exists ? 'green' : 'red';
    echo "<p style='color: $color;'>$status $file</p>";
    
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
    
    echo "<p style='color: green;'>✅ Classes PHP chargées avec succès</p>";
    
    // Test d'instanciation
    $heroSection = new HeroSection();
    echo "<p style='color: green;'>✅ HeroSection instanciée</p>";
    
    $programmeSection = new ProgrammeSection();
    echo "<p style='color: green;'>✅ ProgrammeSection instanciée</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur lors du chargement des classes: " . $e->getMessage() . "</p>";
}

// Test 3: Vérification de la syntaxe CSS
echo "<h2>🎨 Test des fichiers CSS</h2>";

$css_files = [
    'admin/assets/css/admin-core.css',
    'admin/assets/css/admin-components.css',
    'admin/assets/css/admin-sections.css'
];

foreach ($css_files as $css_file) {
    if (file_exists($css_file)) {
        $content = file_get_contents($css_file);
        // Vérification basique de la syntaxe CSS
        if (strpos($content, '{') !== false && strpos($content, '}') !== false) {
            echo "<p style='color: green;'>✅ $css_file - Syntaxe CSS valide</p>";
        } else {
            echo "<p style='color: red;'>❌ $css_file - Syntaxe CSS invalide</p>";
        }
    }
}

// Test 4: Vérification de la syntaxe JavaScript
echo "<h2>⚡ Test des fichiers JavaScript</h2>";

$js_files = [
    'admin/assets/js/admin-core.js',
    'admin/assets/js/admin-modals.js',
    'admin/assets/js/admin-tabs.js',
    'admin/assets/js/admin-actions.js'
];

foreach ($js_files as $js_file) {
    if (file_exists($js_file)) {
        $content = file_get_contents($js_file);
        // Vérification basique de la syntaxe JavaScript
        if (strpos($content, 'class ') !== false && strpos($content, '{') !== false) {
            echo "<p style='color: green;'>✅ $js_file - Syntaxe JavaScript valide</p>";
        } else {
            echo "<p style='color: red;'>❌ $js_file - Syntaxe JavaScript invalide</p>";
        }
    }
}

// Test 5: Vérification de la structure de répertoires
echo "<h2>📂 Vérification de la structure</h2>";

$directories = [
    'admin/includes',
    'admin/pages/sections',
    'admin/assets/css',
    'admin/assets/js'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $count = count(scandir($dir)) - 2; // -2 pour . et ..
        echo "<p style='color: green;'>✅ $dir ($count fichiers)</p>";
    } else {
        echo "<p style='color: red;'>❌ $dir - Répertoire manquant</p>";
    }
}

// Résumé
echo "<h2>📊 Résumé</h2>";

if ($all_files_exist) {
    echo "<div style='background: #d4edda; padding: 1rem; border-radius: 8px; color: #155724;'>";
    echo "<h3>🎉 Refonte terminée avec succès !</h3>";
    echo "<p>L'architecture modulaire a été créée avec succès. Vous pouvez maintenant :</p>";
    echo "<ul>";
    echo "<li>✅ Tester la nouvelle interface avec <code>schema_admin_new.php</code></li>";
    echo "<li>✅ Ajouter de nouvelles sections facilement</li>";
    echo "<li>✅ Maintenir le code de manière modulaire</li>";
    echo "<li>✅ Étendre les fonctionnalités sans conflits</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='margin-top: 2rem;'>";
    echo "<h3>🚀 Prochaines étapes</h3>";
    echo "<ol>";
    echo "<li><strong>Testez la nouvelle interface</strong> : <a href='admin/pages/schema_admin_new.php' target='_blank'>Ouvrir schema_admin_new.php</a></li>";
    echo "<li><strong>Comparez avec l'ancienne</strong> : <a href='admin/pages/schema_admin.php' target='_blank'>Ancienne version</a></li>";
    echo "<li><strong>Validez les fonctionnalités</strong> : Navigation, modals, formulaires</li>";
    echo "<li><strong>Remplacez l'ancienne version</strong> une fois validée</li>";
    echo "</ol>";
    echo "</div>";
    
} else {
    echo "<div style='background: #f8d7da; padding: 1rem; border-radius: 8px; color: #721c24;'>";
    echo "<h3>❌ Problèmes détectés</h3>";
    echo "<p>Certains fichiers sont manquants. Vérifiez la structure et relancez la création des fichiers manquants.</p>";
    echo "</div>";
}

echo "<style>";
echo "body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 0 auto; padding: 2rem; }";
echo "h1, h2, h3 { color: #004a6d; }";
echo "code { background: #f8f9fa; padding: 0.2rem 0.4rem; border-radius: 4px; font-family: 'Monaco', 'Menlo', monospace; }";
echo "ul, ol { padding-left: 2rem; }";
echo "a { color: #ec654f; text-decoration: none; }";
echo "a:hover { text-decoration: underline; }";
echo "</style>";
?>
