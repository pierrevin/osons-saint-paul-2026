<?php
/**
 * Section Gestion des Utilisateurs
 * Intégrée dans l'architecture unifiée
 */

// Inclure UserManager si pas déjà fait
if (!class_exists('UserManager')) {
    require_once __DIR__ . '/../../includes/user_manager.php';
}

class GestionUtilisateursSection extends AdminSection {
    private $userManager;
    
    public function __construct($content) {
        parent::__construct('gestion_utilisateurs', 'Gestion Utilisateurs', 'fas fa-users', $content);
        $this->userManager = new UserManager();
    }
    
    public function renderForm() {
        $users = $this->userManager->getAllUsers();
        
        $html = '<div class="section-header">';
        $html .= '<h3><i class="fas fa-users"></i> Gestion des Utilisateurs</h3>';
        $html .= '<p>Gérez les comptes d\'accès à l\'administration</p>';
        $html .= '</div>';
        
        // Actions rapides
        $html .= '<div class="content-section">';
        $html .= '<div class="section-header">';
        $html .= '<h4>Actions rapides</h4>';
        $html .= '<button class="btn btn-primary" onclick="AdminModal.open(\'createUserModal\')">';
        $html .= '<i class="fas fa-plus"></i> Créer un utilisateur';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Liste des utilisateurs
        $html .= '<div class="content-section">';
        $html .= '<h4>Utilisateurs existants</h4>';
        
        if (empty($users)) {
            $html .= '<div class="empty-state">';
            $html .= '<p>Aucun utilisateur trouvé.</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="table-responsive">';
            $html .= '<table class="users-table">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>Nom d\'utilisateur</th>';
            $html .= '<th>Email</th>';
            $html .= '<th>Rôle</th>';
            $html .= '<th>Dernière connexion</th>';
            $html .= '<th>Actions</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            
            foreach ($users as $user) {
                $html .= '<tr>';
                $html .= '<td><strong>' . htmlspecialchars($user['username']) . '</strong></td>';
                $html .= '<td>' . htmlspecialchars($user['email'] ?? 'N/A') . '</td>';
                $html .= '<td>';
                $html .= '<span class="role-badge role-' . htmlspecialchars($user['role']) . '">';
                $html .= ucfirst($user['role']);
                $html .= '</span>';
                $html .= '</td>';
                $html .= '<td>';
                $html .= $user['last_login'] ? date('d/m/Y H:i', $user['last_login']) : 'Jamais';
                $html .= '</td>';
                $html .= '<td>';
                $html .= '<div class="user-actions">';
                $html .= '<button class="btn btn-sm btn-secondary" onclick="editUser(\'' . $user['id'] . '\')">';
                $html .= '<i class="fas fa-edit"></i>';
                $html .= '</button>';
                $html .= '<button class="btn btn-sm btn-warning" onclick="resetPassword(\'' . $user['id'] . '\')">';
                $html .= '<i class="fas fa-key"></i>';
                $html .= '</button>';
                if ($user['id'] !== $_SESSION['user_id']) {
                    $html .= '<button class="btn btn-sm btn-danger" onclick="deleteUser(\'' . $user['id'] . '\')">';
                    $html .= '<i class="fas fa-trash"></i>';
                    $html .= '</button>';
                }
                $html .= '</div>';
                $html .= '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    public function processFormData($data) {
        // Cette section ne traite pas de données de formulaire directement
        // Les actions sont gérées via AJAX ou des modals
        return ['success' => true, 'message' => 'Section utilisateurs chargée'];
    }
    
    public function handleSubmission($data) {
        $action = $data['action'] ?? '';
        
        try {
            switch ($action) {
                case 'create_user':
                    $username = $data['username'] ?? '';
                    $password = $data['password'] ?? '';
                    $role = $data['role'] ?? 'editeur';
                    $email = $data['email'] ?? '';
                    
                    if (empty($username) || empty($password) || empty($email)) {
                        throw new Exception('Tous les champs sont obligatoires.');
                    }
                    
                    $result = $this->userManager->createUser($username, $password, $role, $email);
                    if ($result['success']) {
                        return ['success' => true, 'message' => $result['message']];
                    } else {
                        throw new Exception($result['message']);
                    }
                    
                case 'update_user':
                    $user_id = $data['user_id'] ?? '';
                    $username = $data['username'] ?? '';
                    $email = $data['email'] ?? '';
                    $role = $data['role'] ?? '';
                    
                    if (empty($user_id) || empty($username) || empty($email)) {
                        throw new Exception('Tous les champs sont obligatoires.');
                    }
                    
                    $result = $this->userManager->updateUser($user_id, $username, $email, $role);
                    if ($result['success']) {
                        return ['success' => true, 'message' => $result['message']];
                    } else {
                        throw new Exception($result['message']);
                    }
                    
                case 'delete_user':
                    $user_id = $data['user_id'] ?? '';
                    
                    if (empty($user_id)) {
                        throw new Exception('ID utilisateur manquant.');
                    }
                    
                    $result = $this->userManager->deleteUser($user_id);
                    if ($result['success']) {
                        return ['success' => true, 'message' => $result['message']];
                    } else {
                        throw new Exception($result['message']);
                    }
                    
                case 'reset_password':
                    $user_id = $data['user_id'] ?? '';
                    $new_password = $data['new_password'] ?? '';
                    
                    if (empty($user_id) || empty($new_password)) {
                        throw new Exception('ID utilisateur et nouveau mot de passe requis.');
                    }
                    
                    $result = $this->userManager->resetPassword($user_id, $new_password);
                    if ($result['success']) {
                        return ['success' => true, 'message' => $result['message']];
                    } else {
                        throw new Exception($result['message']);
                    }
                    
                default:
                    throw new Exception('Action non reconnue.');
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
