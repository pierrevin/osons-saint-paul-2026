<?php
// Script simple de déverrouillage
echo "🔓 Déverrouillage du compte admin...\n";

// Supprimer le fichier de tentatives
$attempts_file = __DIR__ . '/logs/login_attempts.json';
if (file_exists($attempts_file)) {
    unlink($attempts_file);
    echo "✅ Fichier de tentatives supprimé\n";
} else {
    echo "ℹ️ Aucun fichier de tentatives trouvé\n";
}

// Créer un fichier vide
file_put_contents($attempts_file, '{}');
echo "✅ Fichier de tentatives réinitialisé\n";

echo "\n🎯 Compte admin déverrouillé !\n";
echo "Vous pouvez maintenant vous reconnecter.\n";
?>
