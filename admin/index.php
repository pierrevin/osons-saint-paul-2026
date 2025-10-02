<?php
// Redirection vers l'interface d'administration avec vérification d'authentification
require_once __DIR__ . '/config.php';

// Vérifier l'authentification
check_auth();

// Si authentifié, rediriger vers l'admin
header('Location: pages/schema_admin.php');
exit;
?>
