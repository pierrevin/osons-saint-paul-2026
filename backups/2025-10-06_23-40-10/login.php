<?php
// Page de connexion admin
session_start();

require_once __DIR__ . '/includes/user_manager.php';

// Si déjà connecté, rediriger selon le rôle
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: pages/schema_admin_new.php');
    } else {
        header('Location: pages/editeur.php');
    }
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

    // Utiliser le gestionnaire d'utilisateurs sécurisé
    $user_manager = new UserManager();
    $result = $user_manager->authenticate($username, $password);

    if ($result['success']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $result['user']['username'];
        $_SESSION['user_role'] = $result['user']['role'];
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['login_time'] = time();

        // Rediriger selon le rôle
        if ($result['user']['role'] === 'admin') {
            header('Location: pages/schema_admin_new.php');
        } else {
            header('Location: pages/editeur.php');
        }
        exit;
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
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-login">Se connecter</button>
            </form>
            
            <div class="back-link">
                <a href="../index.php">← Retour au site</a>
            </div>
        </div>
    </div>
</body>
</html>
