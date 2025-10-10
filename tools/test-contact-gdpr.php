<?php
/**
 * Test du formulaire contact GDPR
 */

// Simuler l'environnement serveur
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'Test Script';

// Charger le fichier pour vérifier qu'il s'exécute sans erreur
echo "<h1>🧪 Test de contact-gdpr.php</h1>\n";

echo "<h2>1. Vérification du chargement des dépendances</h2>\n";

try {
    require_once __DIR__ . '/../forms/email-config.php';
    echo "<p style='color: green;'>✅ email-config.php chargé avec succès</p>\n";
    
    require_once __DIR__ . '/../forms/email-service.php';
    echo "<p style='color: green;'>✅ email-service.php chargé avec succès</p>\n";
    
    // Vérifier que les constantes sont définies
    if (defined('ADMIN_EMAIL')) {
        echo "<p style='color: green;'>✅ ADMIN_EMAIL défini : " . ADMIN_EMAIL . "</p>\n";
    } else {
        echo "<p style='color: red;'>❌ ADMIN_EMAIL non défini</p>\n";
    }
    
    if (defined('FROM_EMAIL')) {
        echo "<p style='color: green;'>✅ FROM_EMAIL défini : " . FROM_EMAIL . "</p>\n";
    } else {
        echo "<p style='color: red;'>❌ FROM_EMAIL non défini</p>\n";
    }
    
    // Vérifier que la classe EmailService existe
    if (class_exists('EmailService')) {
        echo "<p style='color: green;'>✅ Classe EmailService disponible</p>\n";
        
        // Vérifier les méthodes
        if (method_exists('EmailService', 'sendEmail')) {
            echo "<p style='color: green;'>✅ Méthode sendEmail() disponible</p>\n";
        }
        if (method_exists('EmailService', 'sendContactConfirmationEmail')) {
            echo "<p style='color: green;'>✅ Méthode sendContactConfirmationEmail() disponible</p>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ Classe EmailService non disponible</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur : " . $e->getMessage() . "</p>\n";
}

echo "<h2>2. Test d'envoi d'email de confirmation</h2>\n";

try {
    $result = EmailService::sendContactConfirmationEmail('pierre.vincenot@gmail.com', 'Pierre Vincenot');
    
    if ($result['success']) {
        echo "<p style='color: green;'>✅ Email de confirmation envoyé avec succès</p>\n";
        echo "<p>Service utilisé : " . ($result['service'] ?? 'unknown') . "</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Échec de l'envoi : " . ($result['error'] ?? 'unknown') . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception : " . $e->getMessage() . "</p>\n";
}

echo "<h2>3. Vérification du fichier contact-gdpr.php</h2>\n";

// Vérifier la syntaxe
$output = [];
$return_var = 0;
exec('php -l ' . __DIR__ . '/../forms/contact-gdpr.php 2>&1', $output, $return_var);

if ($return_var === 0) {
    echo "<p style='color: green;'>✅ Pas d'erreur de syntaxe</p>\n";
} else {
    echo "<p style='color: red;'>❌ Erreur de syntaxe détectée :</p>\n";
    echo "<pre>" . implode("\n", $output) . "</pre>\n";
}

echo "<h2>4. Résumé des corrections</h2>\n";
echo "<ul>\n";
echo "<li>✅ Chemins absolus utilisés : <code>require_once __DIR__ . '/email-config.php';</code></li>\n";
echo "<li>✅ Utilisation de email-config.php au lieu de config.php</li>\n";
echo "<li>✅ Ajout de ob_start() pour éviter les problèmes de headers</li>\n";
echo "<li>✅ Suppression des fonctions CSRF non utilisées</li>\n";
echo "<li>✅ Cohérence avec contact.php (qui fonctionne)</li>\n";
echo "</ul>\n";

echo "<h2>5. Test à effectuer dans le navigateur</h2>\n";
echo "<ol>\n";
echo "<li>Accéder à : <strong>https://osons-saint-paul.fr/forms/politique-confidentialite.php#contact-form</strong></li>\n";
echo "<li>Remplir le formulaire de contact</li>\n";
echo "<li>Soumettre le formulaire</li>\n";
echo "<li>Vérifier : pas d'erreur 500, redirection avec message de succès</li>\n";
echo "<li>Vérifier les emails reçus (admin + confirmation utilisateur)</li>\n";
echo "</ol>\n";

echo "<p><strong>Test terminé le:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
?>
