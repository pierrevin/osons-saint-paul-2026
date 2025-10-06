<?php
/**
 * Test de migration de l'ancien vers le nouveau système admin
 */

echo "<h1>🧪 Test de Migration Admin</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>\n";

$tests = [];
$errors = [];

// Test 1: Vérifier que l'ancien système est désactivé
echo "<h2>1. Test désactivation ancien système</h2>\n";
$old_admin_content = file_get_contents('admin/pages/schema_admin.php');
if (strpos($old_admin_content, 'DÉSACTIVÉE') !== false) {
    echo "<span class='success'>✅ Ancien système désactivé</span><br>\n";
    $tests[] = "Ancien système désactivé";
} else {
    echo "<span class='error'>❌ Ancien système encore actif</span><br>\n";
    $errors[] = "Ancien système encore actif";
}

// Test 2: Vérifier que le nouveau système existe
echo "<h2>2. Test nouveau système</h2>\n";
if (file_exists('admin/pages/schema_admin_new.php')) {
    echo "<span class='success'>✅ Nouveau système admin existe</span><br>\n";
    $tests[] = "Nouveau système admin existe";
} else {
    echo "<span class='error'>❌ Nouveau système admin manquant</span><br>\n";
    $errors[] = "Nouveau système admin manquant";
}

// Test 3: Vérifier que la page éditeur existe
echo "<h2>3. Test page éditeur</h2>\n";
if (file_exists('admin/pages/editeur.php')) {
    echo "<span class='success'>✅ Page éditeur existe</span><br>\n";
    $tests[] = "Page éditeur existe";
} else {
    echo "<span class='error'>❌ Page éditeur manquante</span><br>\n";
    $errors[] = "Page éditeur manquante";
}

// Test 4: Vérifier la syntaxe PHP
echo "<h2>4. Test syntaxe PHP</h2>\n";
$php_files = [
    'admin/pages/schema_admin_new.php',
    'admin/pages/editeur.php',
    'admin/pages/gestion-utilisateurs.php',
    'admin/pages/logs.php'
];

foreach ($php_files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<span class='success'>✅ $file - Syntaxe OK</span><br>\n";
            $tests[] = "Syntaxe OK: " . basename($file);
        } else {
            echo "<span class='error'>❌ $file - Erreur syntaxe</span><br>\n";
            $errors[] = "Erreur syntaxe: " . basename($file);
        }
    }
}

// Test 5: Vérifier les redirections
echo "<h2>5. Test redirections</h2>\n";
$redirect_files = [
    'admin/pages/gestion-utilisateurs.php',
    'admin/pages/logs.php',
    'admin/pages/reponse-questionnaire.php'
];

foreach ($redirect_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'schema_admin_new.php') !== false) {
            echo "<span class='success'>✅ $file - Redirection vers nouveau système</span><br>\n";
            $tests[] = "Redirection OK: " . basename($file);
        } else {
            echo "<span class='warning'>⚠️ $file - Vérifier les redirections</span><br>\n";
        }
    }
}

// Résumé
echo "<h2>📊 Résumé</h2>\n";
echo "<p><strong>Tests réussis:</strong> " . count($tests) . "</p>\n";
echo "<p><strong>Erreurs:</strong> " . count($errors) . "</p>\n";

if (empty($errors)) {
    echo "<div style='background:#d4edda;padding:15px;border-radius:5px;border:1px solid #c3e6cb;'>\n";
    echo "<h3 class='success'>🎉 Migration réussie !</h3>\n";
    echo "<p>L'ancien système est désactivé et le nouveau système est opérationnel.</p>\n";
    echo "<p><strong>Prochaines étapes:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ Tester l'interface admin: <a href='admin/pages/schema_admin_new.php' target='_blank'>schema_admin_new.php</a></li>\n";
    echo "<li>✅ Tester l'interface éditeur: <a href='admin/pages/editeur.php' target='_blank'>editeur.php</a></li>\n";
    echo "<li>✅ Supprimer l'ancien fichier schema_admin.php</li>\n";
    echo "</ul>\n";
    echo "</div>\n";
} else {
    echo "<div style='background:#f8d7da;padding:15px;border-radius:5px;border:1px solid #f5c6cb;'>\n";
    echo "<h3 class='error'>❌ Erreurs détectées</h3>\n";
    echo "<ul>\n";
    foreach ($errors as $error) {
        echo "<li>$error</li>\n";
    }
    echo "</ul>\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<p><em>Test effectué le " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
