<?php
/**
 * Script de test du système d'emails
 * Vérifie la configuration et teste l'envoi d'emails
 */

// Configuration
require_once __DIR__ . '/../forms/email-config.php';
require_once __DIR__ . '/../forms/email-service.php';

echo "<h1>🧪 Test du système d'emails</h1>\n";

// Test 1: Vérification de la configuration
echo "<h2>1. Configuration</h2>\n";
echo "<ul>\n";
echo "<li><strong>ADMIN_EMAIL:</strong> " . (defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'NON DÉFINI') . "</li>\n";
echo "<li><strong>FROM_EMAIL:</strong> " . (defined('FROM_EMAIL') ? FROM_EMAIL : 'NON DÉFINI') . "</li>\n";
echo "<li><strong>FROM_NAME:</strong> " . (defined('FROM_NAME') ? FROM_NAME : 'NON DÉFINI') . "</li>\n";
echo "<li><strong>BREVO_API_KEY:</strong> " . (defined('BREVO_API_KEY') && BREVO_API_KEY !== 'YOUR_BREVO_API_KEY_HERE' ? 'CONFIGURÉ' : 'NON CONFIGURÉ') . "</li>\n";
echo "<li><strong>FALLBACK_EMAIL_ENABLED:</strong> " . (defined('FALLBACK_EMAIL_ENABLED') && FALLBACK_EMAIL_ENABLED ? 'OUI' : 'NON') . "</li>\n";
echo "</ul>\n";

// Test 2: Vérification des fonctions PHP
echo "<h2>2. Fonctions PHP</h2>\n";
echo "<ul>\n";
echo "<li><strong>mail():</strong> " . (function_exists('mail') ? 'DISPONIBLE' : 'NON DISPONIBLE') . "</li>\n";
echo "<li><strong>curl:</strong> " . (function_exists('curl_init') ? 'DISPONIBLE' : 'NON DISPONIBLE') . "</li>\n";
echo "</ul>\n";

// Test 3: Test d'envoi d'email simple
echo "<h2>3. Test d'envoi d'email</h2>\n";

$testEmail = 'test@example.com'; // Email de test
$testSubject = 'Test système email - ' . date('Y-m-d H:i:s');
$testMessage = '<h1>Test d\'envoi d\'email</h1><p>Ceci est un test du système d\'emails d\'Osons Saint-Paul.</p>';

echo "<p><strong>Envoi vers:</strong> $testEmail</p>\n";
echo "<p><strong>Sujet:</strong> $testSubject</p>\n";

try {
    $result = EmailService::sendEmail($testEmail, $testSubject, $testMessage);
    
    if ($result['success']) {
        echo "<p style='color: green;'><strong>✅ SUCCÈS:</strong> Email envoyé avec succès via " . ($result['service'] ?? 'service inconnu') . "</p>\n";
    } else {
        echo "<p style='color: red;'><strong>❌ ÉCHEC:</strong> " . ($result['error'] ?? 'Erreur inconnue') . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ EXCEPTION:</strong> " . $e->getMessage() . "</p>\n";
}

// Test 4: Test des templates
echo "<h2>4. Test des templates</h2>\n";

$testProposal = [
    'id' => 'TEST-123',
    'data' => [
        'titre' => 'Test de proposition',
        'email' => 'test@example.com',
        'nom' => 'Test User'
    ]
];

echo "<h3>Template de confirmation:</h3>\n";
$confirmationTemplate = EmailTemplates::getConfirmationTemplate($testProposal);
echo "<p><strong>Sujet:</strong> " . htmlspecialchars($confirmationTemplate['subject']) . "</p>\n";

echo "<h3>Template d'acceptation:</h3>\n";
$approvalTemplate = EmailTemplates::getStatusUpdateTemplate($testProposal, 'approved');
echo "<p><strong>Sujet:</strong> " . htmlspecialchars($approvalTemplate['subject']) . "</p>\n";

echo "<h3>Template de rejet:</h3>\n";
$testProposal['rejection_reason'] = 'Cette proposition ne correspond pas à nos priorités actuelles.';
$rejectionTemplate = EmailTemplates::getStatusUpdateTemplate($testProposal, 'rejected');
echo "<p><strong>Sujet:</strong> " . htmlspecialchars($rejectionTemplate['subject']) . "</p>\n";

// Test 5: Vérification des logs
echo "<h2>5. Logs d'emails</h2>\n";
$logFile = __DIR__ . '/../logs/email_logs.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $logLines = explode("\n", $logs);
    $recentLogs = array_slice($logLines, -10); // 10 dernières lignes
    
    echo "<h3>10 dernières entrées de log:</h3>\n";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>\n";
    foreach ($recentLogs as $log) {
        if (!empty(trim($log))) {
            echo htmlspecialchars($log) . "\n";
        }
    }
    echo "</pre>\n";
} else {
    echo "<p style='color: orange;'>⚠️ Fichier de log non trouvé: $logFile</p>\n";
}

echo "<h2>6. Recommandations</h2>\n";
echo "<ul>\n";

if (!defined('BREVO_API_KEY') || BREVO_API_KEY === 'YOUR_BREVO_API_KEY_HERE') {
    echo "<li style='color: orange;'>⚠️ <strong>Configurez Brevo:</strong> Pour une meilleure délivrabilité, configurez une clé API Brevo</li>\n";
}

if (!function_exists('mail')) {
    echo "<li style='color: red;'>❌ <strong>Fonction mail() manquante:</strong> Contactez votre hébergeur pour activer l'envoi d'emails</li>\n";
}

if (!function_exists('curl_init')) {
    echo "<li style='color: red;'>❌ <strong>cURL manquant:</strong> Nécessaire pour l'API Brevo</li>\n";
}

echo "<li>✅ <strong>Testez régulièrement:</strong> Vérifiez que les emails arrivent bien dans les boîtes de réception</li>\n";
echo "<li>✅ <strong>Surveillez les logs:</strong> Consultez /logs/email_logs.log pour diagnostiquer les problèmes</li>\n";
echo "</ul>\n";

echo "<p><strong>Test terminé le:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
?>
