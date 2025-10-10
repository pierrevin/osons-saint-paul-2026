<?php
/**
 * Sidebar d'administration réutilisable
 * Inclut toutes les sections et pages d'administration
 */

// Charger les sections si elles ne sont pas déjà chargées
if (!isset($sections)) {
    $sections = [];
    $content = $content ?? [];
    
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

// Déterminer la page active
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$active_class = function($page) use ($current_page) {
    return $current_page === $page ? 'active' : '';
};
?>

<!-- Sidebar de navigation -->
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <img src="../../uploads/Osons1.png" alt="Logo Osons" />
        <h2>Administration</h2>
        <a href="../../index.php?utm_source=admin&utm_medium=internal&utm_campaign=admin_preview" target="_blank" class="view-site-btn">
            <i class="fas fa-external-link-alt"></i>
            Voir le site
        </a>
    </div>
    
    <ul class="sidebar-menu">
        <!-- Sections de contenu -->
        <?php foreach ($sections as $index => $section): ?>
            <?= $section->renderMenuItem(false) ?>
        <?php endforeach; ?>
        
        <!-- Section transitions -->
        <?php if (class_exists('TransitionsSection')): ?>
            <?php 
            $transitionsSection = new TransitionsSection($content ?? []);
            echo $transitionsSection->renderMenuItem($current_page === 'schema_admin_new');
            ?>
        <?php endif; ?>
        
        <!-- Menu séparateur -->
        <li class="menu-separator"><hr></li>
        
        <!-- Menu d'administration -->
        <li class="menu-item <?= $active_class('proposez') ?>">
            <a href="../../forms/proposition-citoyenne.php?utm_source=admin&utm_medium=internal&utm_campaign=admin_menu" target="_blank">
                <i class="fas fa-lightbulb"></i>
                <span>Proposez</span>
            </a>
        </li>
        
        <li class="menu-item <?= $active_class('equipe-formulaire') ?>">
            <a href="../../equipe-formulaire.php?utm_source=admin&utm_medium=internal&utm_campaign=admin_menu" target="_blank">
                <i class="fas fa-users"></i>
                <span>Formulaire Équipe</span>
            </a>
        </li>
        
        <li class="menu-item <?= $active_class('reponse-questionnaire') ?>">
            <a href="reponse-questionnaire.php">
                <i class="fas fa-chart-bar"></i>
                <span>Réponses Questionnaire</span>
            </a>
        </li>
        
        <li class="menu-separator"><hr></li>
        
        <!-- Outils marketing -->
        <li class="menu-item <?= $active_class('generateur-utm') ?>">
            <a href="generateur-utm.php">
                <i class="fas fa-link"></i>
                <span>Générateur UTM</span>
            </a>
        </li>
        
        <li class="menu-item <?= $active_class('documentation-utm') ?>">
            <a href="documentation-utm.php">
                <i class="fas fa-book"></i>
                <span>Guide UTM & URLs</span>
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            Déconnexion
        </a>
    </div>
</aside>
