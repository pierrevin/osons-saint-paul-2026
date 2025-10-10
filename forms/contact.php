<?php
// forms/contact.php - Traitement du formulaire de contact avec reCAPTCHA v3 et rate limiting
session_start();

// Configuration
require_once __DIR__ . '/email-service.php';
require_once __DIR__ . '/email-config.php';

$to_email = 'bonjour@osons-saint-paul.fr';

// Fonction de validation
function validateInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fonction de rate limiting
function checkRateLimit($ip) {
    $rate_limit_file = __DIR__ . '/../logs/contact_rate_limit.json';
    $max_attempts = 3;
    $time_window = 3600; // 1 heure
    
    if (file_exists($rate_limit_file)) {
        $limits = json_decode(file_get_contents($rate_limit_file), true) ?? [];
    } else {
        $limits = [];
    }
    
    $current_time = time();
    
    // Nettoyer les anciennes entrées
    foreach ($limits as $stored_ip => $data) {
        if ($current_time - $data['first_attempt'] > $time_window) {
            unset($limits[$stored_ip]);
        }
    }
    
    // Vérifier la limite pour cette IP
    if (isset($limits[$ip])) {
        if ($limits[$ip]['count'] >= $max_attempts) {
            $time_remaining = $time_window - ($current_time - $limits[$ip]['first_attempt']);
            if ($time_remaining > 0) {
                return [
                    'allowed' => false,
                    'message' => 'Trop de messages envoyés. Veuillez réessayer dans ' . ceil($time_remaining / 60) . ' minutes.'
                ];
            } else {
                // Réinitialiser si la fenêtre est expirée
                unset($limits[$ip]);
            }
        }
    }
    
    // Incrémenter le compteur
    if (!isset($limits[$ip])) {
        $limits[$ip] = [
            'count' => 1,
            'first_attempt' => $current_time
        ];
    } else {
        $limits[$ip]['count']++;
    }
    
    // Sauvegarder
    $log_dir = dirname($rate_limit_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    file_put_contents($rate_limit_file, json_encode($limits, JSON_PRETTY_PRINT));
    
    return ['allowed' => true];
}

// Fonction de log
function logContactAttempt($nom, $email, $objet, $success, $error = null) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'nom' => $nom,
        'email' => $email,
        'objet' => $objet,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Inconnue',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu',
        'success' => $success,
        'error' => $error
    ];
    
    $log_file = __DIR__ . '/../logs/contact.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Vérification reCAPTCHA v3
    if (isset($_POST['recaptcha_token'])) {
        $recaptcha_secret = '6LeOrNorAAAAAAyrKUig543vV-h1OJlb9xefHYhA';
        $recaptcha_token = $_POST['recaptcha_token'];
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret' => $recaptcha_secret,
                    'response' => $recaptcha_token,
                    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
                ])
            ]
        ];
        
        $context = stream_context_create($options);
        $recaptcha_response = file_get_contents($recaptcha_url, false, $context);
        $recaptcha_result = json_decode($recaptcha_response, true);
        
        // Vérifier le score (0.0 = bot, 1.0 = humain)
        if (!$recaptcha_result['success'] || $recaptcha_result['score'] < 0.5) {
            error_log('reCAPTCHA échec - Score: ' . ($recaptcha_result['score'] ?? 'N/A'));
            $_SESSION['error'] = 'Erreur de validation reCAPTCHA. Veuillez réessayer.';
            header('Location: /#idees');
            exit;
        }
    } else {
        // Pas de token = tentative de bypass
        error_log('reCAPTCHA - Token manquant');
        $_SESSION['error'] = 'Erreur de validation. Veuillez réessayer.';
        header('Location: /#idees');
        exit;
    }
    
    // Rate limiting
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rate_check = checkRateLimit($ip);
    if (!$rate_check['allowed']) {
        $_SESSION['error'] = $rate_check['message'];
        header('Location: /#idees');
        exit;
    }
    
    // Récupération et validation des données
    $nom = validateInput($_POST['nom'] ?? '');
    $email = validateInput($_POST['email'] ?? '');
    $objet = validateInput($_POST['objet'] ?? '');
    $message = validateInput($_POST['message'] ?? '');
    
    // Validation
    if (empty($nom)) {
        $errors[] = 'Le nom est requis';
    }
    
    if (empty($email) || !validateEmail($email)) {
        $errors[] = 'Une adresse email valide est requise';
    }
    
    if (empty($objet)) {
        $errors[] = 'L\'objet est requis';
    }
    
    if (empty($message)) {
        $errors[] = 'Le message est requis';
    }
    
    // Vérification de la longueur
    if (strlen($message) > 2000) {
        $errors[] = 'Le message ne peut pas dépasser 2000 caractères';
    }
    
    if (strlen($objet) > 200) {
        $errors[] = 'L\'objet ne peut pas dépasser 200 caractères';
    }
    
    // Si pas d'erreurs, envoi des emails
    if (empty($errors)) {
        try {
            // Template pour l'admin
            $subject = "[Osons Saint-Paul] - " . $objet;
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
                        <span class='label'>De :</span> " . htmlspecialchars($nom) . "
                    </div>
                    <div class='field'>
                        <span class='label'>Email :</span> " . htmlspecialchars($email) . "
                    </div>
                    <div class='field'>
                        <span class='label'>Objet :</span> " . htmlspecialchars($objet) . "
                    </div>
                    <div class='field'>
                        <span class='label'>Date :</span> " . date('d/m/Y à H:i') . "
                    </div>
                    <div class='message-box'>
                        <strong>Message :</strong><br><br>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                </div>
            </body>
            </html>";
            
            // Envoi à l'admin
            $adminResult = EmailService::sendEmail($to_email, $subject, $adminHtml);
            
            // Envoi de la confirmation à l'utilisateur
            $userResult = EmailService::sendContactConfirmationEmail($email, $nom);
            
            // Log des envois
            logEmailAttempt($to_email, $subject, $adminResult);
            logEmailAttempt($email, 'Confirmation - Message bien reçu', $userResult);
            
            if ($adminResult['success'] && $userResult['success']) {
            logContactAttempt($nom, $email, $objet, true);
                $_SESSION['success'] = 'Message envoyé avec succès ! Vous recevrez une confirmation par email.';
                header('Location: /#idees');
                exit;
        } else {
                $error_msg = 'Erreur lors de l\'envoi';
                if (!$adminResult['success']) {
                    $error_msg .= ' (admin: ' . ($adminResult['error'] ?? 'unknown') . ')';
                }
                if (!$userResult['success']) {
                    $error_msg .= ' (user: ' . ($userResult['error'] ?? 'unknown') . ')';
                }
                throw new Exception($error_msg);
            }
        } catch (Exception $e) {
            error_log('Erreur envoi email contact: ' . $e->getMessage());
            logContactAttempt($nom, $email, $objet, false, $e->getMessage());
            $_SESSION['error'] = 'Erreur lors de l\'envoi du message. Veuillez réessayer.';
            header('Location: /#idees');
            exit;
        }
    } else {
        logContactAttempt($nom, $email, $objet, false, implode(', ', $errors));
        $_SESSION['error'] = implode(', ', $errors);
        header('Location: /#idees');
        exit;
    }
}

// Si accès direct, rediriger vers l'accueil
header('Location: /#idees');
exit;
?>