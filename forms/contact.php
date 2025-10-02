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
    $subject = "[$site_name] Contact: " . $objet;
    
    $body = "Nouveau message de contact reçu sur le site $site_name\n\n";
    $body .= "Nom: $nom\n";
    $body .= "Email: $email\n";
    $body .= "Objet: $objet\n\n";
    $body .= "Message:\n";
    $body .= $message . "\n\n";
    $body .= "---\n";
    $body .= "Envoyé depuis le formulaire de contact du site $site_name\n";
    $body .= "Date: " . date('d/m/Y à H:i:s') . "\n";
    $body .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Inconnue');
    
    $headers = "From: $from_email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
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
        $redirect_url .= '?success=1';
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
