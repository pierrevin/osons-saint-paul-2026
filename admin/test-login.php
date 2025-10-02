<?php
// Test simple de la page de login
echo "🔍 Test de la page de login...\n";

// Test 1: Session
echo "Test 1: Session...\n";
session_start();
echo "✅ Session démarrée\n";

// Test 2: UserManager
echo "Test 2: UserManager...\n";
try {
    require_once __DIR__ . '/includes/user_manager.php';
    $user_manager = new UserManager();
    echo "✅ UserManager chargé\n";
} catch (Exception $e) {
    echo "❌ Erreur UserManager: " . $e->getMessage() . "\n";
}

// Test 3: Authentification simple
echo "Test 3: Authentification...\n";
try {
    $result = $user_manager->authenticate('admin', 'admin2026');
    if ($result['success']) {
        echo "✅ Authentification admin OK\n";
    } else {
        echo "❌ Authentification admin échouée: " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur authentification: " . $e->getMessage() . "\n";
}

echo "\n🎯 Test terminé !\n";
?>
