<?php
// forms/contact.php - Traitement du formulaire de contact

// Configuration
$to_email = 'bonjour@osons-saint-paul.fr';
$from_email = 'noreply@osons-saint-paul.fr';
$site_name = 'Osons Saint-Paul';

// Fonction de validation
function validateInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fonction d'envoi d'email
function sendContactEmail($nom, $email, $objet, $message, $to_email, $from_email, $site_name) {
    $subject = "[$site_name] - " . $objet;
    
    // Corps HTML du message
    $body = "
    <!DOCTYPE html>
    <html lang='fr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Message de contact</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f9f9f9;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #004a6d;
            }
            .header h1 {
                color: #004a6d;
                margin: 0;
                font-size: 24px;
            }
            .sender {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
                border-left: 4px solid #004a6d;
            }
            .sender-name {
                font-weight: bold;
                color: #004a6d;
                font-size: 16px;
            }
            .message {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                border: 1px solid #e9ecef;
                white-space: pre-wrap;
                font-size: 14px;
                line-height: 1.8;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #e9ecef;
                color: #666;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🍃 Osons Saint-Paul</h1>
                <p>Nouveau message de contact</p>
            </div>
            
            <div class='sender'>
                <div class='sender-name'>" . htmlspecialchars($nom) . "</div>
            </div>
            
            <div class='message'>" . htmlspecialchars($message) . "</div>
            
            <div class='footer'>
                <p>Message envoyé depuis le formulaire de contact du site Osons Saint-Paul</p>
            </div>
        </div>
    </body>
    </html>";
    
    $headers = "From: $from_email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to_email, $subject, $body, $headers);
}

// Fonction de log
function logContactAttempt($nom, $email, $objet, $success) {
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'nom' => $nom,
        'email' => $email,
        'objet' => $objet,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Inconnue',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu',
        'success' => $success
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
    $success = false;
    
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
    
    // Si pas d'erreurs, envoi de l'email
    if (empty($errors)) {
        if (sendContactEmail($nom, $email, $objet, $message, $to_email, $from_email, $site_name)) {
            $success = true;
            logContactAttempt($nom, $email, $objet, true);
        } else {
            $errors[] = 'Erreur lors de l\'envoi du message. Veuillez réessayer.';
            logContactAttempt($nom, $email, $objet, false);
        }
    } else {
        logContactAttempt($nom, $email, $objet, false);
    }
    
    // Redirection avec message
    $redirect_url = '/#idees';
    
    if ($success) {
        $redirect_url .= '?success=1&message=envoye';
    } else {
        $redirect_url .= '?error=' . urlencode(implode(', ', $errors));
    }
    
    header('Location: ' . $redirect_url);
    exit;
}

// Si accès direct, rediriger vers l'accueil
header('Location: /');
exit;
?>
