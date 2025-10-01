<?php
/**
 * Estimateur de compression d'images
 * Répond à la question : Combien pèsera mon image après traitement ?
 */

echo "=== ESTIMATEUR DE COMPRESSION ===\n\n";

// Facteurs de compression moyens observés
$compression_factors = [
    'jpeg_to_webp' => [
        'quality_90' => 0.75,  // -25%
        'quality_85' => 0.65,  // -35%
        'quality_80' => 0.55,  // -45%
        'quality_75' => 0.50,  // -50%
    ],
    'png_to_webp' => [
        'quality_90' => 0.45,  // -55%
        'quality_85' => 0.35,  // -65%
        'quality_80' => 0.30,  // -70%
        'quality_75' => 0.25,  // -75%
    ]
];

function formatBytes($bytes) {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}

function estimate($original_kb, $format, $quality) {
    global $compression_factors;
    
    $original_bytes = $original_kb * 1024;
    $factor = $compression_factors[$format]['quality_' . $quality] ?? 0.65;
    $final_bytes = $original_bytes * $factor;
    $gain_percent = round((1 - $factor) * 100, 1);
    
    return [
        'original' => formatBytes($original_bytes),
        'final' => formatBytes($final_bytes),
        'gain_percent' => $gain_percent,
        'factor' => $factor
    ];
}

// === CAS D'USAGE ===

echo "📸 IMAGE HERO (JPEG 1MB → WebP 85%)\n";
$result = estimate(1024, 'jpeg_to_webp', 85);
echo "   Original : " . $result['original'] . "\n";
echo "   Final    : " . $result['final'] . "\n";
echo "   Gain     : " . $result['gain_percent'] . "%\n\n";

echo "📸 IMAGE HERO (PNG 1MB → WebP 85%)\n";
$result = estimate(1024, 'png_to_webp', 85);
echo "   Original : " . $result['original'] . "\n";
echo "   Final    : " . $result['final'] . "\n";
echo "   Gain     : " . $result['gain_percent'] . "%\n\n";

echo "👤 PHOTO MEMBRE (JPEG 500KB → WebP 90%)\n";
$result = estimate(500, 'jpeg_to_webp', 90);
echo "   Original : " . $result['original'] . "\n";
echo "   Final    : " . $result['final'] . "\n";
echo "   Gain     : " . $result['gain_percent'] . "%\n\n";

echo "🎨 CITATION (JPEG 800KB → WebP 85%)\n";
$result = estimate(800, 'jpeg_to_webp', 85);
echo "   Original : " . $result['original'] . "\n";
echo "   Final    : " . $result['final'] . "\n";
echo "   Gain     : " . $result['gain_percent'] . "%\n\n";

echo "=== TABLEAU RÉCAPITULATIF ===\n\n";

$test_cases = [
    ['500 KB', 500, 'jpeg_to_webp', 85],
    ['1 MB', 1024, 'jpeg_to_webp', 85],
    ['2 MB', 2048, 'jpeg_to_webp', 85],
    ['5 MB', 5120, 'jpeg_to_webp', 85],
];

echo sprintf("%-12s | %-12s | %-12s | %-8s\n", "Original", "Final WebP", "Gain", "Type");
echo str_repeat("-", 55) . "\n";

foreach ($test_cases as $case) {
    [$label, $kb, $format, $quality] = $case;
    $est = estimate($kb, $format, $quality);
    echo sprintf("%-12s | %-12s | %-12s | %-8s\n", 
        $label, 
        $est['final'], 
        $est['gain_percent'] . '%',
        'JPEG'
    );
}

echo "\n💡 Note : Ces estimations sont basées sur des images photographiques standards.\n";
echo "   Les résultats réels varient selon la complexité de l'image.\n\n";

echo "=== VOTRE CONFIGURATION ===\n\n";
echo "Preset Hero :\n";
echo "  • Dimensions max : 1920x1080\n";
echo "  • Qualité WebP : 85%\n";
echo "  • Format sortie : WebP\n";
echo "  • Gain moyen : 65%\n\n";

echo "Pour 1 MB JPEG → ~350 KB WebP ⚡\n\n";
?>

