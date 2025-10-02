<?php
// Page de connexion admin
session_start();

require_once __DIR__ . '/includes/user_manager.php';

// Si déjà connecté, rediriger vers l'admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                header('Location: pages/schema_admin.php');
    exit;
}

$error_message = '';
$timeout_message = '';

// Vérifier les messages d'erreur
if (isset($_GET['timeout'])) {
    $timeout_message = 'Session expirée. Veuillez vous reconnecter.';
}

        // Traitement du formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $twofa_code = $_POST['twofa_code'] ?? '';

            // Utiliser le gestionnaire d'utilisateurs sécurisé
            $user_manager = new UserManager();
            $result = $user_manager->authenticate($username, $password);

            if ($result['success']) {
                $user = $result['user'];
                
                // Vérifier si 2FA est requis
                if (!isset($_SESSION['2fa_required']) || !$_SESSION['2fa_required']) {
                    // Première étape : vérifier les identifiants
                    $_SESSION['2fa_required'] = true;
                    $_SESSION['temp_user'] = $user;
                    
                    // Générer et envoyer le code 2FA selon le rôle
                    if ($user['role'] === 'admin') {
                        // Pour l'admin : Google Authenticator
                        if (!isset($user['google_auth_secret'])) {
                            $secret = $user_manager->generateGoogleAuthSecret($user['id']);
                            $_SESSION['google_auth_secret'] = $secret;
                            $_SESSION['setup_google_auth'] = true;
                        }
                    } else {
                        // Pour l'éditeur : Email
                        $code = $user_manager->generate2FACode($user['id']);
                        // Ici, vous devriez envoyer l'email avec le code
                        // Pour l'instant, on l'affiche (à remplacer par un vrai envoi d'email)
                        $_SESSION['2fa_code_sent'] = $code;
                    }
                    
                    $error_message = ''; // Pas d'erreur, on passe à l'étape 2FA
                } else {
                    // Deuxième étape : vérifier le code 2FA
                    if (empty($twofa_code)) {
                        $error_message = 'Veuillez saisir le code de vérification.';
                    } else {
                        $user = $_SESSION['temp_user'];
                        $twofa_result = false;
                        
                        if ($user['role'] === 'admin') {
                            $twofa_result = $user_manager->verifyGoogleAuthCode($user['id'], $twofa_code);
                        } else {
                            $twofa_result = $user_manager->verify2FACode($user['id'], $twofa_code);
                        }
                        
                        if ($twofa_result['success']) {
                            // 2FA réussi, finaliser la connexion
                            $_SESSION['admin_logged_in'] = true;
                            $_SESSION['admin_user'] = $user['username'];
                            $_SESSION['user_role'] = $user['role'];
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['login_time'] = time();
                            
                            // Nettoyer les variables temporaires
                            unset($_SESSION['2fa_required']);
                            unset($_SESSION['temp_user']);
                            unset($_SESSION['google_auth_secret']);
                            unset($_SESSION['setup_google_auth']);
                            unset($_SESSION['2fa_code_sent']);
                            
                            // Rediriger vers l'admin
                            header('Location: pages/schema_admin.php');
                            exit;
                        } else {
                            $error_message = $twofa_result['message'];
                        }
                    }
                }
            } else {
                $error_message = $result['message'];
            }
        }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Osons Saint-Paul</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #004a6d 0%, #0e7fad 100%);
            padding: 20px;
        }
        
        .login-box {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header img {
            width: 60px;
            height: 60px;
            margin-bottom: 1rem;
        }
        
        .login-header h1 {
            color: #004a6d;
            margin: 0;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #004a6d;
        }
        
        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: #004a6d;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-login:hover {
            background: #003a5a;
        }
        
        .alert {
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .back-link a {
            color: #004a6d;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <img src="../uploads/Osons1.png" alt="Logo Osons Saint-Paul">
                <h1>Administration</h1>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($timeout_message): ?>
                <div class="alert alert-warning">
                    <?= htmlspecialchars($timeout_message) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <?php if (!isset($_SESSION['2fa_required']) || !$_SESSION['2fa_required']): ?>
                    <!-- Étape 1 : Identifiants -->
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn-login">Se connecter</button>
                <?php else: ?>
                    <!-- Étape 2 : Code 2FA -->
                    <?php 
                    $user = $_SESSION['temp_user'] ?? null;
                    $is_admin = $user && $user['role'] === 'admin';
                    ?>
                    
                    <div class="twofa-step">
                        <h3>Vérification en deux étapes</h3>
                        <p>Bonjour <strong><?= htmlspecialchars($user['username'] ?? '') ?></strong></p>
                        
                        <?php if ($is_admin): ?>
                            <?php if (isset($_SESSION['setup_google_auth'])): ?>
                                <!-- Configuration Google Authenticator -->
                                <div class="google-auth-setup">
                                    <h4>Configuration Google Authenticator</h4>
                                    <p>Scannez ce QR code avec Google Authenticator :</p>
                                    <div class="qr-code">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/Osons%20Saint-Paul%20Admin?secret=<?= $_SESSION['google_auth_secret'] ?>&issuer=Osons%20Saint-Paul" alt="QR Code">
                                    </div>
                                    <p><strong>Code secret :</strong> <?= $_SESSION['google_auth_secret'] ?></p>
                                    <p>Ou saisissez manuellement le code secret dans Google Authenticator.</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="twofa_code">Code Google Authenticator</label>
                                <input type="text" id="twofa_code" name="twofa_code" placeholder="123456" maxlength="6" required autofocus>
                            </div>
                        <?php else: ?>
                            <!-- Code Email pour éditeur -->
                            <div class="email-code">
                                <p>Un code de vérification a été envoyé à votre adresse email.</p>
                                <?php if (isset($_SESSION['2fa_code_sent'])): ?>
                                    <p class="debug-code">Code de test : <strong><?= $_SESSION['2fa_code_sent'] ?></strong></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="twofa_code">Code de vérification</label>
                                <input type="text" id="twofa_code" name="twofa_code" placeholder="123456" maxlength="6" required autofocus>
                            </div>
                        <?php endif; ?>
                        
                        <button type="submit" class="btn-login">Vérifier</button>
                        <a href="login.php" class="btn-secondary">Retour</a>
                    </div>
                <?php endif; ?>
            </form>
            
            <div class="back-link">
                <a href="../index.php">← Retour au site</a>
            </div>
        </div>
    </div>
</body>
</html>
