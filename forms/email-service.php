<?php
// Service d'emails avec Brevo (ex-Sendinblue)
require_once 'email-config.php';

define('BREVO_API_URL', 'https://api.brevo.com/v3/smtp/email');

// Templates d'emails
class EmailTemplates {
    
    public static function getConfirmationTemplate($data) {
        return [
            'subject' => 'Confirmation - Proposition citoyenne reçue',
            'html' => "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Confirmation de proposition</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
                    .header { background: #EC654F; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                    .highlight { background: #fff; padding: 15px; border-left: 4px solid #EC654F; margin: 20px 0; }
                    .footer { text-align: center; margin-top: 20px; font-size: 0.9rem; color: #666; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>💡 Proposition reçue !</h1>
                </div>
                <div class='content'>
                    <p>Bonjour,</p>
                    <p>Nous avons bien reçu votre proposition citoyenne :</p>
                    <div class='highlight'>
                        <strong>Titre :</strong> " . htmlspecialchars($data['titre'] ?? 'N/A') . "<br>
                        <strong>ID de suivi :</strong> " . htmlspecialchars($data['id']) . "<br>
                        <strong>Date :</strong> " . date('d/m/Y à H:i') . "
                    </div>
                    <p>Votre proposition va être étudiée par notre équipe. Nous vous tiendrons informé de son avancement.</p>
                    <p>Merci pour votre engagement citoyen !</p>
                    <div class='footer'>
                        <p>L'équipe Osons Saint-Paul 2026</p>
                        <p>Pour toute question : bonjour@osons-saint-paul.fr</p>
                    </div>
                </div>
            </body>
            </html>
            "
        ];
    }
    
    public static function getStatusUpdateTemplate($data, $status) {
        $statusTexts = [
            'approved' => 'approuvée',
            'rejected' => 'rejetée',
            'integrated' => 'intégrée au programme'
        ];
        
        $statusEmojis = [
            'approved' => '✅',
            'rejected' => '❌',
            'integrated' => '🚀'
        ];
        
        $statusText = $statusTexts[$status] ?? $status;
        $statusEmoji = $statusEmojis[$status] ?? '📋';
        
        return [
            'subject' => "Mise à jour - Votre proposition a été {$statusText}",
            'html' => "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Mise à jour de proposition</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
                    .header { background: #2F6E4F; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                    .highlight { background: #fff; padding: 15px; border-left: 4px solid #2F6E4F; margin: 20px 0; }
                    .footer { text-align: center; margin-top: 20px; font-size: 0.9rem; color: #666; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>{$statusEmoji} Proposition {$statusText}</h1>
                </div>
                <div class='content'>
                    <p>Bonjour,</p>
                    <p>Nous vous informons que votre proposition a été {$statusText} :</p>
                    <div class='highlight'>
                        <strong>Titre :</strong> " . htmlspecialchars($data['titre'] ?? 'N/A') . "<br>
                        <strong>ID :</strong> " . htmlspecialchars($data['id']) . "<br>
                        <strong>Statut :</strong> {$statusText}
                    </div>
                    " . ($status === 'integrated' ? "<p>🎉 Félicitations ! Votre proposition fait maintenant partie de notre programme officiel.</p>" : "") . "
                    " . ($status === 'rejected' && isset($data['rejection_reason']) ? "<div class='highlight' style='background: #fff3cd; border-left-color: #ffc107;'><strong>Raison du rejet :</strong><br>" . nl2br(htmlspecialchars($data['rejection_reason'])) . "</div>" : "") . "
                    <p>Merci pour votre engagement citoyen !</p>
                    <div class='footer'>
                        <p>L'équipe Osons Saint-Paul 2026</p>
                        <p>Pour toute question : bonjour@osons-saint-paul.fr</p>
                    </div>
                </div>
            </body>
            </html>
            "
        ];
    }
    
    public static function getNewProposalNotificationTemplate($data) {
        return [
            'subject' => "Nouvelle proposition citoyenne - " . $data['titre'],
            'html' => "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Nouvelle proposition</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
                    .header { background: #2F6E4F; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                    .field { margin: 10px 0; }
                    .label { font-weight: bold; color: #2F6E4F; }
                    .value { margin-left: 10px; }
                    .admin-link { background: #EC654F; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>💡 Nouvelle proposition citoyenne</h1>
                </div>
                <div class='content'>
                    <div class='field'>
                        <span class='label'>Titre :</span>
                        <span class='value'>" . htmlspecialchars($data['titre'] ?? 'N/A') . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Description :</span>
                        <span class='value'>" . nl2br(htmlspecialchars($data['description'] ?? 'N/A')) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Catégories :</span>
                        <span class='value'>" . implode(', ', $data['categories'] ?? []) . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>Email :</span>
                        <span class='value'>" . htmlspecialchars($data['email'] ?? 'N/A') . "</span>
                    </div>
                    " . ($data['telephone'] ? "<div class='field'><span class='label'>Téléphone :</span><span class='value'>" . htmlspecialchars($data['telephone']) . "</span></div>" : "") . "
                    <div class='field'>
                        <span class='label'>Date :</span>
                        <span class='value'>" . date('d/m/Y à H:i') . "</span>
                    </div>
                    <div class='field'>
                        <span class='label'>ID :</span>
                        <span class='value'>" . htmlspecialchars($data['id'] ?? 'N/A') . "</span>
                    </div>
                    <p><a href='" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/admin/pages/schema_admin_new.php' class='admin-link'>Voir dans l'interface admin</a></p>
                </div>
            </body>
            </html>
            "
        ];
    }
    
    public static function getContactConfirmationTemplate($nom) {
        return [
            'subject' => 'Confirmation - Message bien reçu',
            'html' => "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Confirmation de message</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
                    .header { background: #2F6E4F; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                    .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                    .highlight { background: #fff; padding: 15px; border-left: 4px solid #2F6E4F; margin: 20px 0; }
                    .footer { text-align: center; margin-top: 20px; font-size: 0.9rem; color: #666; }
                </style>
            </head>
            <body>
                <div class='header'>
                    <h1>✅ Message bien reçu !</h1>
                </div>
                <div class='content'>
                    <p>Bonjour " . htmlspecialchars($nom) . ",</p>
                    <p>Nous avons bien reçu votre message et vous en remercions.</p>
                    <div class='highlight'>
                        <p><strong>Notre équipe vous répondra dans les plus brefs délais.</strong></p>
                        <p>En attendant, n'hésitez pas à consulter notre programme et nos propositions sur le site.</p>
                    </div>
                    <p>Merci pour votre intérêt et votre engagement !</p>
                    <div class='footer'>
                        <p>L'équipe Osons Saint-Paul 2026</p>
                        <p>Pour toute question : bonjour@osons-saint-paul.fr</p>
                    </div>
                </div>
            </body>
            </html>
            "
        ];
    }
}

// Service d'envoi d'emails
class EmailService {
    
    public static function sendEmail($to, $subject, $htmlContent, $fromEmail = null, $fromName = null) {
        $fromEmail = $fromEmail ?: FROM_EMAIL;
        $fromName = $fromName ?: FROM_NAME;
        
        // Essayer d'abord Brevo
        if (BREVO_API_KEY !== 'YOUR_BREVO_API_KEY_HERE' && !empty(BREVO_API_KEY)) {
            $result = self::sendViaBrevo($to, $subject, $htmlContent, $fromEmail, $fromName);
            if ($result['success']) {
                return $result;
            }
        }
        
        // Fallback vers email PHP natif
        if (FALLBACK_EMAIL_ENABLED) {
            return self::sendViaPHP($to, $subject, $htmlContent, $fromEmail, $fromName);
        }
        
        return ['success' => false, 'error' => 'Aucun service email configuré'];
    }
    
    private static function sendViaBrevo($to, $subject, $htmlContent, $fromEmail, $fromName) {
        $data = [
            'sender' => [
                'name' => $fromName,
                'email' => $fromEmail
            ],
            'to' => [
                [
                    'email' => $to,
                    'name' => $to
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, BREVO_API_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'api-key: ' . BREVO_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 201) {
            return ['success' => true, 'service' => 'brevo'];
        } else {
            return ['success' => false, 'error' => 'Brevo error: ' . $response, 'service' => 'brevo'];
        }
    }
    
    private static function sendViaPHP($to, $subject, $htmlContent, $fromEmail, $fromName) {
        $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail
        ];
        
        // Expéditeur d'enveloppe (-f) recommandé par OVH
        $result = mail($to, $subject, $htmlContent, implode("\r\n", $headers), '-f ' . $fromEmail);
        
        return [
            'success' => $result,
            'service' => 'php',
            'error' => $result ? null : 'Erreur PHP mail()'
        ];
    }
    
    // Méthodes spécialisées
    public static function sendConfirmationEmail($email, $propositionData) {
        $template = EmailTemplates::getConfirmationTemplate($propositionData);
        return self::sendEmail($email, $template['subject'], $template['html']);
    }
    
    public static function sendStatusUpdateEmail($email, $propositionData, $status) {
        $template = EmailTemplates::getStatusUpdateTemplate($propositionData, $status);
        return self::sendEmail($email, $template['subject'], $template['html']);
    }
    
    public static function sendNewProposalNotification($adminEmail, $propositionData) {
        $template = EmailTemplates::getNewProposalNotificationTemplate($propositionData);
        return self::sendEmail($adminEmail, $template['subject'], $template['html']);
    }
    
    public static function sendContactConfirmationEmail($email, $nom) {
        $template = EmailTemplates::getContactConfirmationTemplate($nom);
        return self::sendEmail($email, $template['subject'], $template['html']);
    }
}

// Fonction de log pour les emails
function logEmailAttempt($to, $subject, $result) {
    $log_file = '../logs/email_logs.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] To: $to | Subject: $subject | Success: " . ($result['success'] ? 'YES' : 'NO') . " | Service: " . ($result['service'] ?? 'unknown') . " | Error: " . ($result['error'] ?? 'none') . "\n";
    
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
?>
