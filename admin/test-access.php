<?php
/**
 * Test simple d'accès au diagnostic
 */

echo "<h1>Test d'accès</h1>";
echo "<p>Si vous voyez cette page, l'accès fonctionne !</p>";

echo "<h2>Informations système</h2>";
echo "<p><strong>Chemin actuel:</strong> " . __DIR__ . "</p>";
echo "<p><strong>URL:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p><strong>Host:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";

echo "<h2>Vérification des fichiers</h2>";

// Vérifier credentials
$credentialsPath = __DIR__ . '/../../credentials/ga-service-account.json';
echo "<p><strong>Credentials:</strong> " . $credentialsPath . "</p>";
echo "<p><strong>Existe:</strong> " . (file_exists($credentialsPath) ? "✅ Oui" : "❌ Non") . "</p>";

// Vérifier vendor
$vendorPath = __DIR__ . '/../../vendor/autoload.php';
echo "<p><strong>Vendor:</strong> " . $vendorPath . "</p>";
echo "<p><strong>Existe:</strong> " . (file_exists($vendorPath) ? "✅ Oui" : "❌ Non") . "</p>";

echo "<h2>Liens</h2>";
echo "<p><a href='test-google-analytics.php?debug=local'>Diagnostic Google Analytics</a></p>";
echo "<p><a href='schema_admin_new.php'>Dashboard Admin</a></p>";
?>
