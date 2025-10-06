<?php
require_once __DIR__ . '/../../includes/AdminSection.php';

/**
 * Section Médiathèque - Gestion des médias
 */
class MediathequeSection extends AdminSection {
    
    public function __construct($content = null) {
        parent::__construct('mediatheque', 'Ressources', 'fas fa-photo-video', $content);
    }
    
    protected function renderForm() {
        $mediathequeContent = $this->content['mediatheque'] ?? [];
        
        $html = '<div class="block-edit-form">';
        $html .= '<form method="POST" action="" id="mediatheque-form-element" class="admin-form">';
        $html .= '<input type="hidden" name="csrf_token" value="' . $this->csrfToken . '">';
        $html .= '<input type="hidden" name="action" value="update_mediatheque">';
        
        // Titre principal
        $html .= $this->renderFormField(
            'text',
            'title',
            'Titre principal',
            $mediathequeContent['title'] ?? 'Médiathèque',
            true,
            ['onchange' => 'markFormChanged(\'mediatheque\')']
        );
        
        // Sous-titre
        $html .= $this->renderFormField(
            'text',
            'subtitle',
            'Sous-titre',
            $mediathequeContent['subtitle'] ?? 'Accédez à notre drive partagé',
            false,
            ['onchange' => 'markFormChanged(\'mediatheque\')']
        );
        
        // URL du drive externe
        $html .= $this->renderFormField(
            'url',
            'drive_url',
            'URL du Drive externe',
            $mediathequeContent['drive_url'] ?? 'https://drive.google.com/drive/folders/...',
            true,
            ['onchange' => 'markFormChanged(\'mediatheque\')']
        );
        
        // Description du bouton
        $html .= $this->renderFormField(
            'text',
            'button_text',
            'Texte du bouton',
            $mediathequeContent['button_text'] ?? 'Accéder à la médiathèque',
            true,
            ['onchange' => 'markFormChanged(\'mediatheque\')']
        );
        
        // Boutons d'action
        $html .= '<div class="form-actions" style="margin-top: 1rem; display: flex; gap: .5rem;">';
        $html .= '<button type="submit" form="mediatheque-form-element" class="btn btn-primary">Sauvegarder</button>';
        $html .= '<button type="button" class="btn btn-secondary" onclick="window.location.reload()">Annuler</button>';
        $html .= '</div>';
        
        $html .= '</form>';
        $html .= '</div>';
        
        // Section d'information
        $html .= $this->renderInfoSection();
        
        return $html;
    }
    
    protected function renderInfoSection() {
        $html = '<div class="info-section">';
        $html .= '<div class="info-card">';
        $html .= '<div class="info-icon">';
        $html .= '<i class="fas fa-info-circle"></i>';
        $html .= '</div>';
        $html .= '<div class="info-content">';
        $html .= '<h3>Configuration de la médiathèque</h3>';
        $html .= '<p>Cette section redirige vers un drive externe (Google Drive, Dropbox, etc.) où sont stockés tous les médias.</p>';
        $html .= '<ul>';
        $html .= '<li><strong>URL du Drive :</strong> Configurez l\'URL vers votre drive partagé</li>';
        $html .= '<li><strong>Bouton d\'accès :</strong> Personnalisez le texte du bouton</li>';
        $html .= '<li><strong>Accès public :</strong> Le drive doit être accessible publiquement</li>';
        $html .= '</ul>';
        $html .= '</div>';
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
        $all['mediatheque'] = array_merge($all['mediatheque'] ?? [], [
            'title' => $title,
            'subtitle' => trim($postData['subtitle'] ?? ''),
            'drive_url' => trim($postData['drive_url'] ?? ''),
            'button_text' => trim($postData['button_text'] ?? '')
        ]);
        file_put_contents($dataFile, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return [
            'success' => true,
            'message' => 'Section Médiathèque mise à jour avec succès',
            'data' => $all['mediatheque']
        ];
    }
}
