<?php
// Configuration pour le système de propositions citoyennes

// Configuration des emails
define('ADMIN_EMAIL', 'admin@osons-saintpaul.fr'); // À configurer selon vos besoins
define('FROM_EMAIL', 'bonjour@osons-saint-paul.fr'); // Expéditeur OVH
define('SITE_NAME', 'Osons Saint-Paul 2026');

// Configuration des fichiers
define('PROPOSITIONS_DATA_FILE', '../data/propositions.json');
define('SITE_CONTENT_FILE', '../data/site_content.json');

// Configuration de sécurité
define('ADMIN_PASSWORD', 'admin2026'); // À changer en production
define('MAX_PROPOSITION_LENGTH', 500);
define('MAX_TITLE_LENGTH', 100);

// Configuration des catégories
$PROPOSITION_CATEGORIES = [
    'Urbanisme & Logement' => '🏠',
    'Environnement & Nature' => '🌱',
    'Mobilité & Transport' => '🚗',
    'Vie sociale & Solidarité' => '👥',
    'Éducation & Jeunesse' => '🎓',
    'Santé & Bien-être' => '🏥',
    'Culture & Sport' => '🎭',
    'Économie & Commerce' => '💼',
    'Services publics' => '🔧',
    'Autre' => '📝'
];

// Configuration des statuts
$PROPOSITION_STATUSES = [
    'pending' => ['label' => 'En attente', 'color' => '#ffc107'],
    'approved' => ['label' => 'Approuvée', 'color' => '#28a745'],
    'rejected' => ['label' => 'Rejetée', 'color' => '#dc3545']
];

// Fonction pour envoyer un email
function sendEmail($to, $subject, $message, $is_html = true) {
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: ' . ($is_html ? 'text/html' : 'text/plain') . '; charset=UTF-8',
        'From: ' . SITE_NAME . ' <' . FROM_EMAIL . '>',
        'Reply-To: ' . ADMIN_EMAIL
    ];
    
    // Utiliser l'expéditeur d'enveloppe (-f) recommandé par OVH pour la délivrabilité
    return mail($to, $subject, $message, implode("\r\n", $headers), '-f ' . FROM_EMAIL);
}

// Fonction pour valider l'email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fonction pour nettoyer les données
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Fonction pour générer un ID unique
function generateUniqueId() {
    return uniqid('prop_', true);
}

// Fonction pour logger les erreurs
function logError($message, $context = []) {
    $log_file = '../logs/propositions_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message";
    
    if (!empty($context)) {
        $log_entry .= " | Context: " . json_encode($context);
    }
    
    $log_entry .= "\n";
    
    // Créer le dossier logs s'il n'existe pas
    $log_dir = dirname($log_file);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

// Fonction pour créer une sauvegarde automatique
function createBackup() {
    $backup_dir = '../data/backups/propositions/';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $backup_file = $backup_dir . "propositions_backup_$timestamp.json";
    
    if (file_exists(PROPOSITIONS_DATA_FILE)) {
        copy(PROPOSITIONS_DATA_FILE, $backup_file);
        
        // Garder seulement les 10 dernières sauvegardes
        $files = glob($backup_dir . "propositions_backup_*.json");
        if (count($files) > 10) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            for ($i = 10; $i < count($files); $i++) {
                unlink($files[$i]);
            }
        }
        
        return $backup_file;
    }
    
    return false;
}

// Fonction pour vérifier la limite de propositions par email
function checkEmailLimit($email, $limit = 5) {
    if (!file_exists(PROPOSITIONS_DATA_FILE)) {
        return true;
    }
    
    $data = json_decode(file_get_contents(PROPOSITIONS_DATA_FILE), true);
    $propositions = $data['propositions'] ?? [];
    
    $email_count = 0;
    $today = date('Y-m-d');
    
    foreach ($propositions as $prop) {
        if ($prop['data']['email'] === $email && date('Y-m-d', strtotime($prop['date'])) === $today) {
            $email_count++;
        }
    }
    
    return $email_count < $limit;
}

// Fonction pour nettoyer les anciennes propositions
function cleanOldPropositions($days = 365) {
    if (!file_exists(PROPOSITIONS_DATA_FILE)) {
        return 0;
    }
    
    $data = json_decode(file_get_contents(PROPOSITIONS_DATA_FILE), true);
    $propositions = $data['propositions'] ?? [];
    
    $cutoff_date = date('Y-m-d H:i:s', strtotime("-$days days"));
    $cleaned_count = 0;
    
    $propositions = array_filter($propositions, function($prop) use ($cutoff_date, &$cleaned_count) {
        if ($prop['date'] < $cutoff_date && $prop['status'] === 'rejected') {
            $cleaned_count++;
            return false;
        }
        return true;
    });
    
    if ($cleaned_count > 0) {
        $data['propositions'] = array_values($propositions);
        $data['last_updated'] = date('c');
        file_put_contents(PROPOSITIONS_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    return $cleaned_count;
}

// Nettoyage automatique des anciennes propositions rejetées (exécuté à chaque chargement)
cleanOldPropositions();
?>
