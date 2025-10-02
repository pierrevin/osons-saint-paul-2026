<?php
// Page de test pour la 2FA
session_start();
require_once __DIR__ . '/includes/user_manager.php';

$user_manager = new UserManager();

// Secret de test (connu)
$test_secret = 'JBSWY3DPEHPK3PXP';

// Générer un code de test
$current_time = floor(time() / 30);
$test_code = $user_manager->generateTOTPCode($test_secret, $current_time);

// Vérifier un code saisi
$verification_result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_code'])) {
    $input_code = $_POST['test_code'];
    $expected_code = $user_manager->generateTOTPCode($test_secret, $current_time);
    
    if ($input_code === $expected_code) {
        $verification_result = '✅ Code correct !';
    } else {
        $verification_result = '❌ Code incorrect. Attendu: ' . $expected_code;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test 2FA - Google Authenticator</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="admin-container">
        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-shield-alt"></i> Test 2FA - Google Authenticator</h1>
                <p>Test de la double authentification avec Google Authenticator</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Configuration de test</h3>
                </div>
                <div class="card-body">
                    <div class="google-auth-setup">
                        <h4>Google Authenticator - Test</h4>
                        <p><strong>Scannez ce QR code avec Google Authenticator :</strong></p>
                        
                        <div class="qr-code">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=otpauth://totp/Test%20Osons%20Saint-Paul?secret=<?= $test_secret ?>&issuer=Test" alt="QR Code">
                        </div>
                        
                        <p><strong>Code secret :</strong> <?= $test_secret ?></p>
                        <p><strong>Code actuel attendu :</strong> <?= $test_code ?></p>
                        <p>Ou saisissez manuellement le code secret dans Google Authenticator.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Test de vérification</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label for="test_code">Code Google Authenticator</label>
                            <input type="text" id="test_code" name="test_code" placeholder="123456" maxlength="6" required>
                        </div>
                        
                        <button type="submit" class="btn-primary">Tester le code</button>
                    </form>
                    
                    <?php if ($verification_result): ?>
                        <div class="alert alert-<?= strpos($verification_result, '✅') !== false ? 'success' : 'error' ?>">
                            <?= htmlspecialchars($verification_result) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Informations de debug</h3>
                </div>
                <div class="card-body">
                    <p><strong>Temps actuel :</strong> <?= date('Y-m-d H:i:s') ?></p>
                    <p><strong>Timestamp TOTP :</strong> <?= $current_time ?></p>
                    <p><strong>Code généré :</strong> <?= $test_code ?></p>
                    <p><strong>Secret utilisé :</strong> <?= $test_secret ?></p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
