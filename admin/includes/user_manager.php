<?php
// Gestionnaire d'utilisateurs sécurisé

class UserManager {
    private $users_file;
    private $login_attempts_file;
    
    public function __construct() {
        $this->users_file = __DIR__ . '/../users.json';
        $this->login_attempts_file = __DIR__ . '/../logs/login_attempts.json';
        
        // Créer le dossier logs s'il n'existe pas
        $logs_dir = dirname($this->login_attempts_file);
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }
    }
    
    /**
     * Authentifier un utilisateur
     */
    public function authenticate($username, $password) {
        // Vérifier les tentatives de connexion
        if ($this->isAccountLocked($username)) {
            $this->logSecurityEvent('login_blocked', $username, 'Account locked due to too many failed attempts');
            return ['success' => false, 'message' => 'Compte verrouillé temporairement. Réessayez plus tard.'];
        }
        
        $user = $this->getUserByUsername($username);
        
        if (!$user) {
            $this->recordFailedAttempt($username);
            $this->logSecurityEvent('login_failed', $username, 'User not found');
            return ['success' => false, 'message' => 'Nom d\'utilisateur ou mot de passe incorrect.'];
        }
        
        if (!$user['active']) {
            $this->logSecurityEvent('login_failed', $username, 'Account deactivated');
            return ['success' => false, 'message' => 'Compte désactivé.'];
        }
        
        if (!password_verify($password, $user['password_hash'])) {
            $this->recordFailedAttempt($username);
            $this->logSecurityEvent('login_failed', $username, 'Invalid password');
            return ['success' => false, 'message' => 'Nom d\'utilisateur ou mot de passe incorrect.'];
        }
        
        // Connexion réussie
        $this->clearFailedAttempts($username);
        $this->updateLastLogin($user['id']);
        $this->logSecurityEvent('login_success', $username, 'Successful login');
        
        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'email' => $user['email']
            ]
        ];
    }
    
    /**
     * Récupérer un utilisateur par nom d'utilisateur
     */
    private function getUserByUsername($username) {
        $users_data = $this->loadUsers();
        
        foreach ($users_data['users'] as $user) {
            if ($user['username'] === $username) {
                return $user;
            }
        }
        
        return null;
    }
    
    /**
     * Charger les données des utilisateurs
     */
    private function loadUsers() {
        if (!file_exists($this->users_file)) {
            return ['users' => [], 'settings' => []];
        }
        
        $content = file_get_contents($this->users_file);
        return json_decode($content, true);
    }
    
    /**
     * Vérifier si un compte est verrouillé
     */
    private function isAccountLocked($username) {
        $attempts = $this->getLoginAttempts();
        $user_attempts = $attempts[$username] ?? [];
        
        if (count($user_attempts) < 5) {
            return false;
        }
        
        // Vérifier si le verrouillage a expiré (15 minutes)
        $last_attempt = end($user_attempts);
        $lockout_duration = 900; // 15 minutes
        
        return (time() - $last_attempt) < $lockout_duration;
    }
    
    /**
     * Enregistrer une tentative de connexion échouée
     */
    private function recordFailedAttempt($username) {
        $attempts = $this->getLoginAttempts();
        
        if (!isset($attempts[$username])) {
            $attempts[$username] = [];
        }
        
        $attempts[$username][] = time();
        
        // Garder seulement les 10 dernières tentatives
        $attempts[$username] = array_slice($attempts[$username], -10);
        
        $this->saveLoginAttempts($attempts);
    }
    
    /**
     * Effacer les tentatives de connexion échouées
     */
    private function clearFailedAttempts($username) {
        $attempts = $this->getLoginAttempts();
        unset($attempts[$username]);
        $this->saveLoginAttempts($attempts);
    }
    
    /**
     * Récupérer les tentatives de connexion
     */
    private function getLoginAttempts() {
        if (!file_exists($this->login_attempts_file)) {
            return [];
        }
        
        $content = file_get_contents($this->login_attempts_file);
        return json_decode($content, true) ?: [];
    }
    
    /**
     * Sauvegarder les tentatives de connexion
     */
    private function saveLoginAttempts($attempts) {
        file_put_contents($this->login_attempts_file, json_encode($attempts, JSON_PRETTY_PRINT));
    }
    
    /**
     * Mettre à jour la dernière connexion
     */
    private function updateLastLogin($user_id) {
        $users_data = $this->loadUsers();
        
        foreach ($users_data['users'] as &$user) {
            if ($user['id'] === $user_id) {
                $user['last_login'] = date('c');
                break;
            }
        }
        
        file_put_contents($this->users_file, json_encode($users_data, JSON_PRETTY_PRINT));
    }
    
    /**
     * Enregistrer un événement de sécurité
     */
    private function logSecurityEvent($event, $username, $details) {
        $log_entry = [
            'timestamp' => date('c'),
            'event' => $event,
            'username' => $username,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        $log_file = __DIR__ . '/../logs/security.log';
        $log_line = json_encode($log_entry) . "\n";
        file_put_contents($log_file, $log_line, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Vérifier les permissions d'un utilisateur
     */
    public function hasPermission($user_role, $required_role) {
        $role_hierarchy = [
            'editeur' => 1,
            'admin' => 2
        ];
        
        $user_level = $role_hierarchy[$user_role] ?? 0;
        $required_level = $role_hierarchy[$required_role] ?? 0;
        
        return $user_level >= $required_level;
    }
    
    /**
     * Créer un nouvel utilisateur (admin seulement)
     */
    public function createUser($username, $password, $role, $email) {
        // Vérifier que l'utilisateur actuel est admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            return ['success' => false, 'message' => 'Permissions insuffisantes.'];
        }
        
        $users_data = $this->loadUsers();
        
        // Vérifier que l'utilisateur n'existe pas déjà
        foreach ($users_data['users'] as $user) {
            if ($user['username'] === $username) {
                return ['success' => false, 'message' => 'Ce nom d\'utilisateur existe déjà.'];
            }
        }
        
        // Créer le nouvel utilisateur
        $new_user = [
            'id' => $this->getNextUserId($users_data),
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'email' => $email,
            'created_at' => date('c'),
            'last_login' => null,
            'active' => true
        ];
        
        $users_data['users'][] = $new_user;
        
        if (file_put_contents($this->users_file, json_encode($users_data, JSON_PRETTY_PRINT))) {
            $this->logSecurityEvent('user_created', $username, "User created by {$_SESSION['username']}");
            return ['success' => true, 'message' => 'Utilisateur créé avec succès.'];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de la création de l\'utilisateur.'];
    }
    
    /**
     * Obtenir le prochain ID utilisateur
     */
    private function getNextUserId($users_data) {
        $max_id = 0;
        foreach ($users_data['users'] as $user) {
            if ($user['id'] > $max_id) {
                $max_id = $user['id'];
            }
        }
        return $max_id + 1;
    }
}
?>
