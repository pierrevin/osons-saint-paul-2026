<?php
/**
 * Test des liens de la sidebar
 */

echo "<h1>🔗 Test des Liens Sidebar</h1>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;}</style>\n";

// Simuler le contexte d'inclusion depuis admin/pages/
$current_page = 'test';
$active_class = function($page) use ($current_page) {
    return $page === $current_page ? 'active' : '';
};

echo "<h2>🔍 Test des chemins</h2>\n";

// Test depuis admin/pages/ (contexte réel)
$test_paths = [
    'proposez.php' => '../../proposez.php',
    'equipe-formulaire.php' => '../../equipe-formulaire.php',
    'reponse-questionnaire.php' => 'reponse-questionnaire.php'
];

foreach ($test_paths as $name => $path) {
    $full_path = __DIR__ . '/admin/pages/' . $path;
    if (file_exists($full_path)) {
        echo "<p class='success'>✅ $name : $path → Fichier trouvé</p>\n";
    } else {
        echo "<p class='error'>❌ $name : $path → Fichier non trouvé</p>\n";
        echo "<p class='warning'>Chemin testé: $full_path</p>\n";
    }
}

echo "<h2>🔗 Liens générés</h2>\n";
echo "<ul>\n";
echo "<li><a href='../../proposez.php' target='_blank'>💡 Proposez</a></li>\n";
echo "<li><a href='../../equipe-formulaire.php' target='_blank'>👥 Formulaire Équipe</a></li>\n";
echo "<li><a href='reponse-questionnaire.php'>📊 Réponses Questionnaire</a></li>\n";
echo "</ul>\n";

echo "<h2>🧪 Test de navigation</h2>\n";
echo "<p>Cliquez sur les liens ci-dessus pour tester s'ils fonctionnent.</p>\n";

echo "<hr>\n";
echo "<p><em>Test effectué le " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
