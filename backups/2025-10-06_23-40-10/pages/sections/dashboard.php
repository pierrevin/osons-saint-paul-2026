<?php
/**
 * Section Tableau de Bord
 * Vue d'ensemble avec cartes de navigation rapide
 */

class DashboardSection extends AdminSection {
    
    public function __construct($content) {
        parent::__construct('dashboard', 'Tableau de Bord', 'fas fa-tachometer-alt', $content);
    }
    
    public function renderForm() {
        // Calculer les statistiques
        $programme_count = count($this->content['programme']['proposals'] ?? []);
        $equipe_count = count($this->content['equipe']['members'] ?? []);
        $rendez_vous_count = count($this->content['rendez_vous']['events'] ?? []);
        
        $html = '<div class="section-header">';
        $html .= '<h3><i class="fas fa-tachometer-alt"></i> Tableau de Bord</h3>';
        $html .= '<p>Vue d\'ensemble et accès rapide aux sections</p>';
        $html .= '</div>';
        
        // Statistiques générales
        $html .= '<div class="stats-overview">';
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-icon"><i class="fas fa-list"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . $programme_count . '</span>';
        $html .= '<span class="stat-label">Propositions</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-icon"><i class="fas fa-users"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . $equipe_count . '</span>';
        $html .= '<span class="stat-label">Membres</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-icon"><i class="fas fa-calendar"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . $rendez_vous_count . '</span>';
        $html .= '<span class="stat-label">Événements</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Cartes de navigation rapide
        $html .= '<div class="dashboard-cards">';
        $html .= '<h4>Navigation Rapide</h4>';
        $html .= '<div class="cards-grid">';
        
        // Carte Programme
        $html .= '<div class="dashboard-card" onclick="navigateToSection(\'programme\')">';
        $html .= '<div class="card-header">';
        $html .= '<i class="fas fa-list"></i>';
        $html .= '<h5>Programme</h5>';
        $html .= '</div>';
        $html .= '<div class="card-content">';
        $html .= '<p>Gérer les propositions citoyennes</p>';
        $html .= '<div class="card-stats">' . $programme_count . ' propositions</div>';
        $html .= '</div>';
        $html .= '<div class="card-actions">';
        $html .= '<button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); openProposalModal(\'create\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); navigateToSection(\'programme\')">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Carte Équipe
        $html .= '<div class="dashboard-card" onclick="navigateToSection(\'equipe\')">';
        $html .= '<div class="card-header">';
        $html .= '<i class="fas fa-users"></i>';
        $html .= '<h5>Équipe</h5>';
        $html .= '</div>';
        $html .= '<div class="card-content">';
        $html .= '<p>Gérer les membres de l\'équipe</p>';
        $html .= '<div class="card-stats">' . $equipe_count . ' membres</div>';
        $html .= '</div>';
        $html .= '<div class="card-actions">';
        $html .= '<button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); AdminModal.open(\'addMemberModal\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); navigateToSection(\'equipe\')">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Carte Rendez-vous
        $html .= '<div class="dashboard-card" onclick="navigateToSection(\'rendez_vous\')">';
        $html .= '<div class="card-header">';
        $html .= '<i class="fas fa-calendar"></i>';
        $html .= '<h5>Rendez-vous</h5>';
        $html .= '</div>';
        $html .= '<div class="card-content">';
        $html .= '<p>Gérer les événements et rendez-vous</p>';
        $html .= '<div class="card-stats">' . $rendez_vous_count . ' événements</div>';
        $html .= '</div>';
        $html .= '<div class="card-actions">';
        $html .= '<button class="btn btn-sm btn-primary" onclick="event.stopPropagation(); AdminModal.open(\'addEventModal\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); navigateToSection(\'rendez_vous\')">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Carte Charte
        $html .= '<div class="dashboard-card" onclick="navigateToSection(\'charte\')">';
        $html .= '<div class="card-header">';
        $html .= '<i class="fas fa-file-contract"></i>';
        $html .= '<h5>Charte</h5>';
        $html .= '</div>';
        $html .= '<div class="card-content">';
        $html .= '<p>Modifier la charte et les valeurs</p>';
        $html .= '<div class="card-stats">Contenu statique</div>';
        $html .= '</div>';
        $html .= '<div class="card-actions">';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="event.stopPropagation(); navigateToSection(\'charte\')">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        
        $html .= '</div>'; // cards-grid
        $html .= '</div>'; // dashboard-cards
        
        // Actions rapides système
        $html .= '<div class="system-actions">';
        $html .= '<h4>Actions Système</h4>';
        $html .= '<div class="actions-grid">';
        
        $html .= '<div class="action-card" onclick="navigateToSection(\'gestion_utilisateurs\')">';
        $html .= '<i class="fas fa-users-cog"></i>';
        $html .= '<span>Gestion Utilisateurs</span>';
        $html .= '</div>';
        
        $html .= '<div class="action-card" onclick="navigateToSection(\'logs_securite\')">';
        $html .= '<i class="fas fa-shield-alt"></i>';
        $html .= '<span>Logs de Sécurité</span>';
        $html .= '</div>';
        
        $html .= '</div>'; // actions-grid
        $html .= '</div>'; // system-actions
        
        return $html;
    }
    
    public function processFormData($data) {
        // Cette section ne traite pas de données de formulaire
        return ['success' => true, 'message' => 'Tableau de bord chargé'];
    }
    
    public function handleSubmission($data) {
        // Pas de soumission de formulaire pour cette section
        return ['success' => true, 'message' => 'Tableau de bord affiché'];
    }
}
