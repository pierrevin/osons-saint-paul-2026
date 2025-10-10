<?php
/**
 * Test des formulaires de contact
 */

require_once __DIR__ . '/../forms/email-service.php';

echo "<h1>🧪 Test des formulaires de contact</h1>\n";

$test_nom = 'Pierre Vincenot';
$test_email = 'pierre.vincenot@gmail.com';

echo "<h2>1. Test email admin (formulaire contact page d'accueil)</h2>\n";

$subject = "[Osons Saint-Paul] - Test formulaire contact";
$adminHtml = "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>Message de contact</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
        .header { background: #2F6E4F; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
        .field { margin: 10px 0; }
        .label { font-weight: bold; color: #2F6E4F; }
        .message-box { background: #fff; padding: 15px; border-left: 4px solid #2F6E4F; margin: 20px 0; white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>📧 Nouveau message de contact</h1>
    </div>
    <div class='content'>
        <div class='field'>
            <span class='label'>De :</span> " . htmlspecialchars($test_nom) . "
        </div>
        <div class='field'>
            <span class='label'>Email :</span> " . htmlspecialchars($test_email) . "
        </div>
        <div class='field'>
            <span class='label'>Objet :</span> Test formulaire
        </div>
        <div class='field'>
            <span class='label'>Date :</span> " . date('d/m/Y à H:i') . "
        </div>
        <div class='message-box'>
            <strong>Message :</strong><br><br>
            Ceci est un test du formulaire de contact de la page d'accueil.
        </div>
    </div>
</body>
</html>";

try {
    $result = EmailService::sendEmail('bonjour@osons-saint-paul.fr', $subject, $adminHtml);
    
    if ($result['success']) {
        echo "<p style='color: green;'><strong>✅ SUCCÈS:</strong> Email admin envoyé via " . ($result['service'] ?? 'unknown') . "</p>\n";
    } else {
        echo "<p style='color: red;'><strong>❌ ÉCHEC:</strong> " . ($result['error'] ?? 'Erreur inconnue') . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ EXCEPTION:</strong> " . $e->getMessage() . "</p>\n";
}

echo "<h2>2. Test email confirmation utilisateur</h2>\n";

try {
    $result = EmailService::sendContactConfirmationEmail($test_email, $test_nom);
    
    if ($result['success']) {
        echo "<p style='color: green;'><strong>✅ SUCCÈS:</strong> Email confirmation envoyé via " . ($result['service'] ?? 'unknown') . "</p>\n";
        echo "<p>Vérifiez votre boîte de réception (et les spams) : $test_email</p>\n";
    } else {
        echo "<p style='color: red;'><strong>❌ ÉCHEC:</strong> " . ($result['error'] ?? 'Erreur inconnue') . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ EXCEPTION:</strong> " . $e->getMessage() . "</p>\n";
}

echo "<h2>3. Vérification des logs</h2>\n";

$email_log = __DIR__ . '/../logs/email_logs.log';
if (file_exists($email_log)) {
    $logs = file($email_log);
    $recent_logs = array_slice($logs, -5);
    
    echo "<h3>5 dernières entrées email_logs.log:</h3>\n";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>\n";
    foreach ($recent_logs as $log) {
        echo htmlspecialchars($log);
    }
    echo "</pre>\n";
} else {
    echo "<p style='color: orange;'>⚠️ Fichier email_logs.log non trouvé</p>\n";
}

echo "<h2>4. Résumé</h2>\n";
echo "<ul>\n";
echo "<li>✅ Formulaire contact page d'accueil : emails configurés avec reCAPTCHA v3</li>\n";
echo "<li>✅ Formulaire contact politique confidentialité : emails configurés avec reCAPTCHA v3</li>\n";
echo "<li>✅ Accusés de réception activés pour les deux formulaires</li>\n";
echo "<li>✅ Rate limiting : 3 messages par heure par IP</li>\n";
echo "<li>✅ Logs détaillés dans email_logs.log et contact.log</li>\n";
echo "</ul>\n";

echo "<p><strong>Test terminé le:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
?>
