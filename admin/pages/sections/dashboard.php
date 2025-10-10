<?php
/**
 * Section Tableau de Bord
 * Vue d'ensemble avec cartes de navigation rapide
 */

require_once __DIR__ . '/../../includes/GoogleAnalyticsSimple.php';
require_once __DIR__ . '/../../includes/GoogleAnalyticsReal.php';

class DashboardSection extends AdminSection {
    
    public function __construct($content) {
        parent::__construct('dashboard', 'Tableau de Bord', 'fas fa-tachometer-alt', $content);
    }
    
    public function renderForm() {
        // Calculer les statistiques des propositions
        $propositions = $this->loadPropositionsData();
        $programme_count = count($this->content['programme']['proposals'] ?? []);
        $rendez_vous_count = count($this->content['rendez_vous']['events'] ?? []);
        
        $html = '<div class="section-header">';
        $html .= '<h3><i class="fas fa-tachometer-alt"></i> Tableau de Bord</h3>';
        $html .= '<p>Vue d\'ensemble et accès rapide aux sections</p>';
        $html .= '</div>';
        
        // Statistiques générales
        $html .= '<div class="stats-overview">';
        
        // Indicateur Propositions amélioré
        $html .= '<div class="stat-card propositions-card" onclick="navigateToSection(\'programme\')">';
        $html .= '<div class="stat-icon"><i class="fas fa-list"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . $propositions['total'] . '</span>';
        $html .= '<span class="stat-label">Propositions</span>';
        $html .= '<div class="stat-details">';
        $html .= '<small>Équipe: ' . $propositions['equipe'] . ' | Citoyennes: ' . $propositions['citoyennes_validees'] . '</small>';
        if ($propositions['en_attente'] > 0) {
            $html .= '<div class="alert-badge">' . $propositions['en_attente'] . ' à valider</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Widget fusionné Programme + Rendez-vous
        $html .= '<div class="stat-card merged-card">';
        $html .= '<div class="merged-header">';
        $html .= '<div class="merged-tabs">';
        $html .= '<button class="tab-btn active" data-tab="programme">';
        $html .= '<i class="fas fa-list"></i> Programme (' . $programme_count . ')';
        $html .= '</button>';
        $html .= '<button class="tab-btn" data-tab="rendez-vous">';
        $html .= '<i class="fas fa-calendar"></i> Rendez-vous (' . $rendez_vous_count . ')';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="merged-content">';
        $html .= '<div class="tab-content active" id="tab-programme">';
        $html .= '<div class="quick-actions">';
        $html .= '<button class="btn btn-sm btn-primary" onclick="openProposalModal(\'create\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter proposition';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="navigateToSection(\'programme\')">';
        $html .= '<i class="fas fa-edit"></i> Gérer';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="tab-content" id="tab-rendez-vous">';
        $html .= '<div class="quick-actions">';
        $html .= '<button class="btn btn-sm btn-primary" onclick="AdminModal.open(\'addEventModal\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter événement';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="navigateToSection(\'rendez_vous\')">';
        $html .= '<i class="fas fa-edit"></i> Gérer';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Cartes de navigation rapide (simplifiées)
        $html .= '<div class="dashboard-cards">';
        $html .= '<h4>Navigation Rapide</h4>';
        $html .= '<div class="cards-grid">';
        
        // Carte Équipe
        $html .= '<div class="dashboard-card" onclick="navigateToSection(\'equipe\')">';
        $html .= '<div class="card-header">';
        $html .= '<i class="fas fa-users"></i>';
        $html .= '<h5>Équipe</h5>';
        $html .= '</div>';
        $html .= '<div class="card-content">';
        $html .= '<p>Gérer les membres de l\'équipe</p>';
        $html .= '<div class="card-stats">' . count($this->content['equipe']['members'] ?? []) . ' membres</div>';
        $html .= '</div>';
        $html .= '<div class="card-actions">';
        $html .= '<button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); AdminModal.open(\'addMemberModal\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); navigateToSection(\'equipe\')">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Carte Citations
        $html .= '<div class="dashboard-card" onclick="navigateToSection(\'citations\')">';
        $html .= '<div class="card-header">';
        $html .= '<i class="fas fa-quote-left"></i>';
        $html .= '<h5>Citations</h5>';
        $html .= '</div>';
        $html .= '<div class="card-content">';
        $html .= '<p>Gérer les citations inspirantes</p>';
        $html .= '<div class="card-stats">' . count($this->content['citations'] ?? []) . ' citations</div>';
        $html .= '</div>';
        $html .= '<div class="card-actions">';
        $html .= '<button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); AdminModal.open(\'addCitationModal\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); navigateToSection(\'citations\')">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Carte Contact
        $html .= '<div class="dashboard-card" onclick="navigateToSection(\'contact\')">';
        $html .= '<div class="card-header">';
        $html .= '<i class="fas fa-envelope"></i>';
        $html .= '<h5>Contact</h5>';
        $html .= '</div>';
        $html .= '<div class="card-content">';
        $html .= '<p>Modifier les informations de contact</p>';
        $html .= '<div class="card-stats">Informations publiques</div>';
        $html .= '</div>';
        $html .= '<div class="card-actions">';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); navigateToSection(\'contact\')">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>'; // cards-grid
        $html .= '</div>'; // dashboard-cards
        
        // Ajouter les statistiques Google Analytics
        $html .= $this->renderAnalyticsSection();
        
        return $html;
    }
    
    private function loadPropositionsData() {
        // Charger les propositions citoyennes
        $citizenProposals = [];
        $citizenProposalsFile = DATA_PATH . '/propositions.json';
        if (file_exists($citizenProposalsFile)) {
            $citizenData = json_decode(file_get_contents($citizenProposalsFile), true);
            $citizenProposals = $citizenData['propositions'] ?? [];
        }
        
        // Compter les propositions par statut
        $citoyennes_validees = 0;
        $en_attente = 0;
        
        foreach ($citizenProposals as $proposal) {
            if ($proposal['status'] === 'approved') {
                $citoyennes_validees++;
            } elseif ($proposal['status'] === 'pending') {
                $en_attente++;
            }
        }
        
        // Compter les propositions de l'équipe
        $equipe = count($this->content['programme']['proposals'] ?? []);
        
        return [
            'total' => $equipe + $citoyennes_validees,
            'equipe' => $equipe,
            'citoyennes_validees' => $citoyennes_validees,
            'en_attente' => $en_attente
        ];
    }
    
    public function processFormData($data) {
        // Cette section ne traite pas de données de formulaire
        return ['success' => true, 'message' => 'Tableau de bord chargé'];
    }
    
    public function handleSubmission($data) {
        // Pas de soumission de formulaire pour cette section
        return ['success' => true, 'message' => 'Tableau de bord affiché'];
    }
    
    private function renderAnalyticsSection() {
        // Essayer d'utiliser les vraies données GA, sinon utiliser les données simulées
        try {
            // Vérifier si les classes Google sont disponibles et si le fichier de credentials existe
            if (class_exists('Google\Client') && file_exists(__DIR__ . '/../../credentials/ga-service-account.json')) {
                $analytics = new GoogleAnalyticsReal();
                $isRealData = true;
            } else {
                throw new Exception('Classes Google non disponibles ou credentials manquants');
            }
        } catch (Exception $e) {
            $analytics = new GoogleAnalyticsSimple();
            $isRealData = false;
        }
        
        $stats = $analytics->getGeneralStats(30);
        $realtime = $analytics->getRealtimeData();
        $topPages = $analytics->getTopPages(5);
        $trafficSources = $analytics->getTrafficSources(5);
        
        $html = '<div class="analytics-section">';
        $html .= '<h3><i class="fas fa-chart-line"></i> Statistiques Google Analytics</h3>';
        
        // Indicateur de données
        if ($isRealData) {
            $html .= '<div class="data-indicator real-data"><i class="fas fa-check-circle"></i> Données réelles Google Analytics</div>';
        } else {
            $html .= '<div class="data-indicator demo-data"><i class="fas fa-info-circle"></i> Données de démonstration</div>';
        }
        
        // Statistiques générales
        $html .= '<div class="analytics-stats">';
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-icon"><i class="fas fa-users"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . number_format($stats['total_users']) . '</span>';
        $html .= '<span class="stat-label">Visiteurs (30j)</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-icon"><i class="fas fa-eye"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . number_format($stats['total_pageviews']) . '</span>';
        $html .= '<span class="stat-label">Pages vues</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-icon"><i class="fas fa-clock"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . GoogleAnalyticsSimple::formatDuration($stats['avg_session_duration']) . '</span>';
        $html .= '<span class="stat-label">Durée moyenne</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-icon"><i class="fas fa-broadcast-tower"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . $realtime['total_active_users'] . '</span>';
        $html .= '<span class="stat-label">En ligne maintenant</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Pages populaires
        $html .= '<div class="analytics-details">';
        $html .= '<h4><i class="fas fa-file-alt"></i> Pages populaires</h4>';
        $html .= '<div class="pages-list">';
        foreach ($topPages as $index => $page) {
            $html .= '<div class="page-item">';
            $html .= '<div class="page-rank">' . ($index + 1) . '</div>';
            $html .= '<div class="page-info">';
            $html .= '<div class="page-title">' . htmlspecialchars($page['title']) . '</div>';
            $html .= '<div class="page-path">' . htmlspecialchars($page['path']) . '</div>';
            $html .= '</div>';
            $html .= '<div class="page-stats">';
            $html .= '<span class="page-views">' . $page['pageviews'] . ' vues</span>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        
        // Sources de trafic
        $html .= '<h4><i class="fas fa-external-link-alt"></i> Sources de trafic</h4>';
        $html .= '<div class="sources-list">';
        foreach ($trafficSources as $source) {
            $html .= '<div class="source-item">';
            $html .= '<div class="source-info">';
            $html .= '<div class="source-name">' . htmlspecialchars($source['source']) . '</div>';
            $html .= '<div class="source-medium">' . htmlspecialchars($source['medium']) . '</div>';
            $html .= '</div>';
            $html .= '<div class="source-stats">';
            $html .= '<span class="source-sessions">' . $source['sessions'] . ' sessions</span>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
}
