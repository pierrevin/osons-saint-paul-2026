<?php
/**
 * Script de déverrouillage de compte
 */
require_once __DIR__ . '/includes/user_manager.php';

$user_manager = new UserManager();

// Déverrouiller le compte admin
$result = $user_manager->unlockAccount('admin');

if ($result['success']) {
    echo "✅ Compte admin déverrouillé avec succès !\n";
} else {
    echo "❌ Erreur : " . $result['message'] . "\n";
}

// Afficher l'état des tentatives
$attempts = $user_manager->getLoginAttempts('admin');
echo "📊 Tentatives de connexion pour admin : " . count($attempts) . "\n";

// Nettoyer les tentatives
$user_manager->clearLoginAttempts('admin');
echo "🧹 Tentatives de connexion effacées.\n";

echo "\n🎯 Vous pouvez maintenant vous reconnecter !\n";
?>
