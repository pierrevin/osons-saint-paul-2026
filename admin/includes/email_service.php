<?php
/**
 * Service d'envoi d'emails pour la 2FA
 */
class EmailService {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name;
    
    public function __construct() {
        // Configuration SMTP (à adapter selon votre hébergeur)
        $this->smtp_host = 'ssl0.ovh.net'; // Serveur SMTP OVH
        $this->smtp_port = 587;
        $this->smtp_username = 'admin@osonssaintpaul.fr'; // Votre email OVH
        $this->smtp_password = 'VOTRE_MOT_DE_PASSE_EMAIL'; // À configurer
        $this->from_email = 'admin@osonssaintpaul.fr';
        $this->from_name = 'Osons Saint-Paul - Administration';
    }
    
    /**
     * Envoyer un code 2FA par email
     */
    public function send2FACode($to_email, $username, $code) {
        $subject = 'Code de vérification - Osons Saint-Paul';
        $message = $this->get2FAEmailTemplate($username, $code);
        
        return $this->sendEmail($to_email, $subject, $message);
    }
    
    /**
     * Template email pour la 2FA
     */
    private function get2FAEmailTemplate($username, $code) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Code de vérification</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #ec654f; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 30px; }
                .code { background: #fff; border: 2px solid #ec654f; padding: 20px; text-align: center; font-size: 24px; font-weight: bold; margin: 20px 0; }
                .footer { background: #333; color: white; padding: 15px; text-align: center; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Code de vérification</h1>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>$username</strong>,</p>
                    <p>Vous avez demandé à vous connecter à l'administration d'Osons Saint-Paul.</p>
                    <p>Voici votre code de vérification :</p>
                    <div class='code'>$code</div>
                    <p><strong>Ce code expire dans 5 minutes.</strong></p>
                    <p>Si vous n'avez pas demandé cette connexion, ignorez cet email.</p>
                </div>
                <div class='footer'>
                    <p>Osons Saint-Paul - Administration</p>
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Envoyer un email
     */
    private function sendEmail($to, $subject, $message) {
        // Headers pour email HTML
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->from_name . ' <' . $this->from_email . '>',
            'Reply-To: ' . $this->from_email,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        // Tentative d'envoi avec mail() PHP
        $success = mail($to, $subject, $message, implode("\r\n", $headers));
        
        if ($success) {
            return [
                'success' => true,
                'message' => 'Email envoyé avec succès'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email'
            ];
        }
    }
    
    /**
     * Test de configuration email
     */
    public function testEmailConfiguration() {
        $test_result = [
            'smtp_host' => $this->smtp_host,
            'smtp_port' => $this->smtp_port,
            'from_email' => $this->from_email,
            'from_name' => $this->from_name,
            'php_mail_function' => function_exists('mail') ? 'Disponible' : 'Non disponible'
        ];
        
        return $test_result;
    }
}
?>
