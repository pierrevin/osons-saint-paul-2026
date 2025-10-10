<?php
// tools/backup_daily.php - Sauvegarde quotidienne avec rétention 3 jours
// Utilisation:
//  - CLI: php tools/backup_daily.php --run-now
//  - CLI (rotation seule): php tools/backup_daily.php --rotate-only
//  - HTTP: https://site/tools/backup_daily.php?token=SECRET

date_default_timezone_set('Europe/Paris');

// Détection racine projet
$rootPath = dirname(__DIR__);

// Charger config admin pour DATA_PATH si disponible
$adminConfig = $rootPath . '/admin/config.php';
if (file_exists($adminConfig)) {
    require_once $adminConfig;
} else {
    // Fallback minimal si le fichier n'est pas disponible (en théorie toujours présent)
    define('ROOT_PATH', $rootPath);
    define('DATA_PATH', ROOT_PATH . '/data');
}

// Sécurité HTTP via token
if (php_sapi_name() !== 'cli') {
    $expected = getenv('BACKUP_TOKEN') ?: null;
    $given = $_GET['token'] ?? '';
    if (!$expected || !hash_equals($expected, $given)) {
        http_response_code(403);
        echo 'Forbidden';
        exit;
    }
}

// Paramètres
$retentionDays = 3; // Rétention 3 jours
$backupsDir = $rootPath . '/backups';
$today = (new DateTime('today'))->format('Y-m-d');
$todayDir = $backupsDir . '/' . $today;
$zipName = $backupsDir . '/backup-' . $today . '.zip';

// Fichiers à sauvegarder (chemins absolus => relatifs dans archive)
$files = [
    'data/site_content.json',
    'data/propositions.json',
    'data/admin_log.json',
];

// Prépare répertoires
function ensure_dir($dir) {
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }
}

ensure_dir($backupsDir);
ensure_dir($todayDir);
ensure_dir(DATA_PATH . '/backups'); // journal des backups

// Rotation: supprimer dossiers/archives au-delà de $retentionDays
function rotate_backups($backupsDir, $retentionDays) {
    $entries = scandir($backupsDir);
    if ($entries === false) return;
    $keep = [];

    // Conserver N derniers jours
    $dates = [];
    foreach ($entries as $e) {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $e)) { $dates[] = $e; }
    }
    rsort($dates);
    $keep = array_slice($dates, 0, $retentionDays);

    foreach ($entries as $e) {
        // Dossiers datés
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $e)) {
            if (!in_array($e, $keep, true)) {
                $path = $backupsDir . '/' . $e;
                delete_tree($path);
            }
        }
        // Archives zip datées
        if (preg_match('/^backup-(\d{4}-\d{2}-\d{2})\.zip$/', $e, $m)) {
            if (!in_array($m[1], $keep, true)) {
                @unlink($backupsDir . '/' . $e);
            }
        }
    }
}

function delete_tree($dir) {
    if (!is_dir($dir)) return;
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($items as $item) {
        $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
    }
    rmdir($dir);
}

// Journal
$historyFile = DATA_PATH . '/backups/backup_history.json';
function append_history($historyFile, $entry) {
    $hist = [];
    if (file_exists($historyFile)) {
        $hist = json_decode(file_get_contents($historyFile), true) ?: [];
    }
    $hist[] = $entry;
    // garder 200 dernières lignes
    if (count($hist) > 200) { $hist = array_slice($hist, -200); }
    file_put_contents($historyFile, json_encode($hist, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Parse CLI args
$argvStr = php_sapi_name() === 'cli' ? implode(' ', $argv) : '';
if (strpos($argvStr, '--rotate-only') !== false) {
    rotate_backups($backupsDir, $retentionDays);
    echo "Rotation OK\n";
    exit(0);
}

// Sauvegarde
$copied = [];
foreach ($files as $rel) {
    $src = $rootPath . '/' . $rel;
    // Si DATA_PATH diffère de /data, prendre le fichier depuis DATA_PATH
    if (defined('DATA_PATH')) {
        $dataCandidate = DATA_PATH . '/' . basename($rel);
        if (file_exists($dataCandidate)) { $src = $dataCandidate; }
    }
    if (!file_exists($src)) { continue; }

    $dest = $todayDir . '/' . basename($rel);
    if (@copy($src, $dest)) {
        $copied[] = [ 'from' => $src, 'to' => $dest, 'size' => filesize($dest) ];
    }
}

// Créer archive zip
$zipOk = false;
if (!empty($copied)) {
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($copied as $c) {
                $zip->addFile($c['to'], basename($c['to']));
            }
            $zip->close();
            $zipOk = file_exists($zipName);
        }
    }
}

// Rotation après création
rotate_backups($backupsDir, $retentionDays);

// Log
$totalSize = array_sum(array_map(function($c){ return $c['size']; }, $copied));
append_history($historyFile, [
    'timestamp' => date('Y-m-d H:i:s'),
    'date' => $today,
    'files' => array_map(function($c){ return [ 'file' => basename($c['to']), 'size' => $c['size'] ]; }, $copied),
    'total_size' => $totalSize,
    'zip' => $zipOk ? basename($zipName) : null
]);

// Sortie
$msg = 'Backup terminé: ' . count($copied) . ' fichier(s), ' . round($totalSize/1024,1) . " KB";
if (php_sapi_name() === 'cli') {
    echo $msg . "\n";
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo $msg;
}

?>


