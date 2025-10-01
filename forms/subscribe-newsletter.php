<?php
header('Content-Type: application/json');

// Configuration
require_once 'email-config.php';

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// PROTECTION 1: Honeypot (champ caché anti-bot)
if (!empty($_POST['email_address_check'])) {
    // C'est un bot qui a rempli le champ honeypot
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Spam détecté']);
    exit;
}

// PROTECTION 2: Rate limiting (max 3 inscriptions par IP en 10 minutes)
$ip = $_SERVER['REMOTE_ADDR'];
$rate_limit_file = '../logs/newsletter_rate_limit.json';
$rate_limit_data = [];

if (file_exists($rate_limit_file)) {
    $rate_limit_data = json_decode(file_get_contents($rate_limit_file), true) ?: [];
}

// Nettoyer les vieilles entrées (>10 minutes)
$current_time = time();
$rate_limit_data = array_filter($rate_limit_data, function($timestamp) use ($current_time) {
    return ($current_time - $timestamp) < 600; // 10 minutes
});

// Vérifier le nombre de soumissions de cette IP
$ip_submissions = array_filter($rate_limit_data, function($timestamp, $key) use ($ip) {
    return strpos($key, $ip) === 0;
}, ARRAY_FILTER_USE_BOTH);

if (count($ip_submissions) >= 3) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Trop de tentatives. Réessayez dans 10 minutes.']);
    exit;
}

// Récupérer et valider les données
$email = filter_var($_POST['EMAIL'] ?? '', FILTER_VALIDATE_EMAIL);
$prenom = trim($_POST['PRENOM'] ?? '');

// PROTECTION 3: Validation stricte
if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email invalide']);
    exit;
}

// Vérifier les domaines jetables/temporaires
$disposable_domains = ['tempmail.com', 'guerrillamail.com', 'mailinator.com', '10minutemail.com', 'yopmail.com'];
$email_domain = substr(strrchr($email, "@"), 1);
if (in_array(strtolower($email_domain), $disposable_domains)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Les emails temporaires ne sont pas acceptés']);
    exit;
}

if (empty($prenom) || strlen($prenom) > 50) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Prénom invalide']);
    exit;
}

// Ajouter à Brevo via API avec DOUBLE OPT-IN forcé
function addContactToBrevo($email, $prenom) {
    if (BREVO_API_KEY === 'YOUR_BREVO_API_KEY_HERE' || empty(BREVO_API_KEY)) {
        return ['success' => false, 'error' => 'Brevo non configuré'];
    }
    
    // Utiliser l'endpoint DOI (Double Opt-In)
    $url = 'https://api.brevo.com/v3/contacts/doubleOptinConfirmation';
    
    $data = [
        'email' => $email,
        'attributes' => [
            'PRENOM' => $prenom
        ],
        'includeListIds' => [2], // ID de votre liste (vérifier dans Brevo)
        'templateId' => 1, // Template #1: "Template de confirmation double opt-in par défaut"
        'redirectionUrl' => 'https://osons-saint-paul.fr/merci-inscription'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'content-type: application/json',
        'api-key: ' . BREVO_API_KEY
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // 201 = email DOI envoyé avec succès, 204 = contact déjà confirmé
    if ($httpCode === 201 || $httpCode === 204 || $httpCode === 200) {
        return ['success' => true, 'service' => 'brevo-doi'];
    } else {
        $errorMsg = $response ? $response : $error;
        return ['success' => false, 'error' => 'Erreur Brevo (HTTP ' . $httpCode . '): ' . $errorMsg, 'service' => 'brevo-doi'];
    }
}

// Envoyer à Brevo
$result = addContactToBrevo($email, $prenom);

// Logger
$log_file = '../logs/newsletter_logs.log';
$log_dir = dirname($log_file);
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

$timestamp = date('Y-m-d H:i:s');
$log_entry = "[$timestamp] Email: $email | Prénom: $prenom | Success: " . ($result['success'] ? 'YES' : 'NO') . " | IP: $ip | Error: " . ($result['error'] ?? 'none') . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

// Mettre à jour rate limiting
if ($result['success']) {
    $rate_limit_data[$ip . '_' . time()] = $current_time;
    file_put_contents($rate_limit_file, json_encode($rate_limit_data), LOCK_EX);
}

// Réponse
if ($result['success']) {
    echo json_encode([
        'success' => true,
        'message' => 'Merci ! Un email de confirmation vient de vous être envoyé.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de l\'inscription. Réessayez plus tard.'
    ]);
}
?>

