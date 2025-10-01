<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chargement de la configuration
require_once 'config.php';
require_once 'email-service.php';

// Fonction pour valider le token CSRF
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Les fonctions sanitizeInput, validateEmail et generateUniqueId sont définies dans config.php

// Fonction pour sauvegarder les données
function saveProposition($data) {
    // Charger les données existantes
    $propositions = [];
    if (file_exists(PROPOSITIONS_DATA_FILE)) {
        $content = file_get_contents(PROPOSITIONS_DATA_FILE);
        $propositions = json_decode($content, true) ?: [];
    }
    
    // Ajouter la nouvelle proposition
    $propositions['propositions'][] = $data;
    
    // Créer une sauvegarde avant de sauvegarder
    createBackup();
    
    // Sauvegarder
    return file_put_contents(PROPOSITIONS_DATA_FILE, json_encode($propositions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Fonction pour envoyer un email de confirmation
function sendConfirmationEmail($email, $titre, $propositionId) {
    
    $subject = "Confirmation - Proposition citoyenne reçue";
    $message = "
    <html>
    <head>
        <title>Confirmation de proposition</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #EC654F; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
            .highlight { background: #fff; padding: 15px; border-left: 4px solid #EC654F; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>💡 Proposition reçue !</h1>
            </div>
            <div class='content'>
                <p>Bonjour,</p>
                <p>Nous avons bien reçu votre proposition citoyenne :</p>
                <div class='highlight'>
                    <strong>Titre :</strong> " . htmlspecialchars($titre) . "<br>
                    <strong>ID de suivi :</strong> " . htmlspecialchars($propositionId) . "
                </div>
                <p>Votre proposition va être étudiée par notre équipe. Nous vous tiendrons informé de son avancement.</p>
                <p>Merci pour votre engagement citoyen !</p>
                <p>L'équipe Osons Saint-Paul 2026</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail($email, $subject, $message);
}

// Fonction pour notifier l'admin
function notifyAdmin($data, $propositionId) {
    
    $subject = "Nouvelle proposition citoyenne - " . $data['titre'];
    $message = "
    <html>
    <head>
        <title>Nouvelle proposition</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2F6E4F; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
            .field { margin: 10px 0; }
            .label { font-weight: bold; color: #2F6E4F; }
            .value { margin-left: 10px; }
            .admin-link { background: #EC654F; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Nouvelle proposition citoyenne</h1>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Titre :</span>
                    <span class='value'>" . htmlspecialchars($data['titre']) . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Description :</span>
                    <span class='value'>" . nl2br(htmlspecialchars($data['description'])) . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Catégories :</span>
                    <span class='value'>" . implode(', ', $data['categories']) . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Email :</span>
                    <span class='value'>" . htmlspecialchars($data['email']) . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Date :</span>
                    <span class='value'>" . date('d/m/Y à H:i') . "</span>
                </div>
                <div class='field'>
                    <span class='label'>ID :</span>
                    <span class='value'>" . htmlspecialchars($propositionId) . "</span>
                </div>
                <p><a href='../../admin/pages/schema_admin.php' class='admin-link'>Voir dans l'interface admin</a></p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return sendEmail(ADMIN_EMAIL, $subject, $message);
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validation CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Erreur de sécurité. Veuillez réessayer.');
    }
    
    // Validation des champs obligatoires
    $required_fields = ['email', 'titre', 'description', 'beneficiaires', 'acceptation_publication', 'acceptation_rgpd'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            die('Tous les champs obligatoires doivent être remplis.');
        }
    }
    
    // Validation spéciale pour les catégories (tableau)
    if (!isset($_POST['categories']) || !is_array($_POST['categories']) || empty($_POST['categories'])) {
        die('Veuillez sélectionner au moins une catégorie.');
    }
    
    // Validation de l'email
    if (!validateEmail($_POST['email'])) {
        die('Adresse email invalide.');
    }
    
    // Vérifier la limite de propositions par email
    if (!checkEmailLimit($_POST['email'])) {
        die('Vous avez atteint la limite de propositions pour aujourd\'hui. Réessayez demain.');
    }
    
    // Validation des catégories déjà effectuée plus haut
    
    // Nettoyage et préparation des données
    $propositionData = [
        'id' => generateUniqueId(),
        'date' => date('Y-m-d H:i:s'),
        'status' => 'pending',
        'data' => [
            'nom' => sanitizeInput($_POST['nom'] ?? ''),
            'email' => sanitizeInput($_POST['email']),
            'commune' => sanitizeInput($_POST['commune'] ?? ''),
            'telephone' => sanitizeInput($_POST['telephone'] ?? ''),
            'titre' => sanitizeInput($_POST['titre']),
            'description' => sanitizeInput($_POST['description']),
            'categories' => array_map('sanitizeInput', $_POST['categories']),
            'beneficiaires' => sanitizeInput($_POST['beneficiaires']),
            'cout' => sanitizeInput($_POST['cout'] ?? ''),
            'engagement' => sanitizeInput($_POST['engagement'] ?? ''),
            'engagement_details' => sanitizeInput($_POST['engagement_details'] ?? ''),
            'acceptation_publication' => true,
            'acceptation_rgpd' => true
        ]
    ];
    
    // Sauvegarde
    if (saveProposition($propositionData)) {
        
        // Envoi des emails
        $emailResult = EmailService::sendConfirmationEmail($propositionData['data']['email'], $propositionData);
        logEmailAttempt($propositionData['data']['email'], 'Confirmation proposition', $emailResult);
        
        $adminResult = EmailService::sendNewProposalNotification(ADMIN_EMAIL, $propositionData);
        logEmailAttempt(ADMIN_EMAIL, 'Nouvelle proposition', $adminResult);
        
        // Redirection vers page de confirmation
        header('Location: confirmation.php?id=' . urlencode($propositionData['id']));
        exit;
        
    } else {
        die('Erreur lors de la sauvegarde. Veuillez réessayer.');
    }
    
} else {
    // Redirection si accès direct
    header('Location: proposition-citoyenne.php');
    exit;
}
?>
