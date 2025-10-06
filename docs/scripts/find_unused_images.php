<?php
/**
 * Script pour identifier les images non utilisées
 */

echo "<h1>🔍 Analyse des Images Non Utilisées</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .unused{color:red;} .used{color:green;} .warning{color:orange;}</style>\n";

// Dossiers à exclure (images importantes)
$exclude_dirs = ['gallery_optimized'];

// Trouver toutes les images
$all_images = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('uploads/'));
foreach ($iterator as $file) {
    if ($file->isFile() && preg_match('/\.(jpg|jpeg|png|webp)$/i', $file->getFilename())) {
        $path = $file->getPathname();
        $skip = false;
        foreach ($exclude_dirs as $exclude) {
            if (strpos($path, $exclude) !== false) {
                $skip = true;
                break;
            }
        }
        if (!$skip) {
            $all_images[] = $path;
        }
    }
}

echo "<h2>📊 Statistiques</h2>\n";
echo "<p><strong>Total images trouvées:</strong> " . count($all_images) . "</p>\n";

// Analyser l'utilisation dans les fichiers
$used_images = [];
$unused_images = [];

// Fichiers à analyser
$files_to_scan = [
    'index.php',
    'admin/pages/schema_admin_new.php',
    'admin/pages/editeur.php',
    'admin/pages/gestion-utilisateurs.php',
    'admin/pages/logs.php',
    'admin/pages/reponse-questionnaire.php',
    'equipe-formulaire.php',
    'load_gallery_images.php',
    'data/site_content.json',
    'admin/users.json'
];

// Ajouter tous les fichiers PHP
$php_files = glob('*.php');
$admin_php_files = glob('admin/**/*.php', GLOB_BRACE);
$all_files = array_merge($files_to_scan, $php_files, $admin_php_files);

echo "<h2>🔍 Analyse des fichiers</h2>\n";
echo "<p>Analyse de " . count($all_files) . " fichiers...</p>\n";

foreach ($all_files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        foreach ($all_images as $image) {
            $image_name = basename($image);
            $image_path = str_replace('uploads/', '', $image);
            
            // Rechercher différentes variantes du nom
            $patterns = [
                $image_name,
                $image_path,
                'uploads/' . $image_name,
                'uploads/' . $image_path,
                str_replace('uploads/', '', $image)
            ];
            
            foreach ($patterns as $pattern) {
                if (strpos($content, $pattern) !== false) {
                    if (!in_array($image, $used_images)) {
                        $used_images[] = $image;
                    }
                    break;
                }
            }
        }
    }
}

// Identifier les images non utilisées
$unused_images = array_diff($all_images, $used_images);

echo "<h2>📈 Résultats</h2>\n";
echo "<p><strong>Images utilisées:</strong> " . count($used_images) . "</p>\n";
echo "<p><strong>Images non utilisées:</strong> " . count($unused_images) . "</p>\n";

if (!empty($unused_images)) {
    echo "<h3>🗑️ Images non utilisées (à supprimer)</h3>\n";
    echo "<ul>\n";
    
    $total_size = 0;
    foreach ($unused_images as $image) {
        $size = filesize($image);
        $total_size += $size;
        $size_mb = round($size / 1024 / 1024, 2);
        echo "<li class='unused'>" . basename($image) . " <em>(" . $size_mb . " MB)</em></li>\n";
    }
    echo "</ul>\n";
    
    $total_size_mb = round($total_size / 1024 / 1024, 2);
    echo "<p><strong>Espace à libérer:</strong> " . $total_size_mb . " MB</p>\n";
    
    echo "<h3>⚠️ Images utilisées (à conserver)</h3>\n";
    echo "<ul>\n";
    foreach ($used_images as $image) {
        $size = filesize($image);
        $size_mb = round($size / 1024 / 1024, 2);
        echo "<li class='used'>" . basename($image) . " <em>(" . $size_mb . " MB)</em></li>\n";
    }
    echo "</ul>\n";
} else {
    echo "<p class='used'>✅ Toutes les images sont utilisées !</p>\n";
}

// Analyser les doublons potentiels
echo "<h2>🔍 Analyse des doublons potentiels</h2>\n";
$image_hashes = [];
$potential_duplicates = [];

foreach ($all_images as $image) {
    $hash = md5_file($image);
    if (isset($image_hashes[$hash])) {
        $potential_duplicates[] = [$image_hashes[$hash], $image];
    } else {
        $image_hashes[$hash] = $image;
    }
}

if (!empty($potential_duplicates)) {
    echo "<h3>🔄 Doublons détectés</h3>\n";
    foreach ($potential_duplicates as $duplicate) {
        echo "<p class='warning'>⚠️ " . basename($duplicate[0]) . " = " . basename($duplicate[1]) . "</p>\n";
    }
} else {
    echo "<p class='used'>✅ Aucun doublon détecté</p>\n";
}

echo "<hr>\n";
echo "<p><em>Analyse effectuée le " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
