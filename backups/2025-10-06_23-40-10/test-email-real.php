<?php
// Test d'envoi d'email avec une vraie adresse
require_once 'config.php';
require_once 'email-service.php';

echo "<h1>📧 Test d'envoi d'email réel</h1>";

// Test avec votre vraie adresse email
$testEmail = 'bonjour@osons-saint-paul.fr'; // Adresse OVH à tester
$testData = [
    'id' => 'test_' . uniqid(),
    'titre' => 'Test email réel - ' . date('H:i:s'),
    'description' => 'Ceci est un test d\'envoi d\'email avec votre vraie adresse pour vérifier la délivrabilité.',
    'categories' => ['Environnement & Nature'],
    'email' => $testEmail,
    'telephone' => '06 12 34 56 78',
    'nom' => 'Pierre Vincenot'
];

echo "<h2>📤 Envoi d'email de test</h2>";
echo "<p><strong>Destinataire :</strong> $testEmail</p>";
echo "<p><strong>Service :</strong> " . (BREVO_API_KEY !== 'YOUR_BREVO_API_KEY_HERE' ? 'Brevo' : 'PHP natif') . "</p>";

$result = EmailService::sendConfirmationEmail($testEmail, $testData);
logEmailAttempt($testEmail, 'Test email réel', $result);

echo "<h3>Résultat :</h3>";
if ($result['success']) {
    echo "<div style='background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin: 1rem 0;'>";
    echo "✅ <strong>Email envoyé avec succès !</strong><br>";
    echo "Service utilisé : " . $result['service'] . "<br>";
    echo "Vérifiez votre boîte email (et le dossier spam) dans les prochaines minutes.";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin: 1rem 0;'>";
    echo "❌ <strong>Erreur d'envoi :</strong><br>";
    echo $result['error'];
    echo "</div>";
}

echo "<h2>🔍 Diagnostic</h2>";
echo "<div style='background: #f0f8ff; padding: 1rem; border-radius: 8px; margin: 1rem 0;'>";
echo "<h3>Si vous ne recevez pas l'email :</h3>";
echo "<ol>";
echo "<li><strong>Vérifiez votre dossier spam/courrier indésirable</strong></li>";
echo "<li><strong>Vérifiez les filtres de votre fournisseur email</strong></li>";
echo "<li><strong>Attendez 5-10 minutes</strong> (délai de livraison possible)</li>";
echo "<li><strong>Testez avec une autre adresse email</strong></li>";
echo "</ol>";

echo "<h3>Solutions possibles :</h3>";
echo "<ul>";
echo "<li><strong>Configurer Brevo</strong> pour une meilleure délivrabilité</li>";
echo "<li><strong>Configurer un serveur SMTP</strong> sur votre hébergement</li>";
echo "<li><strong>Ajouter l'adresse d'expédition</strong> à vos contacts</li>";
echo "</ul>";
echo "</div>";

echo "<h2>📊 Logs récents</h2>";
$logFile = '../logs/email_logs.log';
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $logLines = explode("\n", $logs);
    $recentLogs = array_slice($logLines, -10); // 10 dernières lignes
    
    echo "<pre style='background: #f8f9fa; padding: 1rem; border-radius: 8px; overflow-x: auto;'>";
    foreach ($recentLogs as $log) {
        if (!empty(trim($log))) {
            echo htmlspecialchars($log) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>⚠️ Aucun log d'email trouvé</p>";
}

echo "<p><a href='proposition-citoyenne.php'>📝 Retour au formulaire</a> | ";
echo "<a href='../admin/pages/schema_admin.php'>🔧 Interface admin</a> | ";
echo "<a href='propositions-analytics.php'>📊 Analyse</a></p>";
?>
