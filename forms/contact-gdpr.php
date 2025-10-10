<?php
session_start();
ob_start(); // Capture la sortie pour éviter "headers already sent"

require_once __DIR__ . '/email-config.php';
require_once __DIR__ . '/email-service.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Vérification reCAPTCHA v3
    if (isset($_POST['recaptcha_token'])) {
        require_once __DIR__ . '/recaptcha-config.php';
        $recaptcha_secret = getRecaptchaSecret();
        $recaptcha_token = $_POST['recaptcha_token'];
        
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_data = [
            'secret' => $recaptcha_secret,
            'response' => $recaptcha_token,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];
        
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($recaptcha_data)
            ]
        ];
        
        $context = stream_context_create($options);
        $recaptcha_response = file_get_contents($recaptcha_url, false, $context);
        $recaptcha_result = json_decode($recaptcha_response, true);
        
        // Vérifier le score (0.0 = bot, 1.0 = humain)
        if (!$recaptcha_result['success'] || $recaptcha_result['score'] < 0.5) {
            error_log('reCAPTCHA contact échec - Score: ' . ($recaptcha_result['score'] ?? 'N/A'));
            $_SESSION['error'] = 'Erreur de validation reCAPTCHA. Veuillez réessayer.';
            header('Location: politique-confidentialite.php#contact-form');
            exit;
        }
    } else {
        error_log('reCAPTCHA contact - Token manquant');
        $_SESSION['error'] = 'Erreur de validation. Veuillez réessayer.';
        header('Location: politique-confidentialite.php#contact-form');
        exit;
    }
    
    // Validation des champs obligatoires
    $required_fields = ['nom', 'email', 'sujet', 'message'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $_SESSION['error'] = 'Tous les champs sont obligatoires.';
            header('Location: politique-confidentialite.php#contact-form');
            exit;
        }
    }
    
    // Validation de l'email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Adresse email invalide.';
        header('Location: politique-confidentialite.php#contact-form');
        exit;
    }
    
    // Nettoyage des données
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $sujet = htmlspecialchars(trim($_POST['sujet']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    // Rate limiting simple (3 messages par IP en 1 heure)
    $ip = $_SERVER['REMOTE_ADDR'];
    $rate_limit_file = '../logs/contact_rate_limit.json';
    $rate_limit_data = [];
    
    if (file_exists($rate_limit_file)) {
        $rate_limit_data = json_decode(file_get_contents($rate_limit_file), true) ?: [];
    }
    
    // Nettoyer les vieilles entrées (>1 heure)
    $current_time = time();
    $rate_limit_data = array_filter($rate_limit_data, function($timestamp) use ($current_time) {
        return ($current_time - $timestamp) < 3600; // 1 heure
    });
    
    // Vérifier le nombre de soumissions de cette IP
    $ip_submissions = array_filter($rate_limit_data, function($timestamp, $key) use ($ip) {
        return strpos($key, $ip) === 0;
    }, ARRAY_FILTER_USE_BOTH);
    
    if (count($ip_submissions) >= 3) {
        $_SESSION['error'] = 'Trop de messages envoyés. Réessayez dans 1 heure.';
        header('Location: politique-confidentialite.php#contact-form');
        exit;
    }
    
    // Préparer l'email
    $to = 'bonjour@osons-saint-paul.fr'; // Email protégé, pas exposé dans le HTML
    $subject_email = "Contact RGPD - " . $sujet;
    
    $message_html = "
    <html>
    <head>
        <title>Nouveau message de contact</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #EC654F; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
            .field { margin: 15px 0; padding: 10px; background: white; border-left: 4px solid #EC654F; }
            .label { font-weight: bold; color: #2F6E4F; }
            .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 0.9rem; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📧 Nouveau message de contact RGPD</h1>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>De :</span> " . htmlspecialchars($nom) . " (" . htmlspecialchars($email) . ")
                </div>
                <div class='field'>
                    <span class='label'>Sujet :</span> " . htmlspecialchars($sujet) . "
                </div>
                <div class='field'>
                    <span class='label'>Message :</span><br><br>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                <div class='footer'>
                    <p><strong>IP :</strong> " . htmlspecialchars($ip) . "</p>
                    <p><strong>Date :</strong> " . date('Y-m-d H:i:s') . "</p>
                    <p><strong>User-Agent :</strong> " . htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Envoyer l'email à l'admin
    $admin_result = EmailService::sendEmail($to, $subject_email, $message_html);
    
    // Envoyer l'email de confirmation à l'utilisateur
    $user_result = EmailService::sendContactConfirmationEmail($email, $nom);
    
    $email_sent = $admin_result['success'] && $user_result['success'];
    
    // Enregistrer dans les logs
    $log_file = '../logs/contact_logs.log';
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] De: $nom ($email) | Sujet: $sujet | Envoyé: " . ($email_sent ? 'OUI' : 'NON') . " | IP: $ip\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    
    // Mettre à jour rate limiting
    if ($email_sent) {
        $rate_limit_data[$ip . '_' . time()] = $current_time;
        file_put_contents($rate_limit_file, json_encode($rate_limit_data), LOCK_EX);
        
        $_SESSION['success'] = 'Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.';
    } else {
        $_SESSION['error'] = 'Erreur lors de l\'envoi du message. Veuillez réessayer plus tard.';
    }
    
    header('Location: politique-confidentialite.php#contact-form');
    exit;
}

// Si accès direct (GET), rediriger vers la politique
header('Location: politique-confidentialite.php#contact-form');
exit;
?>

