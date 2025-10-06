<?php
/**
 * Script de suppression sécurisée des images non utilisées
 */

echo "<h1>🗑️ Suppression des Images Non Utilisées</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>\n";

// Images à conserver absolument
$protected_images = [
    'Osons1.png',
    'citation_1759768182_68e3ee767f389.webp',
    'citation_1759769083_68e3f1fb370c2.webp',
    'member_1759785411_68e431c377a5f.webp',
    'citation_1759782127_68e424ef1aa4a.webp',
    'citation_1759768147_68e3ee53765ab.webp',
    'hero_1759748494_68e3a18ee0479.webp'
];

// Dossiers à exclure
$exclude_dirs = ['gallery_optimized'];

// Images non utilisées identifiées
$unused_images = [
    // Photos Frederic Maligne (toutes non utilisées)
    'Photo Frederic Maligne-027.jpg',
    'Photo Frederic Maligne-033.jpg',
    'Photo Frederic Maligne-032.jpg',
    'Photo Frederic Maligne-026.jpg',
    'Photo Frederic Maligne-030.jpg',
    'Photo Frederic Maligne-024.jpg',
    'Photo Frederic Maligne-025.jpg',
    'Photo Frederic Maligne-031.jpg',
    'Photo Frederic Maligne-035.jpg',
    'Photo Frederic Maligne-034.jpg',
    'Photo Frederic Maligne-022.jpg',
    'Photo Frederic Maligne-036.jpg',
    'Photo Frederic Maligne-037.jpg',
    'Photo Frederic Maligne-023.jpg',
    'Photo Frederic Maligne-044.jpg',
    'Photo Frederic Maligne-050.jpg',
    'Photo Frederic Maligne-078.jpg',
    'Photo Frederic Maligne-086.jpg',
    'Photo Frederic Maligne-079.jpg',
    'Photo Frederic Maligne-051.jpg',
    'Photo Frederic Maligne-045.jpg',
    'Photo Frederic Maligne-053.jpg',
    'Photo Frederic Maligne-047.jpg',
    'Photo Frederic Maligne-084.jpg',
    'Photo Frederic Maligne-085.jpg',
    'Photo Frederic Maligne-046.jpg',
    'Photo Frederic Maligne-052.jpg',
    'Photo Frederic Maligne-056.jpg',
    'Photo Frederic Maligne-042.jpg',
    'Photo Frederic Maligne-081.jpg',
    'Photo Frederic Maligne-080.jpg',
    'Photo Frederic Maligne-043.jpg',
    'Photo Frederic Maligne-057.jpg',
    'Photo Frederic Maligne-069.jpg',
    'Photo Frederic Maligne-041.jpg',
    'Photo Frederic Maligne-055.jpg',
    'Photo Frederic Maligne-082.jpg',
    'Photo Frederic Maligne-083.jpg',
    'Photo Frederic Maligne-054.jpg',
    'Photo Frederic Maligne-040.jpg',
    'Photo Frederic Maligne-068.jpg',
    'Photo Frederic Maligne-065.jpg',
    'Photo Frederic Maligne-071.jpg',
    'Photo Frederic Maligne-059.jpg',
    'Photo Frederic Maligne-058.jpg',
    'Photo Frederic Maligne-070.jpg',
    'Photo Frederic Maligne-064.jpg',
    'Photo Frederic Maligne-072.jpg',
    'Photo Frederic Maligne-066.jpg',
    'Photo Frederic Maligne-067.jpg',
    'Photo Frederic Maligne-073.jpg',
    'Photo Frederic Maligne-077.jpg',
    'Photo Frederic Maligne-063.jpg',
    'Photo Frederic Maligne-062.jpg',
    'Photo Frederic Maligne-076.jpg',
    'Photo Frederic Maligne-048.jpg',
    'Photo Frederic Maligne-060.jpg',
    'Photo Frederic Maligne-074.jpg',
    'Photo Frederic Maligne-075.jpg',
    'Photo Frederic Maligne-061.jpg',
    'Photo Frederic Maligne-049.jpg',
    'Photo Frederic Maligne-039.jpg',
    'Photo Frederic Maligne-038.jpg',
    'Photo Frederic Maligne-028.jpg',
    'Photo Frederic Maligne-029.jpg',
    
    // Images de test et doublons
    'placeholder.jpg',
    'member_2_1759346918.jpg',
    'member_2_1759323852.jpg',
    'member_2_1759322969.jpeg',
    'member_24_1759221524.jpeg',
    'member_25_1759323662.png',
    'member_3_1759324004.jpg',
    
    // Anciennes versions non utilisées
    'hero-bg_1758959369_68d7970983b0b_old.webp',
    'citation2-bg_1758958516_68d793b40fdf3.webp',
    'citation2-bg_1758958519_68d793b7c8e08.webp',
    'citation2-bg_1758958504_68d793a8596c1.webp',
    'hero-bg_1758956376_68d78b58ceab4.webp',
    
    // Citations non utilisées (doublons)
    'citation_1759741391_68e385cf18426.webp',
    'citation_1759741419_68e385eb7202b.webp',
    'citation_1759741532_68e3865c2deb6.webp',
    'citation_1759741565_68e3867d2ef76.webp',
    'citation_1759741680_68e386f08618b.webp',
    'citation_1759741705_68e38709ab205.webp',
    'citation_1759741835_68e3878bac2ac.webp',
    'citation_1759741898_68e387ca7ec1f.webp',
    'citation_1759742029_68e3884dea504.webp',
    'citation_1759748532_68e3a1b4b08a2.webp',
    'citation_1759762012_68e3d65cd9dc8.webp',
    'citation_1759763185_68e3daf19ddfa.webp',
    
    // Heroes non utilisés
    'hero_1759741651_68e386d3447cf.webp',
    'hero_1759742561_68e38a610e475.webp',
    'hero_1759742870_68e38b9635b60.webp',
    'hero_1759745393_68e39571add17.webp',
    'hero_1759748218_68e3a07a5f0dd.webp',
    'hero_1759742085_68e388855c313.webp',
    
    // Members non utilisés
    'member_1759748578_68e3a1e2e7e71.webp',
    'member_1759760648_68e3d108d740f.webp',
    'member_1759761092_68e3d2c44d4dd.webp',
    'member_1759761164_68e3d30cd80ca.webp',
    'member_1759761578_68e3d4aa79fe4.webp',
    'member_1759761641_68e3d4e911c7b.webp',
    'member_1759761916_68e3d5fc8e51d.webp',
    'member_1759762073_68e3d69978de3.webp',
    'member_1759762110_68e3d6be355d5.webp',
    'member_1759762210_68e3d7224c224.webp',
    'member_1759762256_68e3d750dad09.webp',
    'member_1759762473_68e3d829c41f3.webp',
    'member_1759762712_68e3d91880d82.webp',
    'member_1759762712_68e3d918d89a7.webp',
    'member_1759762753_68e3d941a8664.webp',
    'member_1759762753_68e3d941ee40f.webp',
    'member_1759762851_68e3d9a3e69eb.webp',
    'member_1759762852_68e3d9a429c62.webp',
    'member_1759763079_68e3da87e56ed.webp',
    'member_1759763080_68e3da8835200.webp',
    
    // Backgrounds non utilisés
    'hero-bg_1759322879_68dd22ff53f5e.webp',
    'hero-bg_1759323599_68dd25cf74685.webp',
    'hero-bg_1759324297_68dd28890e331.webp',
    'hero-bg_1759324998_68dd2b46316f2.webp',
    'hero-bg_1758959369_68d7970983b0b.webp',
    'citation1-bg_1758957683_68d79073e32dc.webp',
    'citation1-bg_1759325370_68dd2cba14720.webp',
    'citation1-bg_1759325473_68dd2d21ae938.webp'
];

echo "<h2>🔍 Vérification de sécurité</h2>\n";

// Vérifier que les images protégées ne sont pas dans la liste de suppression
$conflicts = array_intersect($protected_images, $unused_images);
if (!empty($conflicts)) {
    echo "<p class='error'>❌ ERREUR: Images protégées dans la liste de suppression!</p>\n";
    foreach ($conflicts as $conflict) {
        echo "<p class='error'>- $conflict</p>\n";
    }
    exit;
}

echo "<p class='success'>✅ Aucun conflit détecté avec les images protégées</p>\n";

// Créer un backup avant suppression
$backup_dir = 'archive/unused-images-' . date('Y-m-d-H-i-s');
if (!is_dir('archive')) {
    mkdir('archive', 0755, true);
}
mkdir($backup_dir, 0755, true);

echo "<h2>🗑️ Suppression des images</h2>\n";

$deleted_count = 0;
$deleted_size = 0;
$errors = [];

foreach ($unused_images as $image_name) {
    $image_path = "uploads/$image_name";
    
    if (file_exists($image_path)) {
        // Vérifier que ce n'est pas dans un dossier protégé
        $skip = false;
        foreach ($exclude_dirs as $exclude) {
            if (strpos($image_path, $exclude) !== false) {
                $skip = true;
                break;
            }
        }
        
        if (!$skip) {
            $size = filesize($image_path);
            
            // Créer un backup avant suppression
            $backup_path = "$backup_dir/$image_name";
            if (copy($image_path, $backup_path)) {
                // Supprimer l'original
                if (unlink($image_path)) {
                    $deleted_count++;
                    $deleted_size += $size;
                    echo "<p class='success'>✅ Supprimé: $image_name (" . round($size/1024/1024, 2) . " MB)</p>\n";
                } else {
                    $errors[] = "Impossible de supprimer: $image_name";
                    echo "<p class='error'>❌ Erreur suppression: $image_name</p>\n";
                }
            } else {
                $errors[] = "Impossible de sauvegarder: $image_name";
                echo "<p class='error'>❌ Erreur backup: $image_name</p>\n";
            }
        } else {
            echo "<p class='warning'>⚠️ Ignoré (dossier protégé): $image_name</p>\n";
        }
    } else {
        echo "<p class='warning'>⚠️ Fichier non trouvé: $image_name</p>\n";
    }
}

echo "<h2>📊 Résultats</h2>\n";
echo "<p><strong>Images supprimées:</strong> $deleted_count</p>\n";
echo "<p><strong>Espace libéré:</strong> " . round($deleted_size/1024/1024, 2) . " MB</p>\n";
echo "<p><strong>Backup créé:</strong> $backup_dir</p>\n";

if (!empty($errors)) {
    echo "<h3>❌ Erreurs</h3>\n";
    foreach ($errors as $error) {
        echo "<p class='error'>$error</p>\n";
    }
} else {
    echo "<p class='success'>🎉 Suppression réussie sans erreur!</p>\n";
}

echo "<hr>\n";
echo "<p><em>Suppression effectuée le " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
