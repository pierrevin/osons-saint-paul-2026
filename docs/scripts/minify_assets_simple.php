<?php
/**
 * Script de minification simplifié des assets CSS/JS
 */

echo "<h1>⚡ Minification Simplifiée des Assets</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>\n";

// Fonction de minification CSS simple
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
    
    return trim($css);
}

// Fonction de minification JS simple
function minifyJS($js) {
    // Supprimer les commentaires de ligne
    $js = preg_replace('/\/\/.*$/m', '', $js);
    
    // Supprimer les commentaires de bloc
    $js = preg_replace('/\/\*.*?\*\//s', '', $js);
    
    // Supprimer les espaces inutiles (plus conservateur)
    $js = preg_replace('/\s+/', ' ', $js);
    $js = preg_replace('/;\s*/', ';', $js);
    $js = preg_replace('/{\s*/', '{', $js);
    $js = preg_replace('/}\s*/', '}', $js);
    $js = preg_replace('/,\s*/', ',', $js);
    $js = preg_replace('/:\s*/', ':', $js);
    
    return trim($js);
}

// Fichiers à minifier (seulement les plus importants)
$files_to_minify = [
    // CSS principaux
    'styles.css' => 'css',
    'admin/assets/css/admin.css' => 'css',
    
    // JS principaux
    'script.js' => 'js',
    'admin/assets/js/admin-core.js' => 'js'
];

echo "<h2>🔍 Minification des fichiers principaux</h2>\n";

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
    $backup_file = $file . '.backup-simple-' . date('Y-m-d-H-i-s');
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

echo "<hr>\n";
echo "<p><em>Minification simplifiée effectuée le " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
