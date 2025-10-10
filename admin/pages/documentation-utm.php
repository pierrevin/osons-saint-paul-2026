<?php
session_start();
require_once __DIR__ . '/../config.php';

// Vérifier l'authentification
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Charger le contenu du site pour les sections
$data_path = DATA_PATH . '/site_content.json';
$content = file_exists($data_path) ? json_decode(file_get_contents($data_path), true) : [];

// Charger les sections si elles ne sont pas déjà chargées
if (!isset($sections)) {
    $sections = [];
    
    // Charger les sections si les classes sont disponibles
    if (class_exists('DashboardSection')) {
        $sections[] = new DashboardSection($content);
    }
    if (class_exists('HeroSection')) {
        $sections[] = new HeroSection($content);
    }
    if (class_exists('ProgrammeSection')) {
        $programme_count = count($content['programme']['proposals'] ?? []);
        $sections[] = new ProgrammeSection($content, $programme_count);
    }
    if (class_exists('CitationsSection')) {
        $sections[] = new CitationsSection($content);
    }
    if (class_exists('EquipeSection')) {
        $sections[] = new EquipeSection($content);
    }
    if (class_exists('RendezVousSection')) {
        $sections[] = new RendezVousSection($content);
    }
    if (class_exists('CharteSection')) {
        $sections[] = new CharteSection($content);
    }
    if (class_exists('ContactSection')) {
        $sections[] = new ContactSection($content);
    }
    if (class_exists('MediathequeSection')) {
        $sections[] = new MediathequeSection($content);
    }
    if (class_exists('GestionUtilisateursSection')) {
        $sections[] = new GestionUtilisateursSection($content);
    }
    if (class_exists('LogsSecuriteSection')) {
        $sections[] = new LogsSecuriteSection($content);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation UTM | Admin Osons Saint-Paul</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .docs-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        .docs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .doc-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid #ec654f;
        }
        
        .doc-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .doc-card.green {
            border-left-color: #65ae99;
        }
        
        .doc-card.blue {
            border-left-color: #4e9eb0;
        }
        
        .doc-card h2 {
            margin: 0 0 1rem 0;
            color: #004a6d;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .doc-card h2 i {
            color: #ec654f;
        }
        
        .doc-card.green h2 i {
            color: #65ae99;
        }
        
        .doc-card.blue h2 i {
            color: #4e9eb0;
        }
        
        .doc-card p {
            color: #6c757d;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        
        .doc-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: #ec654f;
            color: white;
        }
        
        .btn-primary:hover {
            background: #d94d36;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #65ae99;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #529582;
        }
        
        .btn-outline {
            background: white;
            color: #004a6d;
            border: 2px solid #004a6d;
        }
        
        .btn-outline:hover {
            background: #004a6d;
            color: white;
        }
        
        .quick-links {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }
        
        .quick-links h3 {
            margin: 0 0 1.5rem 0;
            color: #004a6d;
            font-size: 1.3rem;
        }
        
        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .quick-link {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            color: #004a6d;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s;
            border-left: 3px solid #ec654f;
        }
        
        .quick-link:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .quick-link i {
            font-size: 1.5rem;
            color: #ec654f;
        }
        
        .quick-link .link-text {
            flex: 1;
        }
        
        .quick-link .link-title {
            font-weight: 600;
            display: block;
            margin-bottom: 0.25rem;
        }
        
        .quick-link .link-desc {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .info-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .info-banner h2 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
        }
        
        .info-banner p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .files-list {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .files-list h3 {
            margin: 0 0 1.5rem 0;
            color: #004a6d;
            font-size: 1.3rem;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }
        
        .file-item:hover {
            background: #e9ecef;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .file-icon {
            font-size: 2rem;
            color: #ec654f;
        }
        
        .file-details h4 {
            margin: 0 0 0.25rem 0;
            color: #004a6d;
            font-size: 1rem;
        }
        
        .file-details p {
            margin: 0;
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .file-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: white;
            border: 2px solid #dee2e6;
            color: #004a6d;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-icon:hover {
            background: #004a6d;
            color: white;
            border-color: #004a6d;
        }
    </style>
</head>
<body class="admin-body">
    
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="admin-header">
            <h1><i class="fas fa-book"></i> Documentation UTM & Tracking</h1>
            <p class="admin-subtitle">Guides complets pour mesurer vos campagnes</p>
        </div>
        
        <div class="docs-container">
            
            <div class="info-banner">
                <h2>📊 Mesurez l'impact de vos actions</h2>
                <p>Utilisez les paramètres UTM pour tracker précisément vos QR codes, affiches, newsletters et posts sociaux</p>
            </div>
            
            <div class="quick-links">
                <h3><i class="fas fa-bolt"></i> Accès rapides</h3>
                <div class="quick-links-grid">
                    <a href="generateur-utm.php" class="quick-link">
                        <i class="fas fa-magic"></i>
                        <div class="link-text">
                            <span class="link-title">Générateur UTM</span>
                            <span class="link-desc">Créer des URLs trackées</span>
                        </div>
                    </a>
                    
                    <a href="https://analytics.google.com" target="_blank" class="quick-link">
                        <i class="fas fa-chart-line"></i>
                        <div class="link-text">
                            <span class="link-title">Google Analytics</span>
                            <span class="link-desc">Voir vos statistiques</span>
                        </div>
                    </a>
                    
                    <a href="https://search.google.com/search-console" target="_blank" class="quick-link">
                        <i class="fas fa-search"></i>
                        <div class="link-text">
                            <span class="link-title">Search Console</span>
                            <span class="link-desc">Indexation Google</span>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="docs-grid">
                <div class="doc-card">
                    <h2><i class="fas fa-book-open"></i> Guide Tracking UTM</h2>
                    <p>Guide complet pour comprendre et utiliser les paramètres UTM. Nomenclature, exemples, bonnes pratiques et analyse dans Google Analytics.</p>
                    <div class="doc-actions">
                        <a href="#guide-utm" class="btn btn-primary" onclick="showContent('guide-utm'); return false;">
                            <i class="fas fa-eye"></i>
                            Lire le guide
                        </a>
                        <a href="../../GUIDE_TRACKING_UTM.md" download class="btn btn-outline">
                            <i class="fas fa-download"></i>
                            Télécharger
                        </a>
                    </div>
                </div>
                
                <div class="doc-card green">
                    <h2><i class="fas fa-qrcode"></i> URLs QR Codes</h2>
                    <p>Liste complète des URLs pré-générées pour vos QR codes : cartes postales, affiches, flyers, badges équipe, et plus encore.</p>
                    <div class="doc-actions">
                        <a href="#urls-qr" class="btn btn-secondary" onclick="showContent('urls-qr'); return false;">
                            <i class="fas fa-eye"></i>
                            Voir les URLs
                        </a>
                        <a href="../../URLS_QR_CODES.md" download class="btn btn-outline">
                            <i class="fas fa-download"></i>
                            Télécharger
                        </a>
                    </div>
                </div>
                
                <div class="doc-card blue">
                    <h2><i class="fas fa-graduation-cap"></i> Guide SEO</h2>
                    <p>Documentation complète des optimisations SEO implémentées : sitemap, Open Graph, Schema.org, et performances.</p>
                    <div class="doc-actions">
                        <a href="../../SEO_OPTIMISATIONS.md" target="_blank" class="btn btn-outline">
                            <i class="fas fa-external-link-alt"></i>
                            Ouvrir le guide
                        </a>
                        <a href="../../SEO_OPTIMISATIONS.md" download class="btn btn-outline">
                            <i class="fas fa-download"></i>
                            Télécharger
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="files-list">
                <h3><i class="fas fa-folder-open"></i> Fichiers de documentation</h3>
                
                <div class="file-item">
                    <div class="file-info">
                        <i class="fas fa-file-alt file-icon"></i>
                        <div class="file-details">
                            <h4>GUIDE_TRACKING_UTM.md</h4>
                            <p>Guide complet du tracking UTM (50+ pages)</p>
                        </div>
                    </div>
                    <div class="file-actions">
                        <a href="../../GUIDE_TRACKING_UTM.md" target="_blank" class="btn-icon" title="Ouvrir">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="../../GUIDE_TRACKING_UTM.md" download class="btn-icon" title="Télécharger">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
                
                <div class="file-item">
                    <div class="file-info">
                        <i class="fas fa-link file-icon"></i>
                        <div class="file-details">
                            <h4>URLS_QR_CODES.md</h4>
                            <p>URLs trackées pré-générées pour tous vos supports</p>
                        </div>
                    </div>
                    <div class="file-actions">
                        <a href="../../URLS_QR_CODES.md" target="_blank" class="btn-icon" title="Ouvrir">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="../../URLS_QR_CODES.md" download class="btn-icon" title="Télécharger">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
                
                <div class="file-item">
                    <div class="file-info">
                        <i class="fas fa-search file-icon"></i>
                        <div class="file-details">
                            <h4>SEO_OPTIMISATIONS.md</h4>
                            <p>Récapitulatif de toutes les optimisations SEO</p>
                        </div>
                    </div>
                    <div class="file-actions">
                        <a href="../../SEO_OPTIMISATIONS.md" target="_blank" class="btn-icon" title="Ouvrir">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="../../SEO_OPTIMISATIONS.md" download class="btn-icon" title="Télécharger">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
                
                <div class="file-item">
                    <div class="file-info">
                        <i class="fas fa-image file-icon"></i>
                        <div class="file-details">
                            <h4>GUIDE_IMAGE_OPEN_GRAPH.md</h4>
                            <p>Guide pour créer des images de partage optimales</p>
                        </div>
                    </div>
                    <div class="file-actions">
                        <a href="../../GUIDE_IMAGE_OPEN_GRAPH.md" target="_blank" class="btn-icon" title="Ouvrir">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="../../GUIDE_IMAGE_OPEN_GRAPH.md" download class="btn-icon" title="Télécharger">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
    </main>
    
    <script>
        function showContent(section) {
            // Cette fonction pourrait afficher le contenu du guide
            // Pour l'instant, on ouvre dans un nouvel onglet
            if (section === 'guide-utm') {
                window.open('../../GUIDE_TRACKING_UTM.md', '_blank');
            } else if (section === 'urls-qr') {
                window.open('../../URLS_QR_CODES.md', '_blank');
            }
        }
    </script>
</body>
</html>

