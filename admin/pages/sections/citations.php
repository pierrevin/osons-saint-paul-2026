<?php
require_once __DIR__ . '/../../includes/AdminSection.php';
require_once __DIR__ . '/../../includes/image_processor.php';

/**
 * Section Citations - Gestion des citations de transition
 */
class CitationsSection extends AdminSection {
    
    public function __construct($content = null) {
        parent::__construct('citations', 'Citations', 'fas fa-quote-left', $content);
    }
    
    protected function renderForm() {
        $html = '<div class="block-edit-form">';
        $html .= '<form method="POST" action="" id="citations-form-element" class="admin-form">';
        $html .= '<input type="hidden" name="csrf_token" value="' . $this->csrfToken . '">';
        $html .= '<input type="hidden" name="action" value="update_citations">';
        
        // Gestion des 4 citations
        $citations = [
            'citation1' => 'Transition 1 (Programme → Équipe)',
            'citation2' => 'Transition 2 (Équipe → Rendez-vous)',
            'citation3' => 'Transition 3 (Rendez-vous → Charte)',
            'citation4' => 'Transition 4 (Charte → Idées)'
        ];
        
        foreach ($citations as $key => $label) {
            $citationData = $this->content['citations'][$key] ?? [];
            
            $html .= '<div class="citation-group">';
            $html .= '<h4>' . htmlspecialchars($label) . '</h4>';
            
            // Texte de la citation
            $html .= $this->renderFormField(
                'textarea',
                $key . '_text',
                'Texte de la citation',
                $citationData['text'] ?? '',
                true,
                [
                    'rows' => '3',
                    'onchange' => 'markFormChanged(\'citations\')'
                ]
            );
            
            // Auteur
            $html .= $this->renderFormField(
                'text',
                $key . '_author',
                'Auteur',
                $citationData['author'] ?? '',
                false,
                ['onchange' => 'markFormChanged(\'citations\')']
            );
            
            // Image de fond
            $html .= $this->renderImageField($key, $citationData);
            
            $html .= '</div>';
        }
        
        $html .= '</form>';
        $html .= '</div>';
        
        return $html;
    }
    
    protected function renderImageField($citationKey, $citationData) {
        $html = '<div class="form-group">';
        $html .= '<label for="' . $citationKey . '_background_image">Image de fond</label>';
        
        $imageName = $citationData['background_image'] ?? $citationKey . '-bg.jpg';
        $imagePath = __DIR__ . '/../../../' . $imageName;
        
        if (file_exists($imagePath)) {
            $html .= '<div class="current-image-preview">';
            $displaySrc = $imageName;
            if (strpos($displaySrc, '/') === false) {
                $displaySrc = 'uploads/' . $displaySrc;
            }
            $html .= '<img src="../../' . htmlspecialchars($displaySrc) . '" alt="Image actuelle" class="image-thumbnail">';
            $html .= '<small class="form-help">Image actuelle : ' . htmlspecialchars($imageName) . '</small>';
            $html .= '</div>';
        } else {
            $html .= '<small class="form-help">Aucune image trouvée : ' . htmlspecialchars($imageName) . '</small>';
        }
        
        $html .= '<input type="file" id="' . $citationKey . '_background_image" name="' . $citationKey . '_background_image" accept="image/*" data-crop="citation" onchange="markFormChanged(\'citations\')" style="width: 100%; margin-top: 10px;">';
        $html .= '</div>';
        
        return $html;
    }
    
    protected function processFormData($postData) {
        // Charger l'existant
        $dataFile = DATA_PATH . '/site_content.json';
        $all = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
        if (!is_array($all)) { $all = []; }
        $existing = $all['citations'] ?? [];

        $keys = ['citation1', 'citation2', 'citation3', 'citation4'];
        foreach ($keys as $key) {
            // Texte / auteur
            $text = trim($postData[$key . '_text'] ?? ($existing[$key]['text'] ?? ''));
            $author = trim($postData[$key . '_author'] ?? ($existing[$key]['author'] ?? ''));
            if ($text === '') {
                throw new Exception("Le texte de la {$key} est requis");
            }
            $existing[$key]['text'] = $text;
            $existing[$key]['author'] = $author;

            // Image
            $imageField = $key . '_background_image';
            $pathField = $imageField . '_path';
            if (!empty($_POST[$pathField])) {
                $img = trim($_POST[$pathField]);
                $existing[$key]['background_image'] = (strpos($img, 'uploads/') === 0) ? $img : ('uploads/' . basename($img));
            } elseif (isset($_FILES[$imageField]) && $_FILES[$imageField]['error'] === UPLOAD_ERR_OK) {
                $uploadedImage = $this->handleImageUpload($imageField, $key);
                if ($uploadedImage) {
                    $existing[$key]['background_image'] = $uploadedImage;
                }
            } // sinon conserver l'existant
        }

        // Sauvegarde
        $all['citations'] = $existing;
        file_put_contents($dataFile, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'success' => true,
            'message' => 'Citations mises à jour avec succès',
            'data' => $existing
        ];
    }
    
    protected function handleImageUpload($fieldName, $citationKey) {
        $uploadDir = __DIR__ . '/../../../uploads/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        $processor = new ImageProcessor();
        $result = $processor->processWithPreset($_FILES[$fieldName], rtrim($uploadDir, '/'), 'citation', $citationKey . '-bg');
        if (!empty($result['success']) && !empty($result['filename'])) {
            return 'uploads/' . $result['filename'];
        }
        return null;
    }
}
