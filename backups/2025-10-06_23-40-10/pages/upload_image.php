<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/image_processor.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Aucune image reçue');
    }

    $preset = $_POST['preset'] ?? 'standard';

    $uploadsDir = realpath(__DIR__ . '/../../uploads');
    if ($uploadsDir === false) {
        $uploadsDir = __DIR__ . '/../../uploads';
    }
    if (!is_dir($uploadsDir)) {
        @mkdir($uploadsDir, 0755, true);
    }

    $processor = new ImageProcessor();
    $result = $processor->processWithPreset($_FILES['image'], $uploadsDir, $preset, $preset);

    if (!$result['success']) {
        throw new Exception($result['error'] ?? 'Échec du traitement');
    }

    // Construire chemin relatif web
    $relativePath = 'uploads/' . $result['filename'];

    echo json_encode([
        'success' => true,
        'path' => $relativePath,
        'preset' => $preset,
        'details' => [
            'dimensions' => $result['dimensions'] ?? null,
            'compression_ratio' => $result['compression_ratio'] ?? null,
            'format' => $result['format'] ?? null,
            'size' => $result['size'] ?? null,
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
?>


