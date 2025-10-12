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
        
        // Carte Programme
        $html .= '<div class="stat-card programme-card" onclick="navigateToSection(\'programme\')">';
        $html .= '<div class="stat-icon programme-icon"><i class="fas fa-list"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . $propositions['total'] . '</span>';
        $html .= '<span class="stat-label">Propositions</span>';
        $html .= '<div class="stat-details">';
        $html .= '<small>Équipe: ' . $propositions['equipe'] . ' | Citoyennes: ' . $propositions['citoyennes_validees'] . '</small>';
        if ($propositions['en_attente'] > 0) {
            $html .= '<div class="alert-badge">' . $propositions['en_attente'] . ' à valider</div>';
        }
        $html .= '</div>';
        $html .= '<div class="quick-actions">';
        $html .= '<button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); openProposalModal(\'create\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); navigateToSection(\'programme\')">';
        $html .= '<i class="fas fa-edit"></i> Gérer';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Carte Rendez-vous
        $html .= '<div class="stat-card rendez-vous-card" onclick="navigateToSection(\'rendez_vous\')">';
        $html .= '<div class="stat-icon rendez-vous-icon"><i class="fas fa-calendar"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . $rendez_vous_count . '</span>';
        $html .= '<span class="stat-label">Rendez-vous</span>';
        $html .= '<div class="stat-details">';
        
        // Afficher les prochains événements
        $events = $this->content['rendez_vous']['events'] ?? [];
        $upcomingCount = 0;
        foreach ($events as $event) {
            if (isset($event['date']) && strtotime($event['date']) >= time()) {
                $upcomingCount++;
            }
        }
        $html .= '<small>À venir: ' . $upcomingCount . ' | Passés: ' . ($rendez_vous_count - $upcomingCount) . '</small>';
        $html .= '</div>';
        $html .= '<div class="quick-actions">';
        $html .= '<button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); AdminModal.open(\'addEventModal\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); navigateToSection(\'rendez_vous\')">';
        $html .= '<i class="fas fa-edit"></i> Gérer';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
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
        // Essayer d'utiliser les vraies données GA uniquement
        $analytics = null;
        $errorMessage = '';
        
        try {
            // Charger l'autoloader Composer uniquement pour cette section
            $autoloadPath = __DIR__ . '/../../../vendor/autoload.php';
            if (!file_exists($autoloadPath)) {
                throw new Exception('Composer autoload non trouvé. Exécutez "composer install"');
            }
            
            // Charger l'autoloader seulement si les classes Google ne sont pas déjà disponibles
            if (!class_exists('Google\Client')) {
                require_once $autoloadPath;
            }
            
            // Vérifier si les classes sont maintenant disponibles
            if (!class_exists('Google\Client')) {
                throw new Exception('Classes Google non disponibles après chargement de l\'autoloader');
            }
            
            // Vérifier les dépendances spécifiques
            if (!class_exists('Google\Auth\Credentials\ServiceAccountCredentials')) {
                throw new Exception('Dépendance Google Auth manquante (ServiceAccountCredentials). Exécutez "composer install" sur le serveur.');
            }
            
            if (!file_exists(__DIR__ . '/../../../credentials/ga-service-account.json')) {
                throw new Exception('Fichier credentials Google Analytics manquant');
            }
            
            // Utiliser les vraies données
            try {
                $analytics = new GoogleAnalyticsReal();
            } catch (Exception $e) {
                throw new Exception('Erreur lors de l\'initialisation Google Analytics : ' . $e->getMessage());
            }
            
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            error_log("Google Analytics Error: " . $errorMessage);
        } catch (Error $e) {
            // Capturer aussi les erreurs fatales PHP
            $errorMessage = 'Erreur fatale Google Analytics : ' . $e->getMessage();
            error_log("Google Analytics Fatal Error: " . $errorMessage);
        }
        
        // Si erreur, afficher message et arrêter
        if (!$analytics) {
            $html = '<div class="analytics-dashboard">';
            $html .= '<div class="analytics-header">';
            $html .= '<h3><i class="fas fa-chart-line"></i> Statistiques Google Analytics</h3>';
            $html .= '</div>';
            $html .= '<div class="analytics-error">';
            $html .= '<div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>';
            $html .= '<div class="error-content">';
            $html .= '<h4>Configuration requise</h4>';
            $html .= '<p>' . htmlspecialchars($errorMessage) . '</p>';
            $html .= '<a href="test-google-analytics.php" class="btn btn-primary">';
            $html .= '<i class="fas fa-tools"></i> Diagnostic Google Analytics';
            $html .= '</a>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
            return $html;
        }
        
        // Récupérer les données réelles
        $defaultPeriod = 30;
        $stats = $analytics->getGeneralStats($defaultPeriod);
        $realtime = $analytics->getRealtimeData();
        $topPages = $analytics->getTopPages(5);
        $trafficSources = $analytics->getTrafficSources(5);
        
        // Vérifier s'il y a des erreurs dans les données
        $hasAnalyticsError = false;
        $analyticsErrorMessage = $analytics->getInitializationError();
        
        if ($analyticsErrorMessage || isset($stats['error']) || isset($realtime['error'])) {
            $hasAnalyticsError = true;
            if (!$analyticsErrorMessage) {
                $analyticsErrorMessage = $stats['error'] ?? $realtime['error'] ?? 'Erreur inconnue Google Analytics';
            }
        }
        
        // Récupérer les données pour les différentes périodes
        $timeSeries7 = $analytics->getTimeSeriesData(7);
        $timeSeries30 = $analytics->getTimeSeriesData(30);
        $timeSeries90 = $analytics->getTimeSeriesData(90);
        
        $html = '<div class="analytics-dashboard">';
        
        // Afficher un message d'erreur si nécessaire
        if ($hasAnalyticsError) {
            $html .= '<div class="analytics-error-notice">';
            $html .= '<div class="alert alert-warning">';
            $html .= '<i class="fas fa-exclamation-triangle"></i>';
            $html .= '<strong>Attention :</strong> ' . htmlspecialchars($analyticsErrorMessage);
            $html .= '<br><small>Les statistiques peuvent être incomplètes ou inexactes.</small>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        // En-tête avec sélecteur de période
        $html .= '<div class="analytics-header">';
        $html .= '<div class="analytics-title">';
        $html .= '<h3><i class="fas fa-chart-line"></i> Statistiques Google Analytics</h3>';
        $html .= '<div class="analytics-info-compact">';
        $html .= '<div class="data-indicator real-data"><i class="fas fa-check-circle"></i> Données réelles</div>';
        $html .= '<span class="property-id">Property ID: ' . $analytics->getPropertyId() . '</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Sélecteur de période
        $html .= '<div class="analytics-period-selector">';
        $html .= '<button class="period-btn" data-period="7" onclick="AdminAnalytics.changePeriod(7)">7 jours</button>';
        $html .= '<button class="period-btn active" data-period="30" onclick="AdminAnalytics.changePeriod(30)">30 jours</button>';
        $html .= '<button class="period-btn" data-period="90" onclick="AdminAnalytics.changePeriod(90)">3 mois</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Indicateurs KPI principaux
        $html .= '<div class="analytics-kpi-cards">';
        
        // CARTE FUSIONNÉE : Visiteurs + En ligne
        $html .= '<div class="kpi-card kpi-visitors">';
        $html .= '<div class="kpi-icon"><i class="fas fa-users"></i></div>';
        $html .= '<div class="kpi-content">';
        $html .= '<span class="kpi-number" id="kpi-visitors">' . number_format($stats['total_users']) . '</span>';
        $html .= '<span class="kpi-label">Visiteurs</span>';
        $html .= '<div class="kpi-realtime-badge">';
        $html .= '<i class="fas fa-circle pulse-dot"></i>';
        $html .= '<span>' . $realtime['total_active_users'] . ' en ligne</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="kpi-card kpi-pageviews">';
        $html .= '<div class="kpi-icon"><i class="fas fa-eye"></i></div>';
        $html .= '<div class="kpi-content">';
        $html .= '<span class="kpi-number" id="kpi-pageviews">' . number_format($stats['total_pageviews']) . '</span>';
        $html .= '<span class="kpi-label">Pages vues</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="kpi-card kpi-duration">';
        $html .= '<div class="kpi-icon"><i class="fas fa-clock"></i></div>';
        $html .= '<div class="kpi-content">';
        $html .= '<span class="kpi-number" id="kpi-duration">' . GoogleAnalyticsSimple::formatDuration($stats['avg_session_duration']) . '</span>';
        $html .= '<span class="kpi-label">Durée moyenne</span>';
        $html .= '<div class="kpi-progress">';
        $progressPercent = min(100, ($stats['avg_session_duration'] / 300) * 100); // 5 min = 100%
        $html .= '<div class="kpi-progress-bar" style="width: ' . $progressPercent . '%"></div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="kpi-card kpi-bounce">';
        $html .= '<div class="kpi-icon"><i class="fas fa-chart-line"></i></div>';
        $html .= '<div class="kpi-content">';
        $html .= '<span class="kpi-number" id="kpi-bounce">' . round($stats['bounce_rate'] * 100, 1) . '%</span>';
        $html .= '<span class="kpi-label">Taux de rebond</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>'; // Fin analytics-kpi-cards
        
        // Graphique d'évolution temporelle
        $html .= '<div class="analytics-chart-container">';
        $html .= '<h4><i class="fas fa-chart-area"></i> Évolution dans le temps</h4>';
        $html .= '<canvas id="analyticsTimeSeriesChart"></canvas>';
        $html .= '</div>';
        
        // Grille avec pages populaires et sources
        $html .= '<div class="analytics-grid">';
        
        // Pages populaires avec barres de progression
        $html .= '<div class="analytics-box popular-pages-visual">';
        $html .= '<h4><i class="fas fa-file-alt"></i> Pages populaires</h4>';
        $html .= '<div class="pages-list-visual">';
        
        $pageviewsColumn = array_column($topPages, 'pageviews');
        $maxPageviews = !empty($pageviewsColumn) ? max($pageviewsColumn) : 1;
        foreach ($topPages as $index => $page) {
            $percentage = $maxPageviews > 0 ? ($page['pageviews'] / $maxPageviews) * 100 : 0;
            $html .= '<div class="page-item-visual">';
            $html .= '<div class="page-info">';
            $html .= '<span class="page-rank">' . ($index + 1) . '</span>';
            $html .= '<div class="page-details">';
            $html .= '<div class="page-title">' . htmlspecialchars($page['title']) . '</div>';
            $html .= '<div class="page-path">' . htmlspecialchars($page['path']) . '</div>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="page-stats">';
            $html .= '<div class="page-progress">';
            $html .= '<div class="page-progress-bar" style="width: ' . $percentage . '%"></div>';
            $html .= '</div>';
            $html .= '<span class="page-views">' . number_format($page['pageviews']) . '</span>';
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        // Sources de trafic avec graphique donut
        $html .= '<div class="analytics-box traffic-sources-visual">';
        $html .= '<h4><i class="fas fa-external-link-alt"></i> Sources de trafic</h4>';
        $html .= '<div class="traffic-chart-container">';
        $html .= '<canvas id="trafficSourcesChart"></canvas>';
        $html .= '</div>';
        $html .= '<div class="traffic-list">';
        
        // Icônes par type de source
        $sourceIcons = [
            'google' => 'fab fa-google',
            'facebook' => 'fab fa-facebook',
            'instagram' => 'fab fa-instagram',
            'youtube' => 'fab fa-youtube',
            'bing' => 'fab fa-microsoft',
            'direct' => 'fas fa-link'
        ];
        
        foreach ($trafficSources as $source) {
            $sourceName = strtolower($source['source']);
            $icon = $sourceIcons[$sourceName] ?? 'fas fa-globe';
            
            $html .= '<div class="traffic-item">';
            $html .= '<i class="' . $icon . ' traffic-icon"></i>';
            $html .= '<div class="traffic-info">';
            $html .= '<div class="traffic-name">' . htmlspecialchars($source['source']) . '</div>';
            $html .= '<div class="traffic-medium">' . htmlspecialchars($source['medium']) . '</div>';
            $html .= '</div>';
            $html .= '<span class="traffic-sessions">' . number_format($source['sessions']) . '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>'; // analytics-grid
        
        $html .= '</div>'; // analytics-dashboard
        
        // Injecter les données pour les graphiques JavaScript
        $html .= '<script>';
        $html .= 'window.analyticsData = {';
        $html .= '  timeSeries: {';
        $html .= '    "7": ' . (json_encode($timeSeries7) ?: '{"labels":[],"users":[],"pageviews":[]}') . ',';
        $html .= '    "30": ' . (json_encode($timeSeries30) ?: '{"labels":[],"users":[],"pageviews":[]}') . ',';
        $html .= '    "90": ' . (json_encode($timeSeries90) ?: '{"labels":[],"users":[],"pageviews":[]}');
        $html .= '  },';
        $html .= '  trafficSources: ' . (json_encode($trafficSources) ?: '[]') . ',';
        $html .= '  currentPeriod: 30';
        $html .= '};';
        $html .= '</script>';
        
        return $html;
    }
}

