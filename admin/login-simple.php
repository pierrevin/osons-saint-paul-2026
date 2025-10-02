<?php
// Login ultra-simple
session_start();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Authentification simple
    if ($username === 'admin' && $password === 'admin2026') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = 'admin';
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_id'] = 1;
        $_SESSION['login_time'] = time();
        
        header('Location: pages/schema_admin.php');
        exit;
    } elseif ($username === 'editeur' && $password === 'editeur2026') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = 'editeur';
        $_SESSION['user_role'] = 'editeur';
        $_SESSION['user_id'] = 2;
        $_SESSION['login_time'] = time();
        
        header('Location: pages/schema_admin.php');
        exit;
    } else {
        $error_message = 'Identifiants incorrects';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administration</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <img src="../uploads/Osons1.png" alt="Logo Osons Saint-Paul">
            <h1>Administration</h1>
        </div>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error_message) ?>
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
</body>
</html>
