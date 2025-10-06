<?php
require_once __DIR__ . '/../../includes/AdminSection.php';

/**
 * Section Équipe - Gestion des membres de l'équipe
 */
class EquipeSection extends AdminSection {
    
    public function __construct($content = null) {
        parent::__construct('equipe', 'Équipe', 'fas fa-users', $content);
    }
    
    protected function renderForm() {
        $equipeContent = $this->content['equipe'] ?? [];
        
        $html = '<div class="block-edit-form">';
        $html .= '<form method="POST" action="" id="equipe-form-element" class="admin-form">';
        $html .= '<input type="hidden" name="csrf_token" value="' . $this->csrfToken . '">';
        $html .= '<input type="hidden" name="action" value="update_equipe">';
        
        // Titre principal
        $html .= $this->renderFormField(
            'text',
            'title',
            'Titre principal',
            $equipeContent['title'] ?? 'Notre Équipe',
            true,
            ['onchange' => 'markFormChanged(\'equipe\')']
        );
        
        // Sous-titre
        $html .= $this->renderFormField(
            'text',
            'subtitle',
            'Sous-titre',
            $equipeContent['subtitle'] ?? 'Des habitants engagés pour Saint-Paul',
            false,
            ['onchange' => 'markFormChanged(\'equipe\')']
        );
        
        // Boutons d'action
        $html .= '<div class="form-actions" style="margin-top: 1rem; display: flex; gap: .5rem;">';
        $html .= '<button type="submit" form="equipe-form-element" class="btn btn-primary">Sauvegarder</button>';
        $html .= '<button type="button" class="btn btn-secondary" onclick="window.location.reload()">Annuler</button>';
        $html .= '</div>';
        
        $html .= '</form>';
        $html .= '</div>';
        
        // Section des membres
        $html .= $this->renderMembersSection($equipeContent);
        
        return $html;
    }
    
    protected function renderMembersSection($equipeContent) {
        $members = $equipeContent['members'] ?? [];
        
        $html = '<div class="members-section">';
        $html .= '<div class="section-header">';
        $html .= '<h3>Membres de l\'équipe</h3>';
        $html .= '<button type="button" class="btn btn-primary" onclick="AdminModal.open(\'addMemberModal\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter un membre';
        $html .= '</button>';
        $html .= '</div>';
        
        if (empty($members)) {
            $html .= '<div class="empty-state">';
            $html .= '<p>Aucun membre ajouté pour le moment.</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="members-grid">';
            foreach ($members as $member) {
                $html .= $this->renderMemberCard($member);
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    protected function renderMemberCard($member) {
        $dataAttrs = ' data-id="' . htmlspecialchars($member['id']) . '"'
            . ' data-name="' . htmlspecialchars($member['name'] ?? '') . '"'
            . ' data-role="' . htmlspecialchars($member['role'] ?? '') . '"'
            . ' data-description="' . htmlspecialchars($member['description'] ?? '') . '"';
        $html = '<div class="member-card"' . $dataAttrs . '>';
        
        // Photo
        $html .= '<div class="member-photo">';
        $img = $member['image'] ?? ($member['photo'] ?? null);
        if (!empty($img)) {
            // Normaliser chemin: accepter 'uploads/...' ou juste le fichier
            if (strpos($img, 'uploads/') === 0) {
                $src = '../../' . $img; // déjà sous uploads
            } else {
                $src = '../../uploads/' . ltrim($img, '/');
            }
            $html .= '<img src="' . htmlspecialchars($src) . '" alt="' . htmlspecialchars($member['name'] ?? 'Membre') . '">';
        } else {
            $html .= '<div class="member-placeholder"><i class="fas fa-user"></i></div>';
        }
        $html .= '</div>';
        
        // Informations
        $html .= '<div class="member-info">';
        $html .= '<h3>' . htmlspecialchars($member['name']) . '</h3>';
        $html .= '<div class="member-role">' . htmlspecialchars($member['role']) . '</div>';
        if (!empty($member['quote'])) {
            $html .= '<div class="member-quote">"' . htmlspecialchars($member['quote']) . '"</div>';
        }
        $html .= '</div>';
        
        // Actions
        $html .= '<div class="member-actions">';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="AdminModal.open(\'editMemberModal\', this.closest(\'.member-card\').dataset)">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-danger" data-action="delete-member" data-id="' . htmlspecialchars($member['id']) . '">';
        $html .= '<i class="fas fa-trash"></i> Supprimer';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    protected function processFormData($postData) {
        $title = trim($postData['title'] ?? '');
        if (empty($title)) {
            throw new Exception('Le titre principal est requis');
        }
        
        // Persister directement dans data/site_content.json
        $dataFile = __DIR__ . '/../../../data/site_content.json';
        $all = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
        if (!is_array($all)) { $all = []; }
        $all['equipe'] = array_merge($all['equipe'] ?? [], [
            'title' => $title,
            'subtitle' => trim($postData['subtitle'] ?? '')
        ]);
        file_put_contents($dataFile, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return [
            'success' => true,
            'message' => 'Section Équipe mise à jour avec succès',
            'data' => $all['equipe']
        ];
    }
}
