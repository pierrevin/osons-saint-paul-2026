<?php
/**
 * Section Logs de Sécurité
 * Intégrée dans l'architecture unifiée
 */

class LogsSecuriteSection extends AdminSection {
    
    public function __construct($content) {
        parent::__construct('logs_securite', 'Logs de Sécurité', 'fas fa-shield-alt', $content);
    }
    
    public function renderForm() {
        // Lire les logs de sécurité
        $log_file = __DIR__ . '/../../logs/security.log';
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
                return strpos($log['username'] ?? '', $username_filter) !== false;
            });
        }
        
        // Statistiques
        $total_logs = count($logs);
        $failed_logins = count(array_filter($logs, function($log) {
            return strpos($log['event'], 'login_failed') !== false;
        }));
        $successful_logins = count(array_filter($logs, function($log) {
            return strpos($log['event'], 'login_success') !== false;
        }));
        
        $html = '<div class="section-header">';
        $html .= '<h3><i class="fas fa-shield-alt"></i> Logs de Sécurité</h3>';
        $html .= '<p>Surveillance des accès et activités du système</p>';
        $html .= '</div>';
        
        // Statistiques
        $html .= '<div class="stats-grid">';
        $html .= '<div class="stat-card">';
        $html .= '<span class="stat-number">' . $total_logs . '</span>';
        $html .= '<span class="stat-label">Total des événements</span>';
        $html .= '</div>';
        $html .= '<div class="stat-card">';
        $html .= '<span class="stat-number">' . $failed_logins . '</span>';
        $html .= '<span class="stat-label">Tentatives échouées</span>';
        $html .= '</div>';
        $html .= '<div class="stat-card">';
        $html .= '<span class="stat-number">' . $successful_logins . '</span>';
        $html .= '<span class="stat-label">Connexions réussies</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Filtres
        $html .= '<div class="filters">';
        $html .= '<form method="GET" class="filter-group">';
        $html .= '<div class="form-group">';
        $html .= '<label for="event">Événement</label>';
        $html .= '<select id="event" name="event">';
        $html .= '<option value="">Tous les événements</option>';
        $html .= '<option value="login"' . (isset($_GET['event']) && $_GET['event'] === 'login' ? ' selected' : '') . '>Connexions</option>';
        $html .= '<option value="login_failed"' . (isset($_GET['event']) && $_GET['event'] === 'login_failed' ? ' selected' : '') . '>Échecs de connexion</option>';
        $html .= '<option value="logout"' . (isset($_GET['event']) && $_GET['event'] === 'logout' ? ' selected' : '') . '>Déconnexions</option>';
        $html .= '<option value="access_denied"' . (isset($_GET['event']) && $_GET['event'] === 'access_denied' ? ' selected' : '') . '>Accès refusés</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        $html .= '<div class="form-group">';
        $html .= '<label for="username">Utilisateur</label>';
        $html .= '<input type="text" id="username" name="username" placeholder="Nom d\'utilisateur" value="' . htmlspecialchars($_GET['username'] ?? '') . '">';
        $html .= '</div>';
        
        $html .= '<div class="form-group">';
        $html .= '<button type="submit" class="btn btn-primary">';
        $html .= '<i class="fas fa-search"></i> Filtrer';
        $html .= '</button>';
        $html .= '<a href="?section=logs_securite" class="btn btn-secondary">';
        $html .= '<i class="fas fa-times"></i> Effacer';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</form>';
        $html .= '</div>';
        
        // Liste des logs
        $html .= '<div class="content-section">';
        $html .= '<div class="section-header">';
        $html .= '<h4>Événements de sécurité</h4>';
        $html .= '<span class="text-muted">' . count($filtered_logs) . ' événement(s) trouvé(s)</span>';
        $html .= '</div>';
        
        if (empty($filtered_logs)) {
            $html .= '<div class="empty-state">';
            $html .= '<p>Aucun log trouvé avec ces critères.</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="table-responsive">';
            $html .= '<table class="logs-table">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>Date/Heure</th>';
            $html .= '<th>Événement</th>';
            $html .= '<th>Utilisateur</th>';
            $html .= '<th>Adresse IP</th>';
            $html .= '<th>Détails</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            
            foreach ($filtered_logs as $log) {
                $level = 'info';
                if (strpos($log['event'], 'failed') !== false || strpos($log['event'], 'denied') !== false) {
                    $level = 'error';
                } elseif (strpos($log['event'], 'success') !== false) {
                    $level = 'success';
                } elseif (strpos($log['event'], 'warning') !== false) {
                    $level = 'warning';
                }
                
                $html .= '<tr>';
                $html .= '<td>';
                $html .= '<span class="log-timestamp">';
                $html .= date('d/m/Y H:i:s', strtotime($log['timestamp']));
                $html .= '</span>';
                $html .= '</td>';
                $html .= '<td>';
                $html .= '<span class="log-level ' . $level . '">';
                $html .= htmlspecialchars($log['event']);
                $html .= '</span>';
                $html .= '</td>';
                $html .= '<td>';
                $html .= '<strong>' . htmlspecialchars($log['username'] ?? 'N/A') . '</strong>';
                $html .= '</td>';
                $html .= '<td>';
                $html .= '<span class="log-ip">' . htmlspecialchars($log['ip'] ?? 'N/A') . '</span>';
                $html .= '</td>';
                $html .= '<td>';
                if (isset($log['details']) && !empty($log['details'])) {
                    $html .= '<small class="text-muted">';
                    $html .= htmlspecialchars($log['details']);
                    $html .= '</small>';
                } else {
                    $html .= '<span class="text-muted">-</span>';
                }
                $html .= '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
            
            // Pagination simple
            if (count($filtered_logs) > 50) {
                $html .= '<div style="margin-top: 1rem; text-align: center;">';
                $html .= '<p class="text-muted">';
                $html .= 'Affichage des 50 événements les plus récents.';
                $html .= '<a href="#" onclick="loadMoreLogs()"> Charger plus</a>';
                $html .= '</p>';
                $html .= '</div>';
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    public function processFormData($data) {
        // Cette section ne traite pas de données de formulaire directement
        return ['success' => true, 'message' => 'Section logs chargée'];
    }
    
    public function handleSubmission($data) {
        // Pas de soumission de formulaire pour cette section
        return ['success' => true, 'message' => 'Logs affichés'];
    }
}
