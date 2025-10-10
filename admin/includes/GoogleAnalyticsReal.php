<?php
/**
 * Service Google Analytics avec vraies données
 * Utilise l'API Google Analytics Data v1
 */

class GoogleAnalyticsReal {
    private $propertyId;
    private $credentialsPath;
    private $client;
    
    public function __construct() {
        $this->propertyId = '12275333436'; // Property ID GA4 que tu as fourni
        $this->credentialsPath = __DIR__ . '/../../credentials/ga-service-account.json';
        $this->initializeClient();
    }
    
    private function initializeClient() {
        // Vérifier si le fichier de credentials existe
        if (!file_exists($this->credentialsPath)) {
            throw new Exception('Fichier de credentials Google Analytics manquant');
        }
        
        // Vérifier si le fichier autoload existe
        $autoloadPath = __DIR__ . '/../../vendor/autoload.php';
        if (!file_exists($autoloadPath)) {
            throw new Exception('Composer autoload non trouvé. Exécutez "composer install"');
        }
        
        // Initialiser le client Google Analytics
        require_once $autoloadPath;
        
        if (!class_exists('Google\Client')) {
            throw new Exception('Classes Google non disponibles. Vérifiez l\'installation de composer');
        }
        
        $client = new \Google\Client();
        $client->setAuthConfig($this->credentialsPath);
        $client->addScope(\Google\Service\AnalyticsData::ANALYTICS_READONLY);
        
        $this->client = new \Google\Service\AnalyticsData($client);
    }
    
    /**
     * Récupère les statistiques générales
     */
    public function getGeneralStats($days = 30) {
        $dateRange = [
            'start_date' => date('Y-m-d', strtotime("-{$days} days")),
            'end_date' => date('Y-m-d')
        ];
        
        $request = new \Google\Service\AnalyticsData\RunReportRequest([
            'property' => "properties/{$this->propertyId}",
            'date_ranges' => [$dateRange],
            'dimensions' => [
                ['name' => 'date']
            ],
            'metrics' => [
                ['name' => 'activeUsers'],
                ['name' => 'screenPageViews'],
                ['name' => 'averageSessionDuration'],
                ['name' => 'bounceRate']
            ],
            'order_bys' => [
                ['dimension' => ['dimension_name' => 'date'], 'desc' => true]
            ]
        ]);
        
        $response = $this->client->properties->runReport($request);
        
        $stats = [
            'total_users' => 0,
            'total_pageviews' => 0,
            'avg_session_duration' => 0,
            'bounce_rate' => 0,
            'daily_data' => []
        ];
        
        foreach ($response->getRows() as $row) {
            $date = $row->getDimensionValues()[0]->getValue();
            $users = $row->getMetricValues()[0]->getValue();
            $pageviews = $row->getMetricValues()[1]->getValue();
            $sessionDuration = $row->getMetricValues()[2]->getValue();
            $bounceRate = $row->getMetricValues()[3]->getValue();
            
            $stats['total_users'] += (int)$users;
            $stats['total_pageviews'] += (int)$pageviews;
            $stats['avg_session_duration'] += (float)$sessionDuration;
            $stats['bounce_rate'] += (float)$bounceRate;
            
            $stats['daily_data'][] = [
                'date' => $date,
                'users' => (int)$users,
                'pageviews' => (int)$pageviews,
                'session_duration' => (float)$sessionDuration,
                'bounce_rate' => (float)$bounceRate
            ];
        }
        
        // Calculer les moyennes
        $dayCount = count($stats['daily_data']);
        if ($dayCount > 0) {
            $stats['avg_session_duration'] = $stats['avg_session_duration'] / $dayCount;
            $stats['bounce_rate'] = $stats['bounce_rate'] / $dayCount;
        }
        
        return $stats;
    }
    
    /**
     * Récupère les pages les plus visitées
     */
    public function getTopPages($limit = 10) {
        $dateRange = [
            'start_date' => date('Y-m-d', strtotime('-30 days')),
            'end_date' => date('Y-m-d')
        ];
        
        $request = new \Google\Service\AnalyticsData\RunReportRequest([
            'property' => "properties/{$this->propertyId}",
            'date_ranges' => [$dateRange],
            'dimensions' => [
                ['name' => 'pagePath'],
                ['name' => 'pageTitle']
            ],
            'metrics' => [
                ['name' => 'screenPageViews'],
                ['name' => 'activeUsers']
            ],
            'order_bys' => [
                ['metric' => ['metric_name' => 'screenPageViews'], 'desc' => true]
            ],
            'limit' => $limit
        ]);
        
        $response = $this->client->properties->runReport($request);
        
        $pages = [];
        foreach ($response->getRows() as $row) {
            $pages[] = [
                'path' => $row->getDimensionValues()[0]->getValue(),
                'title' => $row->getDimensionValues()[1]->getValue(),
                'pageviews' => (int)$row->getMetricValues()[0]->getValue(),
                'users' => (int)$row->getMetricValues()[1]->getValue()
            ];
        }
        
        return $pages;
    }
    
    /**
     * Récupère les sources de trafic
     */
    public function getTrafficSources($limit = 10) {
        $dateRange = [
            'start_date' => date('Y-m-d', strtotime('-30 days')),
            'end_date' => date('Y-m-d')
        ];
        
        $request = new \Google\Service\AnalyticsData\RunReportRequest([
            'property' => "properties/{$this->propertyId}",
            'date_ranges' => [$dateRange],
            'dimensions' => [
                ['name' => 'sessionSource'],
                ['name' => 'sessionMedium']
            ],
            'metrics' => [
                ['name' => 'sessions'],
                ['name' => 'activeUsers']
            ],
            'order_bys' => [
                ['metric' => ['metric_name' => 'sessions'], 'desc' => true]
            ],
            'limit' => $limit
        ]);
        
        $response = $this->client->properties->runReport($request);
        
        $sources = [];
        foreach ($response->getRows() as $row) {
            $sources[] = [
                'source' => $row->getDimensionValues()[0]->getValue(),
                'medium' => $row->getDimensionValues()[1]->getValue(),
                'sessions' => (int)$row->getMetricValues()[0]->getValue(),
                'users' => (int)$row->getMetricValues()[1]->getValue()
            ];
        }
        
        return $sources;
    }
    
    /**
     * Récupère les données en temps réel
     */
    public function getRealtimeData() {
        $request = new \Google\Service\AnalyticsData\RunRealtimeReportRequest([
            'property' => "properties/{$this->propertyId}",
            'dimensions' => [
                ['name' => 'country'],
                ['name' => 'pagePath']
            ],
            'metrics' => [
                ['name' => 'activeUsers']
            ],
            'limit' => 20
        ]);
        
        $response = $this->client->properties->runRealtimeReport($request);
        
        $realtime = [
            'total_active_users' => 0,
            'countries' => [],
            'current_pages' => []
        ];
        
        foreach ($response->getRows() as $row) {
            $country = $row->getDimensionValues()[0]->getValue();
            $page = $row->getDimensionValues()[1]->getValue();
            $users = (int)$row->getMetricValues()[0]->getValue();
            
            $realtime['total_active_users'] += $users;
            
            // Grouper par pays
            if (!isset($realtime['countries'][$country])) {
                $realtime['countries'][$country] = 0;
            }
            $realtime['countries'][$country] += $users;
            
            // Grouper par page
            if (!isset($realtime['current_pages'][$page])) {
                $realtime['current_pages'][$page] = 0;
            }
            $realtime['current_pages'][$page] += $users;
        }
        
        return $realtime;
    }
    
    /**
     * Formate la durée en format lisible
     */
    public static function formatDuration($seconds) {
        if ($seconds < 60) {
            return round($seconds) . 's';
        } elseif ($seconds < 3600) {
            return round($seconds / 60) . 'min';
        } else {
            return round($seconds / 3600, 1) . 'h';
        }
    }
    
    /**
     * Formate le taux de rebond
     */
    public static function formatBounceRate($rate) {
        return round($rate * 100, 1) . '%';
    }
    
    /**
     * Vérifie si les vraies données GA sont disponibles
     */
    public function isRealDataAvailable() {
        return file_exists($this->credentialsPath);
    }
}
?>
