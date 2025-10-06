<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/user_manager.php';

// Vérifier l'authentification
check_auth();

// Vérifier les permissions (admin seulement)
$user_manager = new UserManager();
if (!$user_manager->canAccess('logs')) {
    header('Location: schema_admin_new.php?error=permission_denied');
    exit;
}

// Lire les logs de sécurité
$log_file = __DIR__ . '/../logs/security.log';
$logs = [];

if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $log_lines = array_filter(explode("\n", $log_content));
    
    foreach ($log_lines as $line) {
        $log_entry = json_decode($line, true);
        if ($log_entry) {
            $logs[] = $log_entry;
        }
    }
    
    // Trier par timestamp décroissant (plus récent en premier)
    usort($logs, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
}

// Filtrer les logs si nécessaire
$filtered_logs = $logs;
if (isset($_GET['event']) && !empty($_GET['event'])) {
    $event_filter = $_GET['event'];
    $filtered_logs = array_filter($logs, function($log) use ($event_filter) {
        return strpos($log['event'], $event_filter) !== false;
    });
}

if (isset($_GET['username']) && !empty($_GET['username'])) {
    $username_filter = $_GET['username'];
    $filtered_logs = array_filter($filtered_logs, function($log) use ($username_filter) {
        return strpos($log['username'], $username_filter) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de Sécurité - Administration</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="../../uploads/Osons1.png" alt="Logo" class="sidebar-logo" />
                <h2>Administration</h2>
                <a href="../../index.php" target="_blank" class="view-site-btn">
                    <i class="fas fa-external-link-alt"></i> Voir le site
                </a>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="schema_admin_new.php"><i class="fas fa-home"></i> Accueil</a></li>
                    <?php foreach ($user_manager->getMenuItems() as $item): ?>
                        <li>
                            <a href="<?= $item['id'] ?>.php" class="<?= basename($_SERVER['PHP_SELF'], '.php') === $item['id'] ? 'active' : '' ?>">
                                <i class="<?= $item['icon'] ?>"></i> <?= htmlspecialchars($item['title']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-shield-alt"></i> Logs de Sécurité</h1>
                <p>Surveillez les événements de sécurité et les tentatives de connexion</p>
            </div>

            <!-- Filtres -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-filter"></i> Filtres</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="form-inline">
                        <div class="form-group">
                            <label for="event_filter">Type d'événement :</label>
                            <select id="event_filter" name="event">
                                <option value="">Tous les événements</option>
                                <option value="login_success" <?= (isset($_GET['event']) && $_GET['event'] === 'login_success') ? 'selected' : '' ?>>Connexions réussies</option>
                                <option value="login_failed" <?= (isset($_GET['event']) && $_GET['event'] === 'login_failed') ? 'selected' : '' ?>>Connexions échouées</option>
                                <option value="login_blocked" <?= (isset($_GET['event']) && $_GET['event'] === 'login_blocked') ? 'selected' : '' ?>>Comptes verrouillés</option>
                                <option value="user_created" <?= (isset($_GET['event']) && $_GET['event'] === 'user_created') ? 'selected' : '' ?>>Création d'utilisateurs</option>
                                <option value="user_updated" <?= (isset($_GET['event']) && $_GET['event'] === 'user_updated') ? 'selected' : '' ?>>Modification d'utilisateurs</option>
                                <option value="user_deleted" <?= (isset($_GET['event']) && $_GET['event'] === 'user_deleted') ? 'selected' : '' ?>>Suppression d'utilisateurs</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="username_filter">Nom d'utilisateur :</label>
                            <input type="text" id="username_filter" name="username" value="<?= htmlspecialchars($_GET['username'] ?? '') ?>" placeholder="Rechercher par nom d'utilisateur">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filtrer
                            </button>
                            <a href="logs.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Effacer
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-sign-in-alt text-success"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_filter($logs, function($log) { return $log['event'] === 'login_success'; })) ?></h3>
                        <p>Connexions réussies</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-times-circle text-danger"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_filter($logs, function($log) { return $log['event'] === 'login_failed'; })) ?></h3>
                        <p>Tentatives échouées</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-lock text-warning"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_filter($logs, function($log) { return $log['event'] === 'login_blocked'; })) ?></h3>
                        <p>Comptes verrouillés</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-user-plus text-info"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= count(array_filter($logs, function($log) { return in_array($log['event'], ['user_created', 'user_updated', 'user_deleted']); })) ?></h3>
                        <p>Actions utilisateurs</p>
                    </div>
                </div>
            </div>

            <!-- Liste des logs -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Événements de sécurité (<?= count($filtered_logs) ?> résultats)</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($filtered_logs)): ?>
                        <p class="text-muted">Aucun événement de sécurité trouvé.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date/Heure</th>
                                        <th>Événement</th>
                                        <th>Utilisateur</th>
                                        <th>Adresse IP</th>
                                        <th>Détails</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filtered_logs as $log): ?>
                                        <tr>
                                            <td>
                                                <small><?= date('d/m/Y H:i:s', strtotime($log['timestamp'])) ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $event_icons = [
                                                    'login_success' => 'fas fa-check-circle text-success',
                                                    'login_failed' => 'fas fa-times-circle text-danger',
                                                    'login_blocked' => 'fas fa-lock text-warning',
                                                    'user_created' => 'fas fa-user-plus text-info',
                                                    'user_updated' => 'fas fa-user-edit text-primary',
                                                    'user_deleted' => 'fas fa-user-times text-danger'
                                                ];
                                                $icon = $event_icons[$log['event']] ?? 'fas fa-info-circle text-secondary';
                                                ?>
                                                <span class="badge badge-<?= strpos($log['event'], 'success') !== false ? 'success' : (strpos($log['event'], 'failed') !== false || strpos($log['event'], 'blocked') !== false ? 'danger' : 'info') ?>">
                                                    <i class="<?= $icon ?>"></i> <?= ucfirst(str_replace('_', ' ', $log['event'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($log['username']) ?></strong>
                                            </td>
                                            <td>
                                                <code><?= htmlspecialchars($log['ip_address']) ?></code>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($log['details']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
