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
if (!$user_manager->canAccess('gestion_utilisateurs')) {
    header('Location: schema_admin.php?error=permission_denied');
    exit;
}

$message = '';
$error = '';

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'create_user':
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'editeur';
            $email = $_POST['email'] ?? '';
            
            if (empty($username) || empty($password) || empty($email)) {
                $error = 'Tous les champs sont obligatoires.';
            } else {
                $result = $user_manager->createUser($username, $password, $role, $email);
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
            }
            break;
            
        case 'update_user':
            $user_id = $_POST['user_id'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? '';
            $active = isset($_POST['active']) ? 1 : 0;
            $password = $_POST['password'] ?? '';
            
            $data = [
                'username' => $username,
                'email' => $email,
                'role' => $role,
                'active' => $active
            ];
            
            if (!empty($password)) {
                $data['password'] = $password;
            }
            
            $result = $user_manager->updateUser($user_id, $data);
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
            break;
            
        case 'delete_user':
            $user_id = $_POST['user_id'] ?? '';
            $result = $user_manager->deleteUser($user_id);
            if ($result['success']) {
                $message = $result['message'];
            } else {
                $error = $result['message'];
            }
            break;
    }
}

// Récupérer la liste des utilisateurs
$users_result = $user_manager->getAllUsers();
$users = $users_result['success'] ? $users_result['users'] : [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Administration</title>
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
                    <li><a href="schema_admin.php"><i class="fas fa-home"></i> Accueil</a></li>
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
                <h1><i class="fas fa-users"></i> Gestion des Utilisateurs</h1>
                <p>Gérez les comptes administrateurs et éditeurs</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire de création d'utilisateur -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user-plus"></i> Créer un nouvel utilisateur</h3>
                </div>
                <div class="card-body">
                    <form method="POST" class="form-grid">
                        <input type="hidden" name="action" value="create_user">
                        <input type="hidden" name="csrf_token" value="<?= bin2hex(random_bytes(32)) ?>">
                        
                        <div class="form-group">
                            <label for="new_username">Nom d'utilisateur *</label>
                            <input type="text" id="new_username" name="username" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_email">Email *</label>
                            <input type="email" id="new_email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Mot de passe *</label>
                            <input type="password" id="new_password" name="password" required minlength="8">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_role">Rôle *</label>
                            <select id="new_role" name="role" required>
                                <option value="editeur">Éditeur</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Créer l'utilisateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des utilisateurs -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Utilisateurs existants</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <p class="text-muted">Aucun utilisateur trouvé.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom d'utilisateur</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Statut</th>
                                        <th>Dernière connexion</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['id']) ?></td>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <span class="badge badge-<?= $user['role'] === 'admin' ? 'primary' : 'secondary' ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $user['active'] ? 'success' : 'danger' ?>">
                                                    <?= $user['active'] ? 'Actif' : 'Inactif' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['last_login']): ?>
                                                    <?= date('d/m/Y H:i', strtotime($user['last_login'])) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Jamais</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= htmlspecialchars(json_encode($user)) ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['username']) ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
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

    <!-- Modal d'édition d'utilisateur -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Modifier l'utilisateur</h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editUserForm" method="POST">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <input type="hidden" name="csrf_token" value="<?= bin2hex(random_bytes(32)) ?>">
                    
                    <div class="form-group">
                        <label for="edit_username">Nom d'utilisateur *</label>
                        <input type="text" id="edit_username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_email">Email *</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" id="edit_password" name="password" minlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_role">Rôle *</label>
                        <select id="edit_role" name="role" required>
                            <option value="editeur">Éditeur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="edit_active" name="active" value="1">
                            <span class="checkmark"></span>
                            Compte actif
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Sauvegarder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div id="deleteUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmer la suppression</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <strong id="delete_username"></strong> ?</p>
                <p class="text-danger">Cette action est irréversible.</p>
                
                <form id="deleteUserForm" method="POST">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <input type="hidden" name="csrf_token" value="<?= bin2hex(random_bytes(32)) ?>">
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Annuler</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            document.getElementById('edit_active').checked = user.active;
            document.getElementById('edit_password').value = '';
            
            document.getElementById('editUserModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editUserModal').style.display = 'none';
        }
        
        function deleteUser(userId, username) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('delete_username').textContent = username;
            document.getElementById('deleteUserModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteUserModal').style.display = 'none';
        }
        
        // Fermer les modales en cliquant à l'extérieur
        window.onclick = function(event) {
            const editModal = document.getElementById('editUserModal');
            const deleteModal = document.getElementById('deleteUserModal');
            
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }
    </script>
</body>
</html>
