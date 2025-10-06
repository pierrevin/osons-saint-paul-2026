<?php
/**
 * Classe de base pour les sections d'administration
 * Encapsule la logique commune à toutes les sections
 */
class AdminSection {
    protected $sectionId;
    protected $sectionName;
    protected $sectionIcon;
    protected $content;
    protected $csrfToken;
    
    public function __construct($sectionId, $sectionName, $sectionIcon, $content = null) {
        $this->sectionId = $sectionId;
        $this->sectionName = $sectionName;
        $this->sectionIcon = $sectionIcon;
        $this->content = $content;
        $this->csrfToken = function_exists('generate_csrf_token') ? generate_csrf_token() : 'test-token';
    }
    
    /**
     * Récupère l'ID de la section
     */
    public function getSectionId() {
        return $this->sectionId;
    }
    
    /**
     * Génère le HTML pour le menu de navigation
     */
    public function renderMenuItem($isActive = false) {
        $activeClass = $isActive ? 'active' : '';
        return sprintf(
            '<li class="menu-item %s">
                <a href="#" onclick="navigateToSection(\'%s\'); return false;">
                    <i class="%s"></i> %s
                </a>
            </li>',
            $activeClass,
            $this->sectionId,
            $this->sectionIcon,
            $this->sectionName
        );
    }
    
    /**
     * Génère le contenu principal de la section
     */
    public function renderContent() {
        $html = '<div class="admin-section-content" id="' . $this->sectionId . '-content">';
        $html .= $this->renderForm();
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Méthode à surcharger par les sections spécialisées
     */
    protected function renderForm() {
        return '<p>Contenu de la section ' . $this->sectionName . '</p>';
    }
    
    /**
     * Génère les champs de formulaire de base
     */
    protected function renderFormField($type, $name, $label, $value = '', $required = false, $attributes = []) {
        $requiredAttr = $required ? 'required' : '';
        $valueAttr = $value ? 'value="' . htmlspecialchars($value) . '"' : '';
        
        // Rendre l'ID unique en ajoutant le préfixe de section
        $uniqueId = $this->sectionId . '_' . $name;
        
        $attrString = '';
        foreach ($attributes as $key => $val) {
            $attrString .= ' ' . $key . '="' . htmlspecialchars($val) . '"';
        }
        
        $html = '<div class="form-group">';
        $html .= '<label for="' . $uniqueId . '">' . htmlspecialchars($label) . ($required ? ' *' : '') . '</label>';
        
        if ($type === 'textarea') {
            $html .= '<textarea id="' . $uniqueId . '" name="' . $name . '" ' . $requiredAttr . $attrString . '>' . htmlspecialchars($value) . '</textarea>';
        } else {
            $html .= '<input type="' . $type . '" id="' . $uniqueId . '" name="' . $name . '" ' . $valueAttr . ' ' . $requiredAttr . $attrString . '>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Traite la soumission du formulaire
     */
    public function handleSubmission($postData) {
        // Logique de base pour la validation CSRF
        if (!isset($postData['csrf_token']) || $postData['csrf_token'] !== $this->csrfToken) {
            throw new Exception('Token CSRF invalide');
        }
        
        return $this->processFormData($postData);
    }
    
    /**
     * Méthode à surcharger pour le traitement spécifique des données
     */
    protected function processFormData($postData) {
        return ['success' => true, 'message' => 'Section mise à jour'];
    }
    
    /**
     * Retourne les données JSON pour le JavaScript
     */
    public function getJsonData() {
        return json_encode([
            'id' => $this->sectionId,
            'name' => $this->sectionName,
            'icon' => $this->sectionIcon
        ]);
    }
}
