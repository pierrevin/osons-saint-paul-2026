<?php
// Page de synchronisation Git
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/user_manager.php';

check_auth();
$user_manager = new UserManager();

// Vérifier les permissions (admin seulement)
if (!$user_manager->canAccess('gestion_utilisateurs')) {
    header('Location: schema_admin.php?error=permission_denied');
    exit;
}

$message = '';
$message_type = '';

// Traitement de la synchronisation Git
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sync_git'])) {
    try {
        // Fichiers de contenu à synchroniser
        $content_files = [
            'data/site_content.json',
            'data/propositions.json', 
            'admin/users.json',
            'admin/logs/security.log'
        ];
        
        $modified_files = [];
        
        // Vérifier quels fichiers ont été modifiés
        foreach ($content_files as $file) {
            $full_path = __DIR__ . '/../../' . $file;
            if (file_exists($full_path)) {
                $modified_files[] = $file;
            }
        }
        
        if (empty($modified_files)) {
            $message = "Aucun fichier de contenu à synchroniser.";
            $message_type = 'warning';
        } else {
            // Commiter les modifications
            $commit_message = "Sync: Modifications de contenu depuis production - " . date('Y-m-d H:i:s');
            
            // Exécuter les commandes Git
            $git_commands = [
                "cd " . escapeshellarg(__DIR__ . '/../../') . " && git add " . implode(' ', array_map('escapeshellarg', $modified_files)),
                "cd " . escapeshellarg(__DIR__ . '/../../') . " && git commit -m " . escapeshellarg($commit_message),
                "cd " . escapeshellarg(__DIR__ . '/../../') . " && git push origin main"
            ];
            
            $output = [];
            $success = true;
            
            foreach ($git_commands as $command) {
                exec($command . ' 2>&1', $output, $return_code);
                if ($return_code !== 0) {
                    $success = false;
                    break;
                }
            }
            
            if ($success) {
                $message = "✅ Synchronisation Git réussie ! Fichiers synchronisés : " . implode(', ', $modified_files);
                $message_type = 'success';
            } else {
                $message = "❌ Erreur lors de la synchronisation Git : " . implode(' ', $output);
                $message_type = 'error';
            }
        }
        
    } catch (Exception $e) {
        $message = "❌ Erreur : " . $e->getMessage();
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Synchronisation Git - Administration</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-body">
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../uploads/Osons1.png" alt="Logo" class="sidebar-logo">
                <h2>Administration</h2>
                <a href="../../index.php" class="view-site-btn" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Voir le site
                </a>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul class="sidebar-menu">
                    <li class="menu-item"><a href="schema_admin.php"><i class="fas fa-home"></i> Accueil</a></li>
                    <li class="menu-item"><a href="schema_admin.php#programme"><i class="fas fa-list-alt"></i> Gestion Programme</a></li>
                    <li class="menu-item"><a href="schema_admin.php#rendez_vous"><i class="fas fa-calendar"></i> Gestion RDV</a></li>
                    <li class="menu-item"><a href="reponse-questionnaire.php" target="_blank"><i class="fas fa-chart-bar"></i> Réponse Questionnaire</a></li>
                    <li class="menu-item"><a href="gestion-utilisateurs.php"><i class="fas fa-users-cog"></i> Gestion Utilisateurs</a></li>
                    <li class="menu-item"><a href="logs.php"><i class="fas fa-shield-alt"></i> Logs de Sécurité</a></li>
                    <li class="menu-item active"><a href="sync-git.php"><i class="fas fa-sync-alt"></i> Sync Git</a></li>
                </ul>
            </nav>
        </aside>

        <main class="main-content">
            <div class="content-header">
                <h1><i class="fas fa-sync-alt"></i> Synchronisation Git</h1>
                <p>Synchronisez les modifications de contenu avec le repository Git.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-git-alt"></i> Synchronisation des modifications</h3>
                </div>
                <div class="card-body">
                    <p>Cette fonction synchronise automatiquement les modifications de contenu (propositions, textes, utilisateurs) avec le repository Git.</p>
                    
                    <div class="form-actions">
                        <form method="POST" style="display: inline;">
                            <button type="submit" name="sync_git" class="btn btn-primary" onclick="return confirm('Êtes-vous sûr de vouloir synchroniser avec Git ?')">
                                <i class="fas fa-sync-alt"></i> Synchroniser avec Git
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> Informations</h3>
                </div>
                <div class="card-body">
                    <p><strong>Fichiers synchronisés :</strong></p>
                    <ul>
                        <li><code>data/site_content.json</code> - Contenu du site</li>
                        <li><code>data/propositions.json</code> - Propositions citoyennes</li>
                        <li><code>admin/users.json</code> - Comptes utilisateurs</li>
                        <li><code>admin/logs/security.log</code> - Logs de sécurité</li>
                    </ul>
                    
                    <p><strong>Après synchronisation :</strong></p>
                    <ul>
                        <li>Les modifications sont commitées dans Git</li>
                        <li>Le repository GitHub est mis à jour</li>
                        <li>Vos autres machines peuvent récupérer les modifications avec <code>git pull</code></li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
