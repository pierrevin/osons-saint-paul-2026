<?php
// Test du système de propositions citoyennes
require_once 'config.php';

echo "<h1>🧪 Test du système de propositions citoyennes</h1>";

// Test 1: Vérification des fichiers
echo "<h2>1. Vérification des fichiers</h2>";
$files_to_check = [
    'config.php' => __FILE__,
    'proposition-citoyenne.php' => 'proposition-citoyenne.php',
    'process-form.php' => 'process-form.php',
    'confirmation.php' => 'confirmation.php',
    'admin/propositions-admin.php' => 'admin/propositions-admin.php',
    'admin/manage-proposition.php' => 'admin/manage-proposition.php',
    PROPOSITIONS_DATA_FILE => PROPOSITIONS_DATA_FILE,
    SITE_CONTENT_FILE => SITE_CONTENT_FILE
];

foreach ($files_to_check as $name => $file) {
    if (file_exists($file)) {
        echo "✅ $name : OK<br>";
    } else {
        echo "❌ $name : MANQUANT<br>";
    }
}

// Test 2: Vérification des permissions
echo "<h2>2. Vérification des permissions</h2>";
$directories_to_check = [
    '../data/' => '../data/',
    '../data/backups/' => '../data/backups/',
    '../logs/' => '../logs/',
    'admin/' => 'admin/'
];

foreach ($directories_to_check as $name => $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "✅ $name : Écriture autorisée<br>";
        } else {
            echo "⚠️ $name : Pas d'écriture<br>";
        }
    } else {
        echo "❌ $name : Répertoire manquant<br>";
    }
}

// Test 3: Vérification du fichier de données
echo "<h2>3. Vérification du fichier de données</h2>";
if (file_exists(PROPOSITIONS_DATA_FILE)) {
    $content = file_get_contents(PROPOSITIONS_DATA_FILE);
    $data = json_decode($content, true);
    
    if ($data) {
        echo "✅ Structure JSON valide<br>";
        echo "📊 Nombre de propositions : " . count($data['propositions'] ?? []) . "<br>";
        echo "📈 Statistiques : " . json_encode($data['statistics'] ?? []) . "<br>";
    } else {
        echo "❌ Structure JSON invalide<br>";
    }
} else {
    echo "⚠️ Fichier de données manquant (sera créé automatiquement)<br>";
}

// Test 4: Test des fonctions utilitaires
echo "<h2>4. Test des fonctions utilitaires</h2>";

// Test generateUniqueId
$id1 = generateUniqueId();
$id2 = generateUniqueId();
if ($id1 !== $id2 && strlen($id1) > 10) {
    echo "✅ generateUniqueId() : OK<br>";
} else {
    echo "❌ generateUniqueId() : ÉCHEC<br>";
}

// Test validateEmail
if (validateEmail('test@example.com') && !validateEmail('invalid-email')) {
    echo "✅ validateEmail() : OK<br>";
} else {
    echo "❌ validateEmail() : ÉCHEC<br>";
}

// Test sanitizeInput
$input = '<script>alert("test")</script>';
$sanitized = sanitizeInput($input);
if (strpos($sanitized, '<script>') === false) {
    echo "✅ sanitizeInput() : OK<br>";
} else {
    echo "❌ sanitizeInput() : ÉCHEC<br>";
}

// Test 5: Vérification de la configuration
echo "<h2>5. Vérification de la configuration</h2>";
echo "📧 Email admin : " . ADMIN_EMAIL . "<br>";
echo "📧 Email from : " . FROM_EMAIL . "<br>";
echo "🏷️ Nom du site : " . SITE_NAME . "<br>";
echo "📁 Fichier propositions : " . PROPOSITIONS_DATA_FILE . "<br>";
echo "📁 Fichier contenu : " . SITE_CONTENT_FILE . "<br>";

// Test 6: Test de création de sauvegarde
echo "<h2>6. Test de sauvegarde</h2>";
if (file_exists(PROPOSITIONS_DATA_FILE)) {
    $backup_file = createBackup();
    if ($backup_file && file_exists($backup_file)) {
        echo "✅ Sauvegarde créée : " . basename($backup_file) . "<br>";
    } else {
        echo "❌ Échec de la sauvegarde<br>";
    }
} else {
    echo "⚠️ Pas de données à sauvegarder<br>";
}

// Test 7: Test de nettoyage
echo "<h2>7. Test de nettoyage</h2>";
$cleaned = cleanOldPropositions();
echo "🧹 Propositions nettoyées : $cleaned<br>";

// Test 8: Test de limite email
echo "<h2>8. Test de limite email</h2>";
if (checkEmailLimit('test@example.com')) {
    echo "✅ Limite email : OK<br>";
} else {
    echo "⚠️ Limite email atteinte<br>";
}

echo "<h2>🎉 Test terminé !</h2>";
echo "<p><a href='proposition-citoyenne.php'>📝 Tester le formulaire</a> | ";
echo "<a href='admin/propositions-admin.php'>🔧 Interface admin</a> | ";
echo "<a href='../index.php'>🏠 Retour au site</a></p>";

// Affichage des erreurs PHP si activées
if (ini_get('display_errors')) {
    echo "<h2>📋 Erreurs PHP</h2>";
    $errors = error_get_last();
    if ($errors) {
        echo "<pre>" . print_r($errors, true) . "</pre>";
    } else {
        echo "Aucune erreur détectée<br>";
    }
}
?>
