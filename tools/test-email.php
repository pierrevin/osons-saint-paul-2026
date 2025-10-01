<?php
// Test du système d'emails
require_once 'config.php';
require_once 'email-service.php';

echo "<h1>📧 Test du système d'emails</h1>";

// Test 1: Configuration
echo "<h2>1. Vérification de la configuration</h2>";
echo "📧 Email admin : " . ADMIN_EMAIL . "<br>";
echo "📧 Email from : " . FROM_EMAIL . "<br>";
echo "🏷️ Nom du site : " . SITE_NAME . "<br>";

if (BREVO_API_KEY !== 'YOUR_BREVO_API_KEY_HERE') {
    echo "✅ Brevo configuré<br>";
} else {
    echo "⚠️ Brevo non configuré (utilisera l'email PHP natif)<br>";
}

// Test 2: Envoi d'email de test
echo "<h2>2. Test d'envoi d'email</h2>";

$testEmail = 'test@example.com'; // Changez par votre email pour tester
$testData = [
    'id' => 'test_' . uniqid(),
    'titre' => 'Test proposition - Créer un jardin partagé',
    'description' => 'Cette proposition vise à créer un jardin partagé au centre-ville pour favoriser les rencontres entre habitants.',
    'categories' => ['Urbanisme & Logement', 'Environnement & Nature'],
    'email' => $testEmail,
    'telephone' => '06 12 34 56 78',
    'nom' => 'Test User'
];

echo "<p>Envoi d'un email de test à : <strong>$testEmail</strong></p>";

$result = EmailService::sendConfirmationEmail($testEmail, $testData);
logEmailAttempt($testEmail, 'Test confirmation', $result);

if ($result['success']) {
    echo "✅ Email envoyé avec succès via " . $result['service'] . "<br>";
} else {
    echo "❌ Erreur d'envoi : " . $result['error'] . "<br>";
}

// Test 3: Vérification des logs
echo "<h2>3. Vérification des logs</h2>";
$logFile = '../logs/email_logs.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $logLines = explode("\n", $logs);
    $recentLogs = array_slice($logLines, -5); // 5 dernières lignes
    
    echo "<h3>Derniers logs d'emails :</h3>";
    echo "<pre>";
    foreach ($recentLogs as $log) {
        if (!empty(trim($log))) {
            echo htmlspecialchars($log) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "⚠️ Aucun log d'email trouvé<br>";
}

// Test 4: Templates
echo "<h2>4. Test des templates</h2>";
$templates = [
    'Confirmation' => EmailTemplates::getConfirmationTemplate($testData),
    'Statut approuvé' => EmailTemplates::getStatusUpdateTemplate($testData, 'approved'),
    'Statut rejeté' => EmailTemplates::getStatusUpdateTemplate($testData, 'rejected'),
    'Intégration' => EmailTemplates::getStatusUpdateTemplate($testData, 'integrated'),
    'Notification admin' => EmailTemplates::getNewProposalNotificationTemplate($testData)
];

foreach ($templates as $name => $template) {
    echo "<h3>$name</h3>";
    echo "<p><strong>Sujet :</strong> " . htmlspecialchars($template['subject']) . "</p>";
    echo "<p><strong>Contenu :</strong> " . (strlen($template['html']) > 100 ? substr(strip_tags($template['html']), 0, 100) . "..." : strip_tags($template['html'])) . "</p>";
}

echo "<h2>🎉 Test terminé !</h2>";
echo "<p><a href='proposition-citoyenne.php'>📝 Retour au formulaire</a> | ";
echo "<a href='../admin/pages/schema_admin.php'>🔧 Interface admin</a></p>";

// Instructions
echo "<h2>📋 Instructions de configuration</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>Pour configurer Brevo :</h3>";
echo "<ol>";
echo "<li>Créez un compte sur <a href='https://www.brevo.com/' target='_blank'>brevo.com</a></li>";
echo "<li>Générez une clé API dans votre dashboard</li>";
echo "<li>Modifiez <code>forms/email-config.php</code> avec votre clé API</li>";
echo "<li>Testez à nouveau cette page</li>";
echo "</ol>";
echo "<h3>Pour configurer l'email PHP natif :</h3>";
echo "<ol>";
echo "<li>Vérifiez que votre serveur supporte la fonction <code>mail()</code></li>";
echo "<li>Configurez un serveur SMTP si nécessaire</li>";
echo "<li>Testez l'envoi d'emails depuis votre serveur</li>";
echo "</ol>";
echo "</div>";
?>
