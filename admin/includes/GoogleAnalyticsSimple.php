<?php
/**
 * Service Google Analytics simplifié pour le dashboard admin
 * Version qui fonctionne sans credentials (données simulées)
 */

class GoogleAnalyticsSimple {
    private $propertyId;
    
    public function __construct() {
        $this->propertyId = 'G-B544VTFXWF';
    }
    
    /**
     * Récupère les statistiques générales (simulées)
     */
    public function getGeneralStats($days = 30) {
        // Données simulées basées sur des patterns réalistes
        $baseUsers = rand(45, 85);
        $basePageviews = $baseUsers * rand(2, 4);
        
        $stats = [
            'total_users' => $baseUsers,
            'total_pageviews' => $basePageviews,
            'avg_session_duration' => rand(120, 300), // 2-5 minutes
            'bounce_rate' => rand(35, 65) / 100, // 35-65%
            'daily_data' => []
        ];
        
        // Générer des données quotidiennes
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dailyUsers = $baseUsers + rand(-15, 15);
            $dailyPageviews = $dailyUsers * rand(2, 4);
            
            $stats['daily_data'][] = [
                'date' => $date,
                'users' => max(0, $dailyUsers),
                'pageviews' => max(0, $dailyPageviews),
                'session_duration' => rand(100, 350),
                'bounce_rate' => rand(30, 70) / 100
            ];
        }
        
        return $stats;
    }
    
    /**
     * Récupère les pages les plus visitées (simulées)
     */
    public function getTopPages($limit = 10) {
        $pages = [
            ['path' => '/', 'title' => 'Accueil - Osons Saint-Paul', 'pageviews' => rand(25, 45), 'users' => rand(20, 35)],
            ['path' => '/#programme', 'title' => 'Programme', 'pageviews' => rand(15, 30), 'users' => rand(12, 25)],
            ['path' => '/#equipe', 'title' => 'Équipe', 'pageviews' => rand(10, 20), 'users' => rand(8, 18)],
            ['path' => '/#rendez-vous', 'title' => 'Rendez-vous', 'pageviews' => rand(8, 18), 'users' => rand(6, 15)],
            ['path' => '/#charte', 'title' => 'Charte', 'pageviews' => rand(5, 12), 'users' => rand(4, 10)],
            ['path' => '/proposez.php', 'title' => 'Proposer une idée', 'pageviews' => rand(3, 8), 'users' => rand(2, 6)],
            ['path' => '/mentions-legales.php', 'title' => 'Mentions légales', 'pageviews' => rand(2, 5), 'users' => rand(1, 4)],
            ['path' => '/gestion-cookies.php', 'title' => 'Gestion des cookies', 'pageviews' => rand(1, 3), 'users' => rand(1, 2)]
        ];
        
        // Trier par pageviews
        usort($pages, function($a, $b) {
            return $b['pageviews'] - $a['pageviews'];
        });
        
        return array_slice($pages, 0, $limit);
    }
    
    /**
     * Récupère les sources de trafic (simulées)
     */
    public function getTrafficSources($limit = 10) {
        $sources = [
            ['source' => 'google', 'medium' => 'organic', 'sessions' => rand(20, 40), 'users' => rand(18, 35)],
            ['source' => 'direct', 'medium' => '(none)', 'sessions' => rand(15, 30), 'users' => rand(12, 25)],
            ['source' => 'facebook.com', 'medium' => 'referral', 'sessions' => rand(5, 15), 'users' => rand(4, 12)],
            ['source' => 'instagram.com', 'medium' => 'referral', 'sessions' => rand(3, 10), 'users' => rand(2, 8)],
            ['source' => 'bing', 'medium' => 'organic', 'sessions' => rand(2, 8), 'users' => rand(1, 6)],
            ['source' => 'youtube.com', 'medium' => 'referral', 'sessions' => rand(1, 5), 'users' => rand(1, 4)]
        ];
        
        // Trier par sessions
        usort($sources, function($a, $b) {
            return $b['sessions'] - $a['sessions'];
        });
        
        return array_slice($sources, 0, $limit);
    }
    
    /**
     * Récupère les données en temps réel (simulées)
     */
    public function getRealtimeData() {
        $activeUsers = rand(1, 8);
        
        $countries = [
            'France' => rand(1, $activeUsers),
            'Belgique' => rand(0, max(0, $activeUsers - 2)),
            'Suisse' => rand(0, max(0, $activeUsers - 3))
        ];
        
        $currentPages = [
            '/' => rand(1, $activeUsers),
            '/#programme' => rand(0, max(0, $activeUsers - 1)),
            '/#equipe' => rand(0, max(0, $activeUsers - 2))
        ];
        
        return [
            'total_active_users' => $activeUsers,
            'countries' => array_filter($countries, function($count) { return $count > 0; }),
            'current_pages' => array_filter($currentPages, function($count) { return $count > 0; })
        ];
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
        return file_exists(__DIR__ . '/../../credentials/ga-service-account.json');
    }
}
?>
