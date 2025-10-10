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
        $this->propertyId = '508180392'; // Nouvelle propriété G-ME92TR3X97
        $this->credentialsPath = __DIR__ . '/../../credentials/ga-service-account.json';
        $this->initializeClient();
    }
    
    public function getPropertyId() {
        return $this->propertyId;
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
        
        // Initialiser le client Google Analytics (chargement conditionnel pour éviter les conflits)
        if (!class_exists('Google\Client')) {
            require_once $autoloadPath;
        }
        
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
        $request = new \Google\Service\AnalyticsData\RunReportRequest([
            'dateRanges' => [
                new \Google\Service\AnalyticsData\DateRange([
                    'startDate' => date('Y-m-d', strtotime("-{$days} days")),
                    'endDate' => date('Y-m-d')
                ])
            ],
            'dimensions' => [
                new \Google\Service\AnalyticsData\Dimension(['name' => 'date'])
            ],
            'metrics' => [
                new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers']),
                new \Google\Service\AnalyticsData\Metric(['name' => 'screenPageViews']),
                new \Google\Service\AnalyticsData\Metric(['name' => 'averageSessionDuration']),
                new \Google\Service\AnalyticsData\Metric(['name' => 'bounceRate'])
            ],
            'orderBys' => [
                new \Google\Service\AnalyticsData\OrderBy([
                    'dimension' => new \Google\Service\AnalyticsData\DimensionOrderBy(['dimensionName' => 'date']),
                    'desc' => true
                ])
            ]
        ]);
        
        $propertyName = "properties/{$this->propertyId}";
        $response = $this->client->properties->runReport($propertyName, $request);
        
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
        $request = new \Google\Service\AnalyticsData\RunReportRequest([
            'dateRanges' => [
                new \Google\Service\AnalyticsData\DateRange([
                    'startDate' => date('Y-m-d', strtotime('-30 days')),
                    'endDate' => date('Y-m-d')
                ])
            ],
            'dimensions' => [
                new \Google\Service\AnalyticsData\Dimension(['name' => 'pagePath']),
                new \Google\Service\AnalyticsData\Dimension(['name' => 'pageTitle'])
            ],
            'metrics' => [
                new \Google\Service\AnalyticsData\Metric(['name' => 'screenPageViews']),
                new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])
            ],
            'orderBys' => [
                new \Google\Service\AnalyticsData\OrderBy([
                    'metric' => new \Google\Service\AnalyticsData\MetricOrderBy(['metricName' => 'screenPageViews']),
                    'desc' => true
                ])
            ],
            'limit' => $limit
        ]);
        
        $propertyName = "properties/{$this->propertyId}";
        $response = $this->client->properties->runReport($propertyName, $request);
        
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
        $request = new \Google\Service\AnalyticsData\RunReportRequest([
            'dateRanges' => [
                new \Google\Service\AnalyticsData\DateRange([
                    'startDate' => date('Y-m-d', strtotime('-30 days')),
                    'endDate' => date('Y-m-d')
                ])
            ],
            'dimensions' => [
                new \Google\Service\AnalyticsData\Dimension(['name' => 'sessionSource']),
                new \Google\Service\AnalyticsData\Dimension(['name' => 'sessionMedium'])
            ],
            'metrics' => [
                new \Google\Service\AnalyticsData\Metric(['name' => 'sessions']),
                new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])
            ],
            'orderBys' => [
                new \Google\Service\AnalyticsData\OrderBy([
                    'metric' => new \Google\Service\AnalyticsData\MetricOrderBy(['metricName' => 'sessions']),
                    'desc' => true
                ])
            ],
            'limit' => $limit
        ]);
        
        $propertyName = "properties/{$this->propertyId}";
        $response = $this->client->properties->runReport($propertyName, $request);
        
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
            'dimensions' => [
                new \Google\Service\AnalyticsData\Dimension(['name' => 'country'])
            ],
            'metrics' => [
                new \Google\Service\AnalyticsData\Metric(['name' => 'activeUsers'])
            ],
            'limit' => 20
        ]);
        
        $propertyName = "properties/{$this->propertyId}";
        $response = $this->client->properties->runRealtimeReport($propertyName, $request);
        
        $realtime = [
            'total_active_users' => 0,
            'countries' => [],
            'current_pages' => []
        ];
        
        foreach ($response->getRows() as $row) {
            $country = $row->getDimensionValues()[0]->getValue();
            $users = (int)$row->getMetricValues()[0]->getValue();
            
            $realtime['total_active_users'] += $users;
            
            // Grouper par pays
            if (!isset($realtime['countries'][$country])) {
                $realtime['countries'][$country] = 0;
            }
            $realtime['countries'][$country] += $users;
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
     * Récupère les données de séries temporelles pour graphiques
     */
    public function getTimeSeriesData($days = 30) {
        $stats = $this->getGeneralStats($days);
        
        $timeSeries = [
            'labels' => [],
            'users' => [],
            'pageviews' => []
        ];
        
        foreach ($stats['daily_data'] as $day) {
            // Formater la date en français
            $date = new DateTime($day['date']);
            $timeSeries['labels'][] = $date->format('d M');
            $timeSeries['users'][] = $day['users'];
            $timeSeries['pageviews'][] = $day['pageviews'];
        }
        
        return $timeSeries;
    }
    
    /**
     * Vérifie si les vraies données GA sont disponibles
     */
    public function isRealDataAvailable() {
        return file_exists($this->credentialsPath);
    }
}
?>
