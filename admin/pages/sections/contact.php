<?php
require_once __DIR__ . '/../../includes/AdminSection.php';

/**
 * Section Contactez-nous - Formulaire de contact
 */
class ContactSection extends AdminSection {
    
    public function __construct($content = null) {
        parent::__construct('contact', 'Contactez-nous', 'fas fa-envelope', $content);
    }
    
    protected function renderForm() {
        // Aligner sur la section publique "idees" (Vos idées / contact)
        $contactContent = $this->content['idees'] ?? [];
        
        $html = '<div class="block-edit-form">';
        $html .= '<form method="POST" action="" id="contact-form-element" class="admin-form">';
        $html .= '<input type="hidden" name="csrf_token" value="' . $this->csrfToken . '">';
        $html .= '<input type="hidden" name="action" value="update_contact">';
        $html .= '<input type="hidden" name="redirect_section" value="contact">';
        
        // Titre principal
        $html .= $this->renderFormField(
            'text',
            'title',
            'Titre principal',
            $contactContent['title'] ?? 'Vos idées comptent',
            true,
            ['onchange' => 'markFormChanged(\'contact\')']
        );
        
        // Sous-titre
        $html .= $this->renderFormField(
            'text',
            'subtitle',
            'Sous-titre',
            $contactContent['subtitle'] ?? 'Osez nous contacter',
            false,
            ['onchange' => 'markFormChanged(\'contact\')']
        );
        
        // Description
        $html .= $this->renderFormField(
            'textarea',
            'description',
            'Description',
            $contactContent['description'] ?? 'Partagez vos idées, vos préoccupations, vos suggestions. Votre voix compte dans notre projet municipal.',
            false,
            [
                'rows' => '3',
                'onchange' => 'markFormChanged(\'contact\')'
            ]
        );
        
        // (Email/Téléphone retirés: non affichés sur la page publique)
        
        // Boutons d'action
        $html .= '<div class="form-actions" style="margin-top: 1rem; display: flex; gap: .5rem;">';
        $html .= '<button type="submit" form="contact-form-element" class="btn btn-primary">Sauvegarder</button>';
        $html .= '<button type="button" class="btn btn-secondary" onclick="window.location.reload()">Annuler</button>';
        $html .= '</div>';
        
        $html .= '</form>';        
        $html .= '</div>';
        
        return $html;
    }
    
    
    protected function processFormData($postData) {
        $title = trim($postData['title'] ?? '');
        if (empty($title)) {
            throw new Exception('Le titre principal est requis');
        }
        
        // Persister directement dans data/site_content.json sous la section publique "idees"
        $dataFile = DATA_PATH . '/site_content.json';
        $all = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
        if (!is_array($all)) { $all = []; }
        $all['idees'] = array_merge($all['idees'] ?? [], [
            'title' => $title,
            'subtitle' => trim($postData['subtitle'] ?? ''),
            'description' => trim($postData['description'] ?? '')
        ]);
        file_put_contents($dataFile, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return [
            'success' => true,
            'message' => 'Section Contactez-nous mise à jour avec succès',
            'data' => $all['idees']
        ];
    }
}
