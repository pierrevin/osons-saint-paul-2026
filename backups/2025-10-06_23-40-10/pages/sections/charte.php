<?php
require_once __DIR__ . '/../../includes/AdminSection.php';

/**
 * Section Charte - Gestion des principes et valeurs
 */
class CharteSection extends AdminSection {
    
    public function __construct($content = null) {
        parent::__construct('charte', 'Charte', 'fas fa-handshake', $content);
    }
    
    protected function renderForm() {
        $charteContent = $this->content['charte'] ?? [];
        
        $html = '<div class="block-edit-form">';
        $html .= '<form method="POST" action="" id="charte-form-element" class="admin-form">';
        $html .= '<input type="hidden" name="csrf_token" value="' . $this->csrfToken . '">';
        $html .= '<input type="hidden" name="action" value="update_charte">';
        $html .= '<input type="hidden" name="redirect_section" value="charte">';
        
        // Titre principal (H2)
        $html .= $this->renderFormField(
            'text',
            'h2',
            'Titre principal (H2)',
            $charteContent['h2'] ?? 'Notre charte',
            true,
            ['onchange' => 'markFormChanged(\'charte\')']
        );
        
        // Sous-titre (H3)
        $html .= $this->renderFormField(
            'text',
            'h3',
            'Sous-titre (H3)',
            $charteContent['h3'] ?? 'Nos engagements',
            false,
            ['onchange' => 'markFormChanged(\'charte\')']
        );

        // Description avec HTML
        $html .= $this->renderFormField(
            'textarea',
            'description',
            'Description (HTML autorisé)',
            $charteContent['description'] ?? '',
            false,
            ['rows' => '6', 'onchange' => 'markFormChanged(\'charte\')']
        );
        
        // Boutons d'action
        $html .= '<div class="form-actions" style="margin-top: 1rem; display: flex; gap: .5rem;">';
        $html .= '<button type="submit" form="charte-form-element" class="btn btn-primary">Sauvegarder</button>';
        $html .= '<button type="button" class="btn btn-secondary" onclick="window.location.reload()">Annuler</button>';
        $html .= '</div>';
        
        $html .= '</form>';
        $html .= '</div>';
        
        // Section des principes
        $html .= $this->renderPrinciplesSection($charteContent);
        
        return $html;
    }
    
    protected function renderPrinciplesSection($charteContent) {
        $principles = $charteContent['principles'] ?? [];
        
        $html = '<div class="principles-section">';
        $html .= '<div class="section-header">';
        $html .= '<h3>Principes</h3>';
        $html .= '<button type="button" class="btn btn-primary" onclick="AdminModal.open(\'addPrincipleModal\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter un principe';
        $html .= '</button>';
        $html .= '</div>';
        
        if (empty($principles)) {
            $html .= '<div class="empty-state">';
            $html .= '<p>Aucun principe défini pour le moment.</p>';
            $html .= '</div>';
        } else {
            foreach ($principles as $index => $principle) {
                $html .= $this->renderPrincipleCard($principle, $index + 1);
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    protected function renderPrincipleCard($principle, $number) {
        $dataAttrs = ' data-id="' . htmlspecialchars($principle['id']) . '"'
            . ' data-title="' . htmlspecialchars($principle['title'] ?? '') . '"'
            . ' data-description="' . htmlspecialchars($principle['description'] ?? '') . '"'
            . ' data-thematique="' . htmlspecialchars($principle['thematique'] ?? '') . '"';
        $html = '<div class="principle-card"' . $dataAttrs . '>';
        $html .= '<div class="principle-number">' . $number . '</div>';
        
        $html .= '<div class="principle-title">' . htmlspecialchars($principle['title']) . '</div>';
        
        if (!empty($principle['description'])) {
            $html .= '<div class="principle-description">' . htmlspecialchars($principle['description']) . '</div>';
        }
        
        $html .= '<div class="principle-actions">';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="AdminModal.open(\'editPrincipleModal\', this.closest(\'.principle-card\').dataset)">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-danger" data-action="delete-principle" data-id="' . htmlspecialchars($principle['id']) . '">';
        $html .= '<i class="fas fa-trash"></i> Supprimer';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    protected function processFormData($postData) {
        $h2 = trim($postData['h2'] ?? '');
        if ($h2 === '') { throw new Exception('Le titre (H2) est requis'); }
        $h3 = trim($postData['h3'] ?? '');
        $description = trim($postData['description'] ?? '');

        // Persister directement dans data/site_content.json
        $dataFile = __DIR__ . '/../../../data/site_content.json';
        $all = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
        if (!is_array($all)) { $all = []; }
        $all['charte'] = array_merge($all['charte'] ?? [], [
            'h2' => $h2,
            'h3' => $h3,
            'description' => $description
        ]);
        file_put_contents($dataFile, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'success' => true,
            'message' => 'Section Charte mise à jour avec succès',
            'data' => $all['charte']
        ];
    }
}
