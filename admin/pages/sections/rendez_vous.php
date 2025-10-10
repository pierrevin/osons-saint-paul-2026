<?php
require_once __DIR__ . '/../../includes/AdminSection.php';

/**
 * Section Rendez-vous - Gestion des événements
 */
class RendezVousSection extends AdminSection {
    
    public function __construct($content = null) {
        parent::__construct('rendez_vous', 'Rendez-vous', 'fas fa-calendar', $content);
    }
    
    protected function renderForm() {
        $rendezVousContent = $this->content['rendez_vous'] ?? [];
        
        $html = '<div class="block-edit-form">';
        $html .= '<form method="POST" action="" id="rendez_vous-form-element" class="admin-form">';
        $html .= '<input type="hidden" name="csrf_token" value="' . $this->csrfToken . '">';
        $html .= '<input type="hidden" name="action" value="update_rendez_vous">';
        $html .= '<input type="hidden" name="redirect_section" value="rendez_vous">';
        
        // Titre principal (H2)
        $html .= $this->renderFormField(
            'text',
            'h2',
            'Titre principal (H2)',
            $rendezVousContent['h2'] ?? 'Nos rendez-vous',
            true,
            ['onchange' => 'markFormChanged(\'rendez_vous\')']
        );
        
        // Sous-titre (H3)
        $html .= $this->renderFormField(
            'text',
            'h3',
            'Sous-titre (H3)',
            $rendezVousContent['h3'] ?? 'Osons échanger',
            false,
            ['onchange' => 'markFormChanged(\'rendez_vous\')']
        );
        
        // Boutons d'action
        $html .= '<div class="form-actions" style="margin-top: 1rem; display: flex; gap: .5rem;">';
        $html .= '<button type="submit" form="rendez_vous-form-element" class="btn btn-primary">Sauvegarder</button>';
        $html .= '<button type="button" class="btn btn-secondary" onclick="window.location.reload()">Annuler</button>';
        $html .= '</div>';
        
        $html .= '</form>';
        $html .= '</div>';
        
        // Section des événements
        $html .= $this->renderEventsSection($rendezVousContent);
        
        return $html;
    }
    
    protected function renderEventsSection($rendezVousContent) {
        $events = $rendezVousContent['events'] ?? [];
        
        $html = '<div class="events-section">';
        $html .= '<div class="section-header">';
        $html .= '<h3>Événements</h3>';
        $html .= '<button type="button" class="btn btn-primary" onclick="AdminModal.open(\'addEventModal\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter un événement';
        $html .= '</button>';
        $html .= '</div>';
        
        if (empty($events)) {
            $html .= '<div class="empty-state">';
            $html .= '<p>Aucun événement programmé pour le moment.</p>';
            $html .= '</div>';
        } else {
            foreach ($events as $event) {
                $html .= $this->renderEventCard($event);
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    protected function renderEventCard($event) {
        $dataId = htmlspecialchars($event['id']);
        $dataTitle = htmlspecialchars($event['title'] ?? '');
        $dataDesc = htmlspecialchars($event['description'] ?? '');
        $dataDate = htmlspecialchars($event['date'] ?? '');
        $dataLoc = htmlspecialchars($event['location'] ?? '');
        $html = '<div class="event-card" data-id="' . $dataId . '" data-title="' . $dataTitle . '" data-description="' . $dataDesc . '" data-date="' . $dataDate . '" data-location="' . $dataLoc . '">';
        
        $html .= '<div class="event-header">';
        $html .= '<div class="event-title">' . htmlspecialchars($event['title']) . '</div>';
        $html .= '<div class="event-date">' . htmlspecialchars($event['date']) . '</div>';
        $html .= '</div>';
        
        if (!empty($event['description'])) {
            $html .= '<div class="event-description">' . htmlspecialchars($event['description']) . '</div>';
        }
        
        $html .= '<div class="event-actions">';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="AdminModal.open(\'editEventModal\', this.closest(\'.event-card\').dataset)">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-danger" data-action="delete-event" data-id="' . htmlspecialchars($event['id']) . '">';
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

        // Persister directement dans data/site_content.json
        $dataFile = DATA_PATH . '/site_content.json';
        $all = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
        if (!is_array($all)) { $all = []; }
        $all['rendez_vous'] = array_merge($all['rendez_vous'] ?? [], [
            'h2' => $h2,
            'h3' => $h3
        ]);
        file_put_contents($dataFile, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'success' => true,
            'message' => 'Section Rendez-vous mise à jour avec succès',
            'data' => $all['rendez_vous']
        ];
    }
}
