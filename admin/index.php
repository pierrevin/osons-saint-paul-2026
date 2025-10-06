<?php
// Redirection vers l'interface d'administration avec vérification d'authentification
session_start();
require_once __DIR__ . '/config.php';

// Vérifier l'authentification
check_auth();

// Si authentifié, rediriger vers l'admin (nouvelle version)
header('Location: pages/schema_admin_new.php');
exit;
?>
