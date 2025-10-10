<?php
/**
 * Script de diagnostic Google Analytics
 * Vérifie la configuration et teste la connexion à l'API
 */

// Vérifier l'authentification admin
session_start();

// Vérifier si l'utilisateur est connecté comme admin
$isAdmin = false;

// Méthode 1: Vérifier la session admin normale
if (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']) {
    $isAdmin = true;
}

// Méthode 2: Vérifier si on vient de l'interface admin (pour localhost)
if (!$isAdmin && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/admin/') !== false) {
    $isAdmin = true;
}

// Méthode 3: Vérifier si on a un paramètre d'accès direct (pour le debug)
if (!$isAdmin && isset($_GET['debug']) && $_GET['debug'] === 'local') {
    $isAdmin = true;
}

// Méthode 4: Vérifier si on est sur localhost (pour le développement)
if (!$isAdmin && in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1'])) {
    // Vérifier qu'il y a une session active (même si pas admin)
    if (isset($_SESSION) && !empty($_SESSION)) {
        $isAdmin = true;
    }
}

if (!$isAdmin) {
    http_response_code(403);
    echo '<!DOCTYPE html><html><head><title>Accès non autorisé</title></head><body>';
    echo '<h1>Accès non autorisé</h1>';
    echo '<p>Connectez-vous en tant qu\'administrateur ou ajoutez <code>?debug=local</code> à l\'URL pour le développement local.</p>';
    echo '<p><a href="schema_admin_new.php">Retour au dashboard admin</a></p>';
    echo '</body></html>';
    exit;
}

// Configuration
$credentialsPath = __DIR__ . '/../credentials/ga-service-account.json';
$propertyId = '508182192';
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

// Résultats du diagnostic
$results = [
    'credentials_file' => false,
    'credentials_valid' => false,
    'composer_autoload' => false,
    'google_classes' => false,
    'api_connection' => false,
    'property_access' => false,
    'test_data' => false,
    'errors' => [],
    'recommendations' => []
];

// 1. Vérifier l'existence du fichier credentials
if (file_exists($credentialsPath)) {
    $results['credentials_file'] = true;
    
    // 2. Valider la structure JSON
    $credentialsContent = file_get_contents($credentialsPath);
    $credentials = json_decode($credentialsContent, true);
    
    if (json_last_error() === JSON_ERROR_NONE && $credentials) {
        $results['credentials_valid'] = true;
        
        // Vérifier les champs requis
        $requiredFields = ['type', 'project_id', 'private_key_id', 'client_email', 'client_id'];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($credentials[$field]) || empty($credentials[$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (!empty($missingFields)) {
            $results['errors'][] = 'Champs manquants dans credentials: ' . implode(', ', $missingFields);
        }
    } else {
        $results['errors'][] = 'Fichier credentials JSON invalide: ' . json_last_error_msg();
    }
} else {
    $results['errors'][] = 'Fichier credentials non trouvé: ' . $credentialsPath;
    $results['recommendations'][] = 'Téléchargez le fichier JSON du compte de service depuis Google Cloud Console';
}

// 3. Vérifier Composer autoload
if (file_exists($autoloadPath)) {
    $results['composer_autoload'] = true;
    require_once $autoloadPath;
    
    // 4. Vérifier les classes Google
    if (class_exists('Google\Client') && class_exists('Google\Service\AnalyticsData')) {
        $results['google_classes'] = true;
        
        try {
            // 5. Tester la connexion API
            $client = new \Google\Client();
            $client->setAuthConfig($credentialsPath);
            $client->addScope(\Google\Service\AnalyticsData::ANALYTICS_READONLY);
            
            $analytics = new \Google\Service\AnalyticsData($client);
            $results['api_connection'] = true;
            
            // 6. Tester l'accès à la propriété
            $request = new \Google\Service\AnalyticsData\RunReportRequest([
                'dateRanges' => [
                    new \Google\Service\AnalyticsData\DateRange([
                        'startDate' => date('Y-m-d', strtotime('-7 days')),
                        'endDate' => date('Y-m-d')
                    ])
                ],
                'dimensions' => [new \Google\Service\AnalyticsData\Dimension(['name' => 'date'])],
                'metrics' => [new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])],
                'limit' => 1
            ]);
            
            $propertyName = "properties/{$propertyId}";
            $response = $analytics->properties->runReport($propertyName, $request);
            $results['property_access'] = true;
            
            // 7. Récupérer des données de test
            if ($response->getRows() && count($response->getRows()) > 0) {
                $results['test_data'] = true;
                $testUserCount = $response->getRows()[0]->getMetricValues()[0]->getValue();
                $results['test_user_count'] = $testUserCount;
            } else {
                $results['errors'][] = 'Aucune donnée retournée par l\'API (normal si le site n\'a pas de trafic récent)';
            }
            
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            $results['errors'][] = 'Erreur API: ' . $errorMessage;
            
            // Analyser l'erreur pour donner des recommandations
            if (strpos($errorMessage, '403') !== false || strpos($errorMessage, 'Forbidden') !== false) {
                $results['recommendations'][] = 'Vérifiez que le compte de service a le rôle "Lecteur" dans Google Analytics 4';
                $results['recommendations'][] = 'Ajoutez l\'email du compte de service dans Admin → Accès à la propriété → Utilisateurs et notifications';
            } elseif (strpos($errorMessage, '404') !== false || strpos($errorMessage, 'Not Found') !== false) {
                $results['recommendations'][] = 'Vérifiez le Property ID: ' . $propertyId;
                $results['recommendations'][] = 'Assurez-vous que la propriété GA4 existe et est active';
            } elseif (strpos($errorMessage, '401') !== false || strpos($errorMessage, 'Unauthorized') !== false) {
                $results['recommendations'][] = 'Vérifiez que le fichier credentials est correct et non expiré';
                $results['recommendations'][] = 'Regénérez le fichier JSON depuis Google Cloud Console si nécessaire';
            }
        }
    } else {
        $results['errors'][] = 'Classes Google non disponibles. Exécutez "composer install"';
        $results['recommendations'][] = 'Exécutez: composer install dans le répertoire du projet';
    }
} else {
    $results['errors'][] = 'Composer autoload non trouvé: ' . $autoloadPath;
    $results['recommendations'][] = 'Exécutez: composer install dans le répertoire du projet';
}

// Fonction helper pour afficher un statut
function displayStatus($status, $label) {
    $icon = $status ? '✅' : '❌';
    $class = $status ? 'success' : 'error';
    echo "<div class='status-item {$class}'>";
    echo "<span class='status-icon'>{$icon}</span>";
    echo "<span class='status-label'>{$label}</span>";
    echo "</div>";
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Google Analytics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 2rem;
            background: #f8fafc;
            color: #334155;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
        }
        .content {
            padding: 2rem;
        }
        .section {
            margin-bottom: 2rem;
        }
        .section h2 {
            margin: 0 0 1rem 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .status-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .status-item.success {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }
        .status-item.error {
            background: #fef2f2;
            border-color: #fecaca;
        }
        .status-icon {
            font-size: 1.25rem;
        }
        .status-label {
            font-weight: 500;
        }
        .errors {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .errors h3 {
            margin: 0 0 0.5rem 0;
            color: #dc2626;
            font-size: 1rem;
        }
        .errors ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        .errors li {
            margin-bottom: 0.25rem;
        }
        .recommendations {
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 1rem;
        }
        .recommendations h3 {
            margin: 0 0 0.5rem 0;
            color: #92400e;
            font-size: 1rem;
        }
        .recommendations ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        .recommendations li {
            margin-bottom: 0.25rem;
        }
        .summary {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }
        .summary h2 {
            margin: 0 0 1rem 0;
            color: #1e293b;
        }
        .summary-ok {
            color: #059669;
            font-size: 1.125rem;
            font-weight: 600;
        }
        .summary-error {
            color: #dc2626;
            font-size: 1.125rem;
            font-weight: 600;
        }
        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .info-box h4 {
            margin: 0 0 0.5rem 0;
            color: #1e40af;
        }
        .refresh-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }
        .refresh-btn:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Diagnostic Google Analytics</h1>
            <p>Vérification de la configuration et test de connexion</p>
        </div>
        
        <div class="content">
            <!-- Résumé -->
            <div class="section">
                <div class="summary">
                    <h2>Résumé du diagnostic</h2>
                    <?php
                    $allOk = $results['credentials_file'] && $results['credentials_valid'] && 
                            $results['composer_autoload'] && $results['google_classes'] && 
                            $results['api_connection'] && $results['property_access'];
                    
                    if ($allOk) {
                        echo '<div class="summary-ok">✅ Configuration Google Analytics OK</div>';
                        echo '<p>Les vraies données devraient s\'afficher sur le dashboard.</p>';
                    } else {
                        echo '<div class="summary-error">❌ Configuration à corriger</div>';
                        echo '<p>Voir les détails ci-dessous pour résoudre les problèmes.</p>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- Informations système -->
            <div class="section">
                <h2><i class="fas fa-info-circle"></i> Informations système</h2>
                <div class="info-box">
                    <h4>Configuration actuelle</h4>
                    <p><strong>Property ID:</strong> <?= htmlspecialchars($propertyId) ?></p>
                    <p><strong>Credentials:</strong> <?= htmlspecialchars($credentialsPath) ?></p>
                    <p><strong>Autoload:</strong> <?= htmlspecialchars($autoloadPath) ?></p>
                    <p><strong>Date du test:</strong> <?= date('d/m/Y H:i:s') ?></p>
                </div>
            </div>
            
            <!-- Tests de configuration -->
            <div class="section">
                <h2><i class="fas fa-check-circle"></i> Tests de configuration</h2>
                
                <?php displayStatus($results['credentials_file'], 'Fichier credentials trouvé'); ?>
                <?php displayStatus($results['credentials_valid'], 'Structure JSON valide'); ?>
                <?php displayStatus($results['composer_autoload'], 'Composer autoload disponible'); ?>
                <?php displayStatus($results['google_classes'], 'Classes Google Analytics disponibles'); ?>
                <?php displayStatus($results['api_connection'], 'Connexion API réussie'); ?>
                <?php displayStatus($results['property_access'], 'Accès à la propriété autorisé'); ?>
                <?php displayStatus($results['test_data'], 'Données de test récupérées'); ?>
                
                <?php if (isset($results['test_user_count'])): ?>
                <div class="status-item success">
                    <span class="status-icon">📊</span>
                    <span class="status-label">Utilisateurs actifs (7 derniers jours): <?= number_format($results['test_user_count']) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Erreurs -->
            <?php if (!empty($results['errors'])): ?>
            <div class="section">
                <h2><i class="fas fa-exclamation-triangle"></i> Erreurs détectées</h2>
                <div class="errors">
                    <h3>Problèmes à résoudre</h3>
                    <ul>
                        <?php foreach ($results['errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Recommandations -->
            <?php if (!empty($results['recommendations'])): ?>
            <div class="section">
                <h2><i class="fas fa-lightbulb"></i> Recommandations</h2>
                <div class="recommendations">
                    <h3>Actions à effectuer</h3>
                    <ul>
                        <?php foreach ($results['recommendations'] as $recommendation): ?>
                        <li><?= htmlspecialchars($recommendation) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Actions -->
            <div class="section">
                <h2><i class="fas fa-tools"></i> Actions</h2>
                <a href="?refresh=1" class="refresh-btn">
                    <i class="fas fa-sync-alt"></i>
                    Relancer le diagnostic
                </a>
                <a href="schema_admin_new.php?section=dashboard" class="refresh-btn" style="margin-left: 1rem;">
                    <i class="fas fa-arrow-left"></i>
                    Retour au dashboard
                </a>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-refresh si demandé
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('refresh') === '1') {
            // Scroll vers le haut
            window.scrollTo(0, 0);
        }
    </script>
</body>
</html>
