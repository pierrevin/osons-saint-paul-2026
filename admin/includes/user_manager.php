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
            $this->logSecurityEvent('user_created', $username, "User created by {$_SESSION['admin_user']}");
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
    
    /**
     * Récupérer tous les utilisateurs (admin seulement)
     */
    public function getAllUsers() {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            return ['success' => false, 'message' => 'Permissions insuffisantes.'];
        }
        
        $users_data = $this->loadUsers();
        return ['success' => true, 'users' => $users_data['users']];
    }
    
    /**
     * Modifier un utilisateur (admin seulement)
     */
    public function updateUser($user_id, $data) {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            return ['success' => false, 'message' => 'Permissions insuffisantes.'];
        }
        
        $users_data = $this->loadUsers();
        
        foreach ($users_data['users'] as &$user) {
            if ($user['id'] == $user_id) {
                if (isset($data['username'])) $user['username'] = $data['username'];
                if (isset($data['email'])) $user['email'] = $data['email'];
                if (isset($data['role'])) $user['role'] = $data['role'];
                if (isset($data['active'])) $user['active'] = (bool)$data['active'];
                if (isset($data['password']) && !empty($data['password'])) {
                    $user['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
                }
                
                if (file_put_contents($this->users_file, json_encode($users_data, JSON_PRETTY_PRINT))) {
                    $this->logSecurityEvent('user_updated', $user['username'], "User updated by {$_SESSION['admin_user']}");
                    return ['success' => true, 'message' => 'Utilisateur modifié avec succès.'];
                }
                break;
            }
        }
        
        return ['success' => false, 'message' => 'Utilisateur non trouvé.'];
    }
    
    /**
     * Supprimer un utilisateur (admin seulement)
     */
    public function deleteUser($user_id) {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            return ['success' => false, 'message' => 'Permissions insuffisantes.'];
        }
        
        // Empêcher la suppression de son propre compte
        if ($user_id == $_SESSION['user_id']) {
            return ['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'];
        }
        
        $users_data = $this->loadUsers();
        
        foreach ($users_data['users'] as $index => $user) {
            if ($user['id'] == $user_id) {
                $username = $user['username'];
                unset($users_data['users'][$index]);
                $users_data['users'] = array_values($users_data['users']); // Réindexer
                
                if (file_put_contents($this->users_file, json_encode($users_data, JSON_PRETTY_PRINT))) {
                    $this->logSecurityEvent('user_deleted', $username, "User deleted by {$_SESSION['admin_user']}");
                    return ['success' => true, 'message' => 'Utilisateur supprimé avec succès.'];
                }
                break;
            }
        }
        
        return ['success' => false, 'message' => 'Utilisateur non trouvé.'];
    }
    
    /**
     * Générer un code 2FA
     */
    public function generate2FACode($user_id) {
        $code = sprintf('%06d', mt_rand(100000, 999999));
        $expires = time() + 300; // 5 minutes
        
        // Stocker le code temporairement
        $temp_codes = $this->loadTempCodes();
        $temp_codes[$user_id] = [
            'code' => $code,
            'expires' => $expires,
            'attempts' => 0
        ];
        $this->saveTempCodes($temp_codes);
        
        return $code;
    }
    
    /**
     * Vérifier un code 2FA
     */
    public function verify2FACode($user_id, $code) {
        $temp_codes = $this->loadTempCodes();
        
        if (!isset($temp_codes[$user_id])) {
            return ['success' => false, 'message' => 'Code non trouvé'];
        }
        
        $temp_code = $temp_codes[$user_id];
        
        // Vérifier l'expiration
        if (time() > $temp_code['expires']) {
            unset($temp_codes[$user_id]);
            $this->saveTempCodes($temp_codes);
            return ['success' => false, 'message' => 'Code expiré'];
        }
        
        // Vérifier les tentatives
        if ($temp_code['attempts'] >= 3) {
            unset($temp_codes[$user_id]);
            $this->saveTempCodes($temp_codes);
            return ['success' => false, 'message' => 'Trop de tentatives'];
        }
        
        // Vérifier le code
        if ($temp_code['code'] === $code) {
            unset($temp_codes[$user_id]);
            $this->saveTempCodes($temp_codes);
            return ['success' => true, 'message' => 'Code valide'];
        } else {
            $temp_codes[$user_id]['attempts']++;
            $this->saveTempCodes($temp_codes);
            return ['success' => false, 'message' => 'Code incorrect'];
        }
    }
    
    /**
     * Charger les codes temporaires
     */
    private function loadTempCodes() {
        $file = __DIR__ . '/../logs/temp_2fa_codes.json';
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true) ?: [];
        }
        return [];
    }
    
    /**
     * Sauvegarder les codes temporaires
     */
    private function saveTempCodes($codes) {
        $file = __DIR__ . '/../logs/temp_2fa_codes.json';
        file_put_contents($file, json_encode($codes, JSON_PRETTY_PRINT));
    }
    
    /**
     * Générer un secret Google Authenticator
     */
    public function generateGoogleAuthSecret($user_id) {
        $secret = $this->generateRandomSecret();
        
        // Sauvegarder le secret pour l'utilisateur
        $users = $this->loadUsers();
        foreach ($users['users'] as &$user) {
            if ($user['id'] == $user_id) {
                $user['google_auth_secret'] = $secret;
                break;
            }
        }
        $this->saveUsers($users);
        
        return $secret;
    }
    
    /**
     * Vérifier un code Google Authenticator
     */
    public function verifyGoogleAuthCode($user_id, $code) {
        $users = $this->loadUsers();
        $user = null;
        
        foreach ($users['users'] as $u) {
            if ($u['id'] == $user_id) {
                $user = $u;
                break;
            }
        }
        
        if (!$user || !isset($user['google_auth_secret'])) {
            return ['success' => false, 'message' => 'Secret Google Auth non configuré'];
        }
        
        // Vérification simple (en production, utiliser une librairie dédiée)
        $secret = $user['google_auth_secret'];
        $current_time = floor(time() / 30);
        
        for ($i = -1; $i <= 1; $i++) {
            $time = $current_time + $i;
            $expected_code = $this->generateTOTPCode($secret, $time);
            if ($expected_code === $code) {
                return ['success' => true, 'message' => 'Code valide'];
            }
        }
        
        return ['success' => false, 'message' => 'Code incorrect'];
    }
    
    /**
     * Générer un secret aléatoire
     */
    private function generateRandomSecret($length = 16) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $secret;
    }
    
    /**
     * Générer un code TOTP
     */
    private function generateTOTPCode($secret, $time) {
        $key = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $time);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        return sprintf('%06d', $code);
    }
    
    /**
     * Décoder base32
     */
    private function base32Decode($input) {
        $map = array_flip(str_split('ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'));
        $input = strtoupper($input);
        $output = '';
        $v = 0;
        $vbits = 0;
        
        for ($i = 0; $i < strlen($input); $i++) {
            $v <<= 5;
            $v += $map[$input[$i]];
            $vbits += 5;
            
            if ($vbits >= 8) {
                $output .= chr(($v >> ($vbits - 8)) & 255);
                $vbits -= 8;
            }
        }
        
        return $output;
    }

    /**
     * Vérifier les permissions spécifiques selon le rôle
     */
    public function canAccess($section) {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_role'])) {
            return false;
        }
        
        $role = $_SESSION['user_role'];
        
        // Permissions par section
        $permissions = [
            'admin' => [
                'gestion_utilisateurs' => true,
                'gestion_programme' => true,
                'gestion_rdv' => true,
                'bilan_propositions' => true,
                'parametres' => true,
                'logs' => true
            ],
            'editeur' => [
                'gestion_utilisateurs' => false,
                'gestion_programme' => true,
                'gestion_rdv' => true,
                'bilan_propositions' => true,
                'parametres' => false,
                'logs' => false
            ]
        ];
        
        return $permissions[$role][$section] ?? false;
    }
    
    /**
     * Obtenir le menu selon le rôle
     */
    public function getMenuItems() {
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_role'])) {
            return [];
        }
        
        $role = $_SESSION['user_role'];
        
        $menu_items = [
            'admin' => [
                ['id' => 'gestion-programme', 'title' => 'Gestion Programme', 'icon' => 'fas fa-list', 'section' => 'gestion_programme'],
                ['id' => 'gestion-rdv', 'title' => 'Gestion RDV', 'icon' => 'fas fa-calendar', 'section' => 'gestion_rdv'],
                ['id' => 'bilan-propositions', 'title' => 'Réponse Questionnaire', 'icon' => 'fas fa-chart-bar', 'section' => 'bilan_propositions'],
                ['id' => 'gestion-utilisateurs', 'title' => 'Gestion Utilisateurs', 'icon' => 'fas fa-users', 'section' => 'gestion_utilisateurs'],
                ['id' => 'logs', 'title' => 'Logs de Sécurité', 'icon' => 'fas fa-shield-alt', 'section' => 'logs']
            ],
            'editeur' => [
                ['id' => 'gestion-programme', 'title' => 'Gestion Programme', 'icon' => 'fas fa-list', 'section' => 'gestion_programme'],
                ['id' => 'gestion-rdv', 'title' => 'Gestion RDV', 'icon' => 'fas fa-calendar', 'section' => 'gestion_rdv'],
                ['id' => 'bilan-propositions', 'title' => 'Réponse Questionnaire', 'icon' => 'fas fa-chart-bar', 'section' => 'bilan_propositions']
            ]
        ];
        
        return $menu_items[$role] ?? [];
    }
}
?>
