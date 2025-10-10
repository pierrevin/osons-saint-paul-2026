<?php
require_once __DIR__ . '/../../includes/AdminSection.php';
require_once __DIR__ . '/../../includes/image_processor.php';

/**
 * Section Hero - Page d'accueil
 */
class HeroSection extends AdminSection {
    
    public function __construct($content = null) {
        parent::__construct('hero', 'Hero', 'fas fa-home', $content);
    }
    
    protected function renderForm() {
        $heroContent = $this->content['hero'] ?? [];
        
        $html = '<div class="block-edit-form">';
        $html .= '<form method="POST" action="" id="hero-form-element" class="admin-form" enctype="multipart/form-data">';
        $html .= '<input type="hidden" name="csrf_token" value="' . $this->csrfToken . '">';
        $html .= '<input type="hidden" name="action" value="update_hero">';
        
        // Titre principal
        $html .= $this->renderFormField(
            'text',
            'title',
            'Titre principal',
            $heroContent['title'] ?? '',
            true,
            ['onchange' => 'markFormChanged(\'hero\')']
        );
        
        // Bouton principal
        $html .= $this->renderFormField(
            'text',
            'button_primary',
            'Bouton principal',
            $heroContent['button_primary'] ?? '',
            false,
            ['onchange' => 'markFormChanged(\'hero\')']
        );
        
        // Bouton secondaire
        $html .= $this->renderFormField(
            'text',
            'button_secondary',
            'Bouton secondaire',
            $heroContent['button_secondary'] ?? '',
            false,
            ['onchange' => 'markFormChanged(\'hero\')']
        );
        
        // Image de fond
        $html .= $this->renderImageField($heroContent);
        
        $html .= '</form>';
        $html .= '</div>';
        
        return $html;
    }
    
    protected function renderImageField($heroContent) {
        $html = '<div class="form-group">';
        $html .= '<label for="hero_background_image">Image de fond</label>';
        
        $heroImage = $heroContent['background_image'] ?? 'hero-bg.jpg';
        $imagePath = __DIR__ . '/../../../' . $heroImage;
        
        if (file_exists($imagePath)) {
            $html .= '<div class="current-image-preview">';
            $displaySrc = $heroImage;
            if (strpos($displaySrc, '/') === false) {
                $displaySrc = 'uploads/' . $displaySrc;
            }
            $html .= '<img src="../../' . htmlspecialchars($displaySrc) . '" alt="Image actuelle" class="image-thumbnail">';
            $html .= '<small class="form-help">Image actuelle : ' . htmlspecialchars($heroImage) . '</small>';
            $html .= '</div>';
        } else {
            $html .= '<small class="form-help">Aucune image trouvée : ' . htmlspecialchars($heroImage) . '</small>';
        }
        
        $html .= '<input type="file" id="hero_background_image" name="background_image" accept="image/*" data-crop="hero" onchange="markFormChanged(\'hero\')" style="width: 100%; margin-top: 10px;">';
        $html .= '</div>';
        
        return $html;
    }
    
    protected function processFormData($postData) {
        // Validation des données
        $title = trim($postData['title'] ?? '');
        if (empty($title)) {
            throw new Exception('Le titre principal est requis');
        }
        
        $buttonPrimary = trim($postData['button_primary'] ?? '');
        $buttonSecondary = trim($postData['button_secondary'] ?? '');
        
        // Gestion de l'image: préférer le chemin post-AJAX, sinon fallback upload direct traité
        $backgroundImage = null;
        if (!empty($postData['background_image_path'])) {
            $backgroundImage = trim($postData['background_image_path']);
        } else {
            $backgroundImage = $this->handleImageUpload();
        }
        
        // Mise à jour des données
        $heroData = [
            'title' => $title,
            'button_primary' => $buttonPrimary,
            'button_secondary' => $buttonSecondary
        ];
        
        if ($backgroundImage) {
            $heroData['background_image'] = $backgroundImage;
        }
        
        // Persister dans data/site_content.json
        $dataFile = DATA_PATH . '/site_content.json';
        $all = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
        if (!is_array($all)) { $all = []; }
        $all['hero'] = array_merge($all['hero'] ?? [], $heroData);
        file_put_contents($dataFile, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        return [
            'success' => true,
            'message' => 'Section Hero mise à jour avec succès',
            'data' => $heroData
        ];
    }
    
    protected function handleImageUpload() {
        if (!isset($_FILES['background_image']) || $_FILES['background_image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $uploadDir = __DIR__ . '/../../../uploads/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        $processor = new ImageProcessor();
        $result = $processor->processWithPreset($_FILES['background_image'], rtrim($uploadDir, '/'), 'hero', 'hero-bg');
        if (!empty($result['success']) && !empty($result['filename'])) {
            return 'uploads/' . $result['filename'];
        }
        return null;
    }
}
