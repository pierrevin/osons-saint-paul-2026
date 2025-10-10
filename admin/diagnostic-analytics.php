<?php
/**
 * Script de diagnostic Google Analytics
 * 
 * Ce script teste méthodiquement chaque étape du chargement Google Analytics
 * pour identifier EXACTEMENT où et pourquoi ça crash sur le serveur OVH.
 * 
 * ⚠️ SUPPRIMEZ ce fichier après diagnostic !
 */

// Désactiver l'affichage des erreurs dans le HTML
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Logger dans un fichier
$log_file = __DIR__ . '/diagnostic-analytics-report.txt';
$report = [];

function logTest($test_name, $success, $message = '') {
    global $report;
    $status = $success ? '✅ SUCCÈS' : '❌ ÉCHEC';
    $report[] = "[$status] $test_name" . ($message ? " : $message" : "");
}

$report[] = "=== DIAGNOSTIC GOOGLE ANALYTICS ===";
$report[] = "Date : " . date('Y-m-d H:i:s');
$report[] = "Serveur : " . $_SERVER['HTTP_HOST'];
$report[] = "";

// TEST 1 : Fichier config.php
$report[] = "--- TEST 1 : Configuration ---";
try {
    require_once __DIR__ . '/config.php';
    logTest("Chargement config.php", true);
    
    if (defined('DATA_PATH')) {
        logTest("DATA_PATH défini", true, DATA_PATH);
    } else {
        logTest("DATA_PATH défini", false, "Non défini");
    }
} catch (Exception $e) {
    logTest("Chargement config.php", false, $e->getMessage());
} catch (Error $e) {
    logTest("Chargement config.php", false, "ERREUR FATALE: " . $e->getMessage());
}

$report[] = "";

// TEST 2 : Fichier credentials
$report[] = "--- TEST 2 : Fichier credentials ---";
$credentials_path = __DIR__ . '/../credentials/ga-service-account.json';

if (file_exists($credentials_path)) {
    logTest("Fichier credentials existe", true, $credentials_path);
    
    // Lire le contenu
    $credentials_content = file_get_contents($credentials_path);
    logTest("Lecture du fichier", true, strlen($credentials_content) . " octets");
    
    // Vérifier le JSON
    $credentials = json_decode($credentials_content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        logTest("JSON valide", true);
        
        // Vérifier les clés
        $required_keys = ['type', 'private_key', 'client_email'];
        $missing_keys = [];
        foreach ($required_keys as $key) {
            if (!isset($credentials[$key])) {
                $missing_keys[] = $key;
            }
        }
        
        if (empty($missing_keys)) {
            logTest("Clés requises présentes", true, implode(', ', $required_keys));
        } else {
            logTest("Clés requises présentes", false, "Manquantes : " . implode(', ', $missing_keys));
        }
        
    } else {
        logTest("JSON valide", false, json_last_error_msg());
    }
} else {
    logTest("Fichier credentials existe", false, $credentials_path);
}

$report[] = "";

// TEST 3 : Autoloader Composer
$report[] = "--- TEST 3 : Autoloader Composer ---";
$autoload_path = __DIR__ . '/../vendor/autoload.php';

if (file_exists($autoload_path)) {
    logTest("Fichier autoload.php existe", true, $autoload_path);
    
    try {
        require_once $autoload_path;
        logTest("Chargement autoload.php", true);
        
        // Vérifier les classes Google
        if (class_exists('Google\Client')) {
            logTest("Classe Google\\Client disponible", true);
        } else {
            logTest("Classe Google\\Client disponible", false);
        }
        
        if (class_exists('Google\Service\AnalyticsData')) {
            logTest("Classe Google\\Service\\AnalyticsData disponible", true);
        } else {
            logTest("Classe Google\\Service\\AnalyticsData disponible", false);
        }
        
    } catch (Exception $e) {
        logTest("Chargement autoload.php", false, $e->getMessage());
    } catch (Error $e) {
        logTest("Chargement autoload.php", false, "ERREUR FATALE: " . $e->getMessage());
    }
} else {
    logTest("Fichier autoload.php existe", false, $autoload_path);
}

$report[] = "";

// TEST 4 : Chargement GoogleAnalyticsReal.php
$report[] = "--- TEST 4 : GoogleAnalyticsReal.php ---";
$ga_real_path = __DIR__ . '/includes/GoogleAnalyticsReal.php';

if (file_exists($ga_real_path)) {
    logTest("Fichier GoogleAnalyticsReal.php existe", true);
    
    try {
        require_once $ga_real_path;
        logTest("Chargement GoogleAnalyticsReal.php", true);
        
        // Vérifier que la classe est définie
        if (class_exists('GoogleAnalyticsReal')) {
            logTest("Classe GoogleAnalyticsReal définie", true);
            
            // TEST 5 : Instanciation GoogleAnalyticsReal
            $report[] = "";
            $report[] = "--- TEST 5 : Instanciation GoogleAnalyticsReal ---";
            
            try {
                $analytics = new GoogleAnalyticsReal();
                logTest("new GoogleAnalyticsReal()", true);
                
                // Tester getInitializationError
                $init_error = $analytics->getInitializationError();
                if ($init_error) {
                    logTest("Initialisation Google Analytics", false, $init_error);
                } else {
                    logTest("Initialisation Google Analytics", true, "Aucune erreur");
                }
                
            } catch (Exception $e) {
                logTest("new GoogleAnalyticsReal()", false, $e->getMessage());
            } catch (Error $e) {
                logTest("new GoogleAnalyticsReal()", false, "ERREUR FATALE: " . $e->getMessage());
            }
            
        } else {
            logTest("Classe GoogleAnalyticsReal définie", false);
        }
        
    } catch (Exception $e) {
        logTest("Chargement GoogleAnalyticsReal.php", false, $e->getMessage());
    } catch (Error $e) {
        logTest("Chargement GoogleAnalyticsReal.php", false, "ERREUR FATALE: " . $e->getMessage());
    }
} else {
    logTest("Fichier GoogleAnalyticsReal.php existe", false, $ga_real_path);
}

$report[] = "";

// TEST 6 : DashboardSection
$report[] = "--- TEST 6 : DashboardSection ---";
try {
    require_once __DIR__ . '/includes/AdminSection.php';
    require_once __DIR__ . '/includes/GoogleAnalyticsSimple.php';
    require_once __DIR__ . '/pages/sections/dashboard.php';
    
    logTest("Chargement des classes Dashboard", true);
    
    $content = [];
    try {
        $dashboard = new DashboardSection($content);
        logTest("new DashboardSection()", true);
        
        // Tester renderForm
        try {
            $html = $dashboard->renderForm();
            logTest("DashboardSection->renderForm()", true, strlen($html) . " caractères générés");
            
            // Vérifier si le HTML contient <script>
            if (strpos($html, '<script>') !== false) {
                logTest("Génération JavaScript", true);
            } else {
                logTest("Génération JavaScript", false, "Aucun <script> trouvé");
            }
            
        } catch (Exception $e) {
            logTest("DashboardSection->renderForm()", false, $e->getMessage());
        } catch (Error $e) {
            logTest("DashboardSection->renderForm()", false, "ERREUR FATALE: " . $e->getMessage());
        }
        
    } catch (Exception $e) {
        logTest("new DashboardSection()", false, $e->getMessage());
    } catch (Error $e) {
        logTest("new DashboardSection()", false, "ERREUR FATALE: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    logTest("Chargement des classes Dashboard", false, $e->getMessage());
} catch (Error $e) {
    logTest("Chargement des classes Dashboard", false, "ERREUR FATALE: " . $e->getMessage());
}

$report[] = "";
$report[] = "=== FIN DU DIAGNOSTIC ===";
$report[] = "";

// Écrire le rapport
$report_content = implode("\n", $report);
file_put_contents($log_file, $report_content);

// Afficher le rapport
echo '<!DOCTYPE html>';
echo '<html lang="fr">';
echo '<head>';
echo '<meta charset="UTF-8">';
echo '<title>Diagnostic Google Analytics</title>';
echo '<style>';
echo 'body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }';
echo 'pre { background: #2d2d2d; padding: 20px; border-radius: 5px; overflow-x: auto; }';
echo '.success { color: #4ec9b0; }';
echo '.error { color: #f48771; }';
echo 'h1 { color: #569cd6; }';
echo '</style>';
echo '</head>';
echo '<body>';
echo '<h1>📊 Diagnostic Google Analytics</h1>';
echo '<pre>';
foreach ($report as $line) {
    if (strpos($line, '✅') !== false) {
        echo '<span class="success">' . htmlspecialchars($line) . '</span>' . "\n";
    } elseif (strpos($line, '❌') !== false) {
        echo '<span class="error">' . htmlspecialchars($line) . '</span>' . "\n";
    } else {
        echo htmlspecialchars($line) . "\n";
    }
}
echo '</pre>';
echo '<p style="color: #ce9178;">Rapport sauvegardé dans : ' . htmlspecialchars($log_file) . '</p>';
echo '<p style="color: #f48771;"><strong>⚠️ SUPPRIMEZ ce fichier après lecture du rapport !</strong></p>';
echo '</body>';
echo '</html>';
?>
