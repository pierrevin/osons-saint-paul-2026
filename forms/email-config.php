<?php
// Configuration des emails pour le système de propositions citoyennes

// ===== CONFIGURATION BREVO (RECOMMANDÉ) =====
// 1. Créez un compte sur https://www.brevo.com/
// 2. Générez une clé API dans votre dashboard
// 3. Remplacez la clé ci-dessous
define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: 'YOUR_BREVO_API_KEY_HERE');

// ===== CONFIGURATION EMAIL PHP NATIF (FALLBACK) =====
// Si Brevo n'est pas configuré, le système utilisera l'email PHP natif
define('FALLBACK_EMAIL_ENABLED', true);

// ===== CONFIGURATION DES ADRESSES =====
// Email de l'administrateur (recevra les notifications)
if (!defined('ADMIN_EMAIL')) {
    define('ADMIN_EMAIL', 'admin@osons-saintpaul.fr');
}

// Email d'expédition (doit être configuré sur votre serveur)
if (!defined('FROM_EMAIL')) {
    define('FROM_EMAIL', 'noreply@osons-saintpaul.fr');
}

// Nom de l'expéditeur
if (!defined('FROM_NAME')) {
    define('FROM_NAME', 'Osons Saint-Paul 2026');
}

// ===== CONFIGURATION AVANCÉE =====
// Activer les logs d'emails
define('EMAIL_LOGGING_ENABLED', true);

// Limite d'emails par jour par adresse (anti-spam)
define('EMAIL_DAILY_LIMIT', 10);

// ===== INSTRUCTIONS DE CONFIGURATION =====
/*
POUR CONFIGURER BREVO :
1. Allez sur https://www.brevo.com/
2. Créez un compte gratuit (100 emails/jour)
3. Dans le dashboard, allez dans "SMTP & API"
4. Créez une nouvelle clé API
5. Remplacez 'YOUR_BREVO_API_KEY_HERE' par votre vraie clé

POUR CONFIGURER L'EMAIL PHP NATIF :
1. Vérifiez que votre serveur supporte la fonction mail()
2. Configurez un serveur SMTP si nécessaire
3. Testez l'envoi d'emails depuis votre serveur

POUR TESTER :
1. Accédez à /forms/test-email.php
2. Vérifiez les logs dans /logs/email_logs.log
*/
?>
