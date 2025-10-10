<?php
/**
 * Test spécifique pour l'email d'acceptation de proposition
 */

require_once __DIR__ . '/../forms/email-service.php';

echo "<h1>🧪 Test email d'acceptation</h1>\n";

// Créer une proposition de test
$testProposal = [
    'id' => 'TEST-APPROVAL-123',
    'data' => [
        'titre' => 'Test de proposition d\'acceptation',
        'email' => 'pierre.vincenot@gmail.com', // Votre email pour le test
        'nom' => 'Pierre Vincenot',
        'description' => 'Ceci est une proposition de test pour vérifier l\'envoi d\'email d\'acceptation.'
    ]
];

echo "<h2>Proposition de test:</h2>\n";
echo "<ul>\n";
echo "<li><strong>ID:</strong> " . $testProposal['id'] . "</li>\n";
echo "<li><strong>Titre:</strong> " . $testProposal['data']['titre'] . "</li>\n";
echo "<li><strong>Email:</strong> " . $testProposal['data']['email'] . "</li>\n";
echo "<li><strong>Nom:</strong> " . $testProposal['data']['nom'] . "</li>\n";
echo "</ul>\n";

echo "<h2>Envoi de l'email d'acceptation...</h2>\n";

try {
    $result = EmailService::sendStatusUpdateEmail(
        $testProposal['data']['email'], 
        $testProposal, 
        'approved'
    );
    
    if ($result['success']) {
        echo "<p style='color: green;'><strong>✅ SUCCÈS:</strong> Email d'acceptation envoyé avec succès via " . ($result['service'] ?? 'service inconnu') . "</p>\n";
        echo "<p>Vérifiez votre boîte de réception (et les spams) pour l'email d'acceptation.</p>\n";
    } else {
        echo "<p style='color: red;'><strong>❌ ÉCHEC:</strong> " . ($result['error'] ?? 'Erreur inconnue') . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ EXCEPTION:</strong> " . $e->getMessage() . "</p>\n";
}

echo "<h2>Test de l'email de rejet...</h2>\n";

// Ajouter une raison de rejet
$testProposal['rejection_reason'] = 'Cette proposition de test ne correspond pas à nos priorités actuelles. Merci pour votre participation !';

try {
    $result = EmailService::sendStatusUpdateEmail(
        $testProposal['data']['email'], 
        $testProposal, 
        'rejected'
    );
    
    if ($result['success']) {
        echo "<p style='color: green;'><strong>✅ SUCCÈS:</strong> Email de rejet envoyé avec succès via " . ($result['service'] ?? 'service inconnu') . "</p>\n";
        echo "<p>Vérifiez votre boîte de réception (et les spams) pour l'email de rejet avec la raison.</p>\n";
    } else {
        echo "<p style='color: red;'><strong>❌ ÉCHEC:</strong> " . ($result['error'] ?? 'Erreur inconnue') . "</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ EXCEPTION:</strong> " . $e->getMessage() . "</p>\n";
}

echo "<p><strong>Test terminé le:</strong> " . date('Y-m-d H:i:s') . "</p>\n";
?>
