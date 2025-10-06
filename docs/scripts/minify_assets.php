<?php
/**
 * Script de minification des assets CSS/JS
 */

echo "<h1>⚡ Minification des Assets</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>\n";

// Fonction de minification CSS
function minifyCSS($css) {
    // Supprimer les commentaires
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    
    // Supprimer les espaces inutiles
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/;\s*}/', '}', $css);
    $css = preg_replace('/{\s*/', '{', $css);
    $css = preg_replace('/;\s*/', ';', $css);
    $css = preg_replace('/,\s*/', ',', $css);
    $css = preg_replace('/:\s*/', ':', $css);
    
    // Supprimer les espaces autour des opérateurs
    $css = preg_replace('/\s*([{}:;,>+~])\s*/', '$1', $css);
    
    return trim($css);
}

// Fonction de minification JS
function minifyJS($js) {
    // Supprimer les commentaires de ligne
    $js = preg_replace('/\/\/.*$/m', '', $js);
    
    // Supprimer les commentaires de bloc
    $js = preg_replace('/\/\*.*?\*\//s', '', $js);
    
    // Supprimer les espaces inutiles
    $js = preg_replace('/\s+/', ' ', $js);
    $js = preg_replace('/;\s*/', ';', $js);
    $js = preg_replace('/{\s*/', '{', $js);
    $js = preg_replace('/}\s*/', '}', $js);
    $js = preg_replace('/,\s*/', ',', $js);
    $js = preg_replace('/:\s*/', ':', $js);
    
    // Supprimer les espaces autour des opérateurs
    $js = preg_replace('/\s*([{}:;,=+\-*\/<>!&|])\s*/', '$1', $js);
    
    return trim($js);
}

// Fichiers à minifier
$files_to_minify = [
    // CSS
    'styles.css' => 'css',
    'admin/assets/css/admin-core.css' => 'css',
    'admin/assets/css/admin-components.css' => 'css',
    'admin/assets/css/admin-sections.css' => 'css',
    'admin/assets/css/admin.css' => 'css',
    
    // JS
    'script.js' => 'js',
    'admin/assets/js/admin-core.js' => 'js',
    'admin/assets/js/admin-actions.js' => 'js',
    'admin/assets/js/admin-modals.js' => 'js',
    'admin/assets/js/admin-tabs.js' => 'js',
    'admin/assets/js/admin-image-cropper.js' => 'js'
];

echo "<h2>🔍 Analyse des fichiers</h2>\n";

$total_original_size = 0;
$total_minified_size = 0;
$total_files = 0;
$errors = [];

foreach ($files_to_minify as $file => $type) {
    if (!file_exists($file)) {
        echo "<p class='warning'>⚠️ Fichier non trouvé: $file</p>\n";
        continue;
    }
    
    $total_files++;
    $original_content = file_get_contents($file);
    $original_size = strlen($original_content);
    $total_original_size += $original_size;
    
    echo "<h3>📄 Minification de $file</h3>\n";
    echo "<p>Taille originale: " . round($original_size/1024, 2) . " KB</p>\n";
    
    // Minifier selon le type
    if ($type === 'css') {
        $minified_content = minifyCSS($original_content);
    } else {
        $minified_content = minifyJS($original_content);
    }
    
    $minified_size = strlen($minified_content);
    $total_minified_size += $minified_size;
    $reduction = round((1 - $minified_size / $original_size) * 100, 1);
    
    echo "<p>Taille minifiée: " . round($minified_size/1024, 2) . " KB</p>\n";
    echo "<p>Réduction: $reduction%</p>\n";
    
    // Créer un backup
    $backup_file = $file . '.backup-' . date('Y-m-d-H-i-s');
    if (copy($file, $backup_file)) {
        // Écrire le fichier minifié
        if (file_put_contents($file, $minified_content)) {
            echo "<p class='success'>✅ Fichier minifié avec succès</p>\n";
            echo "<p class='success'>📁 Backup créé: $backup_file</p>\n";
        } else {
            $errors[] = "Impossible d'écrire: $file";
            echo "<p class='error'>❌ Erreur écriture: $file</p>\n";
        }
    } else {
        $errors[] = "Impossible de créer backup: $file";
        echo "<p class='error'>❌ Erreur backup: $file</p>\n";
    }
}

echo "<h2>📊 Résultats</h2>\n";
echo "<p><strong>Fichiers traités:</strong> $total_files</p>\n";
echo "<p><strong>Taille originale:</strong> " . round($total_original_size/1024, 2) . " KB</p>\n";
echo "<p><strong>Taille minifiée:</strong> " . round($total_minified_size/1024, 2) . " KB</p>\n";

$total_reduction = round((1 - $total_minified_size / $total_original_size) * 100, 1);
$space_saved = round(($total_original_size - $total_minified_size)/1024, 2);

echo "<p><strong>Réduction totale:</strong> $total_reduction%</p>\n";
echo "<p><strong>Espace économisé:</strong> $space_saved KB</p>\n";

if (!empty($errors)) {
    echo "<h3>❌ Erreurs</h3>\n";
    foreach ($errors as $error) {
        echo "<p class='error'>$error</p>\n";
    }
} else {
    echo "<p class='success'>🎉 Minification réussie sans erreur!</p>\n";
}

// Vérification de la syntaxe
echo "<h2>🔍 Vérification syntaxe</h2>\n";
$js_files = array_filter($files_to_minify, function($type) {
    return $type === 'js';
}, ARRAY_FILTER_USE_KEY);

foreach ($js_files as $file => $type) {
    if (file_exists($file)) {
        // Test simple de syntaxe JS (vérifier les accolades)
        $content = file_get_contents($file);
        $open_braces = substr_count($content, '{');
        $close_braces = substr_count($content, '}');
        $open_parens = substr_count($content, '(');
        $close_parens = substr_count($content, ')');
        
        if ($open_braces === $close_braces && $open_parens === $close_parens) {
            echo "<p class='success'>✅ $file - Syntaxe OK</p>\n";
        } else {
            echo "<p class='error'>❌ $file - Erreur de syntaxe détectée</p>\n";
        }
    }
}

echo "<hr>\n";
echo "<p><em>Minification effectuée le " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
