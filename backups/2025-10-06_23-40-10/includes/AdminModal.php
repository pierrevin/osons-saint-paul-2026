<?php
/**
 * Classe pour gérer les modals d'administration
 * Standardise la création et gestion des modals
 */
class AdminModal {
    protected $modalId;
    protected $title;
    protected $content;
    protected $buttons;
    protected $size;
    
    public function __construct($modalId, $title, $content = '', $buttons = [], $size = 'normal') {
        $this->modalId = $modalId;
        $this->title = $title;
        $this->content = $content;
        $this->buttons = $buttons;
        $this->size = $size;
    }
    
    /**
     * Génère le HTML complet du modal
     */
    public function render() {
        $sizeClass = $this->size === 'large' ? 'modal-large' : '';
        
        $html = '<div id="' . $this->modalId . '" class="modal-overlay" style="display: none;">';
        $html .= '<div class="modal-container ' . $sizeClass . '">';
        
        // Header
        $html .= '<div class="modal-header">';
        $html .= '<h3>' . htmlspecialchars($this->title) . '</h3>';
        $html .= '<button onclick="AdminModal.close(\'' . $this->modalId . '\')" class="btn-close">&times;</button>';
        $html .= '</div>';
        
        // Body
        $html .= '<div class="modal-body">';
        $html .= $this->content;
        $html .= '</div>';
        
        // Footer avec boutons
        if (!empty($this->buttons)) {
            $html .= '<div class="modal-footer">';
            foreach ($this->buttons as $button) {
                $html .= $this->renderButton($button);
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Génère un bouton pour le modal
     */
    protected function renderButton($button) {
        $class = $button['class'] ?? 'btn btn-secondary';
        $onclick = isset($button['onclick']) ? 'onclick="' . htmlspecialchars($button['onclick']) . '"' : '';
        $text = htmlspecialchars($button['text']);
        
        return '<button type="button" class="' . $class . '" ' . $onclick . '>' . $text . '</button>';
    }
    
    /**
     * Crée un modal de confirmation
     */
    public static function createConfirmation($modalId, $title, $message, $confirmAction, $cancelAction = null) {
        $buttons = [
            [
                'text' => 'Confirmer',
                'class' => 'btn btn-danger',
                'onclick' => $confirmAction
            ]
        ];
        
        if ($cancelAction) {
            $buttons[] = [
                'text' => 'Annuler',
                'class' => 'btn btn-secondary',
                'onclick' => $cancelAction
            ];
        }
        
        return new self($modalId, $title, '<p>' . htmlspecialchars($message) . '</p>', $buttons);
    }
    
    /**
     * Crée un modal de formulaire
     */
    public static function createForm($modalId, $title, $formContent, $submitAction, $cancelAction = null) {
        $buttons = [
            [
                'text' => 'Sauvegarder',
                'class' => 'btn btn-primary',
                'onclick' => $submitAction
            ]
        ];
        
        if ($cancelAction) {
            $buttons[] = [
                'text' => 'Annuler',
                'class' => 'btn btn-secondary',
                'onclick' => $cancelAction
            ];
        }
        
        return new self($modalId, $title, $formContent, $buttons, 'large');
    }
}
