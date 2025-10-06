<?php
/**
 * Script de nettoyage sécurisé du code de debug
 */

echo "<h1>🧹 Nettoyage du Code de Debug</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>\n";

// Fichiers à nettoyer
$files_to_clean = [
    'admin/pages/editeur.php',
    'admin/pages/schema_admin_new.php',
    'admin/pages/reponse-questionnaire.php',
    'admin/assets/js/admin-core.js',
    'admin/assets/js/admin-actions.js',
    'admin/assets/js/admin-modals.js',
    'admin/assets/js/admin-tabs.js',
    'script.js',
    'equipe-formulaire.php'
];

// Patterns de debug à supprimer (avec contexte pour éviter les bugs)
$debug_patterns = [
    // Console.log simples
    '/\s*console\.log\([^)]*\);\s*/',
    '/\s*console\.debug\([^)]*\);\s*/',
    '/\s*console\.warn\([^)]*\);\s*/',
    '/\s*console\.error\([^)]*\);\s*/',
    
    // Debugger statements
    '/\s*debugger;\s*/',
    
    // PHP debug
    '/\s*error_log\([^)]*\);\s*/',
    '/\s*var_dump\([^)]*\);\s*/',
    '/\s*print_r\([^)]*\);\s*/',
    
    // Commentaires de debug
    '/\/\/\s*Debug[^\n]*\n/',
    '/\/\/\s*TODO[^\n]*\n/',
    '/\/\*\s*Debug[^*]*\*\/\s*/',
];

// Patterns à conserver (code fonctionnel qui ressemble à du debug)
$preserve_patterns = [
    // Gestion d'erreurs légitimes
    '/console\.error\([^)]*\);\s*alert\(/',
    '/console\.error\([^)]*\);\s*showToast\(/',
    '/\.catch\(error\s*=>\s*\{[^}]*console\.error/',
    
    // Logs de sécurité
    '/error_log\([^)]*security[^)]*\)/',
    '/error_log\([^)]*login[^)]*\)/',
    '/error_log\([^)]*auth[^)]*\)/',
];

echo "<h2>🔍 Analyse des fichiers</h2>\n";

$total_cleaned = 0;
$total_files = 0;
$errors = [];

foreach ($files_to_clean as $file) {
    if (!file_exists($file)) {
        echo "<p class='warning'>⚠️ Fichier non trouvé: $file</p>\n";
        continue;
    }
    
    $total_files++;
    $original_content = file_get_contents($file);
    $cleaned_content = $original_content;
    $file_cleaned = 0;
    
    echo "<h3>📄 Nettoyage de $file</h3>\n";
    
    // Vérifier les patterns à préserver
    $preserve_found = [];
    foreach ($preserve_patterns as $pattern) {
        if (preg_match_all($pattern, $cleaned_content, $matches)) {
            $preserve_found = array_merge($preserve_found, $matches[0]);
        }
    }
    
    // Nettoyer les patterns de debug
    foreach ($debug_patterns as $pattern) {
        $matches = [];
        if (preg_match_all($pattern, $cleaned_content, $matches)) {
            foreach ($matches[0] as $match) {
                // Vérifier si c'est un pattern à préserver
                $should_preserve = false;
                foreach ($preserve_found as $preserve) {
                    if (strpos($preserve, $match) !== false) {
                        $should_preserve = true;
                        break;
                    }
                }
                
                if (!$should_preserve) {
                    $cleaned_content = str_replace($match, '', $cleaned_content);
                    $file_cleaned++;
                    echo "<p class='success'>✅ Supprimé: " . htmlspecialchars(trim($match)) . "</p>\n";
                } else {
                    echo "<p class='warning'>⚠️ Conservé (fonctionnel): " . htmlspecialchars(trim($match)) . "</p>\n";
                }
            }
        }
    }
    
    // Nettoyer les lignes vides multiples
    $cleaned_content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $cleaned_content);
    
    // Sauvegarder si des changements ont été faits
    if ($cleaned_content !== $original_content) {
        // Créer un backup
        $backup_file = $file . '.backup-' . date('Y-m-d-H-i-s');
        if (copy($file, $backup_file)) {
            // Écrire le fichier nettoyé
            if (file_put_contents($file, $cleaned_content)) {
                echo "<p class='success'>✅ Fichier nettoyé: $file_cleaned éléments supprimés</p>\n";
                echo "<p class='success'>📁 Backup créé: $backup_file</p>\n";
                $total_cleaned += $file_cleaned;
            } else {
                $errors[] = "Impossible d'écrire: $file";
                echo "<p class='error'>❌ Erreur écriture: $file</p>\n";
            }
        } else {
            $errors[] = "Impossible de créer backup: $file";
            echo "<p class='error'>❌ Erreur backup: $file</p>\n";
        }
    } else {
        echo "<p class='success'>✅ Aucun debug trouvé dans $file</p>\n";
    }
}

echo "<h2>📊 Résultats</h2>\n";
echo "<p><strong>Fichiers traités:</strong> $total_files</p>\n";
echo "<p><strong>Éléments de debug supprimés:</strong> $total_cleaned</p>\n";

if (!empty($errors)) {
    echo "<h3>❌ Erreurs</h3>\n";
    foreach ($errors as $error) {
        echo "<p class='error'>$error</p>\n";
    }
} else {
    echo "<p class='success'>🎉 Nettoyage réussi sans erreur!</p>\n";
}

// Vérification de la syntaxe PHP
echo "<h2>🔍 Vérification syntaxe PHP</h2>\n";
$php_files = array_filter($files_to_clean, function($file) {
    return file_exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php';
});

foreach ($php_files as $file) {
    $output = shell_exec("php -l $file 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "<p class='success'>✅ $file - Syntaxe OK</p>\n";
    } else {
        echo "<p class='error'>❌ $file - Erreur syntaxe: $output</p>\n";
    }
}

echo "<hr>\n";
echo "<p><em>Nettoyage effectué le " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
