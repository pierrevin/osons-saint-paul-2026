<?php
/**
 * Section Programme - Version UX Refactorisée
 * Gestion des propositions avec workflow en 3 zones
 */

class ProgrammeSection extends AdminSection {
    protected $programmeCount;
    
    public function __construct($content, $programmeCount = 0) {
        parent::__construct('programme', 'Programme', 'fas fa-list', $content);
        $this->programmeCount = $programmeCount;
    }
    
    public function renderForm() {
        // Charger les propositions citoyennes
        $citizenProposals = $this->loadCitizenProposals();
        $teamProposals = $this->content['programme']['proposals'] ?? [];
        
        // Exclure les propositions archivées/supprimées (soft delete)
        $citizenProposals = array_values(array_filter($citizenProposals, function($p) {
            return ($p['status'] ?? '') !== 'deleted';
        }));
        // Séparer par statut (citoyennes)
        $pendingProposals = array_filter($citizenProposals, function($p) { return ($p['status'] ?? '') === 'pending'; });

        // Construire l'ensemble des propositions citoyennes déjà intégrées dans les cartes équipe
        $integratedCitizenIds = [];
        foreach ($teamProposals as $tp) {
            if (isset($tp['citizen_proposal_id']) && $tp['citizen_proposal_id']) {
                $integratedCitizenIds[(string)$tp['citizen_proposal_id']] = true;
            }
        }

        // Propositions citoyennes approuvées à afficher: exclure celles déjà intégrées (pour éviter doublon visuel)
        $approvedCitizenProposals = array_filter($citizenProposals, function($p) use ($integratedCitizenIds) {
            if (($p['status'] ?? '') !== 'approved') { return false; }
            $pid = (string)($p['id'] ?? '');
            return !isset($integratedCitizenIds[$pid]);
        });
        $rejectedProposals = array_filter($citizenProposals, function($p) { return ($p['status'] ?? '') === 'rejected'; });
        
        // Construire la liste "validées" = cartes équipe + cartes citoyennes validées (sans doublons visuels)
        // On conserve TOUTES les cartes équipe (y compris celles issues d'une citoyenne intégrée)
        // et on ajoute les citoyennes validées (objets avec clé 'data')
        $allApprovedProposals = array_merge($teamProposals, $approvedCitizenProposals);
        
        // Trier par ordre d'affichage (les propositions d'équipe en premier, puis les citoyennes par date)
        usort($allApprovedProposals, function($a, $b) {
            $aIsTeam = !isset($a['data']) || !is_array($a['data']);
            $bIsTeam = !isset($b['data']) || !is_array($b['data']);
            
            // Les propositions d'équipe en premier
            if ($aIsTeam && !$bIsTeam) return -1;
            if (!$aIsTeam && $bIsTeam) return 1;
            
            // Si les deux sont du même type, trier par date (plus récent en premier)
            $aDate = $aIsTeam ? ($a['date_added'] ?? '1970-01-01') : $a['date'];
            $bDate = $bIsTeam ? ($b['date_added'] ?? '1970-01-01') : $b['date'];
            return strtotime($bDate) - strtotime($aDate);
        });
        
        $html = '<div class="section-header">';
        $html .= '<h3><i class="fas fa-list"></i> Programme</h3>';
        $html .= '<p>Gérez les propositions du programme avec workflow de validation</p>';
        $html .= '<div class="section-actions">';
        $html .= '<button class="btn btn-primary" onclick="openProposalModal(\'create\')">';
        $html .= '<i class="fas fa-plus"></i> Ajouter une proposition';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Statistiques
        $html .= '<div class="stats-overview">';
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-icon"><i class="fas fa-clock"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . count($pendingProposals) . '</span>';
        $html .= '<span class="stat-label">En attente</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-icon"><i class="fas fa-check"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . count($allApprovedProposals) . '</span>';
        $html .= '<span class="stat-label">Validées</span>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="stat-card">';
        $html .= '<div class="stat-icon"><i class="fas fa-times"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<span class="stat-number">' . count($rejectedProposals) . '</span>';
        $html .= '<span class="stat-label">Rejetées</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Zone 1: Propositions en attente
        $html .= '<div class="proposals-zone pending-zone">';
        $html .= '<div class="zone-header">';
        $html .= '<h4><i class="fas fa-clock"></i> Propositions en attente</h4>';
        $html .= '<span class="zone-badge pending">' . count($pendingProposals) . ' en attente</span>';
        $html .= '</div>';
        
        if (empty($pendingProposals)) {
            $html .= '<div class="empty-state">';
            $html .= '<p>Aucune proposition en attente de validation.</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="proposals-grid">';
            foreach ($pendingProposals as $proposal) {
                $html .= $this->renderPendingProposalCard($proposal);
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        
        // Zone 2: Propositions validées
        $html .= '<div class="proposals-zone approved-zone">';
        $html .= '<div class="zone-header">';
        $html .= '<h4><i class="fas fa-check"></i> Propositions validées</h4>';
        $html .= '<span class="zone-badge approved">' . count($allApprovedProposals) . ' validées</span>';
        $html .= '</div>';
        
        if (empty($allApprovedProposals)) {
            $html .= '<div class="empty-state">';
            $html .= '<p>Aucune proposition validée.</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="proposals-grid">';
            foreach ($allApprovedProposals as $proposal) {
                $html .= $this->renderApprovedProposalCard($proposal);
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        
        // Zone 3: Propositions rejetées (dépliable)
        $html .= '<div class="proposals-zone rejected-zone">';
        $html .= '<div class="zone-header collapsible" onclick="toggleRejectedZone()">';
        $html .= '<h4><i class="fas fa-times"></i> Propositions rejetées</h4>';
        $html .= '<span class="zone-badge rejected">' . count($rejectedProposals) . ' rejetées</span>';
        $html .= '<i class="fas fa-chevron-down toggle-icon"></i>';
        $html .= '</div>';
        
        $html .= '<div class="zone-content" id="rejected-content" style="display: none;">';
        if (empty($rejectedProposals)) {
            $html .= '<div class="empty-state">';
            $html .= '<p>Aucune proposition rejetée.</p>';
            $html .= '</div>';
        } else {
            $html .= '<div class="proposals-grid">';
            foreach ($rejectedProposals as $proposal) {
                $html .= $this->renderRejectedProposalCard($proposal);
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    private function loadCitizenProposals() {
        $propositionsFile = DATA_PATH . '/propositions.json';
        if (file_exists($propositionsFile)) {
            $data = json_decode(file_get_contents($propositionsFile), true);
            return $data['propositions'] ?? [];
        }
        return [];
    }
    
    private function renderPendingProposalCard($proposal) {
        $html = '<div class="proposal-card pending-card">';
        $html .= '<div class="proposal-header">';
        $html .= '<h5>' . htmlspecialchars($proposal['data']['titre']) . '</h5>';
        $html .= '<span class="status-badge pending">EN ATTENTE</span>';
        $html .= '</div>';
        
        $html .= '<div class="proposal-content">';
        $html .= '<p>' . htmlspecialchars($proposal['data']['description']) . '</p>';
        $html .= '<div class="proposal-meta">';
        $html .= '<small><i class="fas fa-user"></i> ' . htmlspecialchars($proposal['data']['nom'] ?: 'Anonyme') . '</small>';
        $html .= '<small><i class="fas fa-calendar"></i> ' . date('d/m/Y', strtotime($proposal['date'])) . '</small>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<div class="proposal-actions">';
        $proposalJson = htmlspecialchars(json_encode($proposal, JSON_HEX_QUOT | JSON_HEX_APOS), ENT_NOQUOTES, 'UTF-8');
        $html .= '<button class="btn btn-sm btn-success" data-proposal=\'' . $proposalJson . '\' onclick="openProposalModal(\'approve\', JSON.parse(this.dataset.proposal))">';
        $html .= '<i class="fas fa-check"></i> Modifier & Approuver';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-danger" onclick="rejectProposal(\'' . htmlspecialchars($proposal['id'], ENT_QUOTES, 'UTF-8') . '\')">';
        $html .= '<i class="fas fa-times"></i> Rejeter';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-secondary" onclick="deleteProposal(\'' . htmlspecialchars($proposal['id'], ENT_QUOTES, 'UTF-8') . '\')">';
        $html .= '<i class="fas fa-trash"></i> Supprimer';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        return $html;
    }
    
    private function renderApprovedProposalCard($proposal) {
        // Badge citoyen si:
        // - case "badge citoyen" cochée (citizen_proposal true),
        // - objet citoyen brut (clé 'data'),
        // - ou carte équipe issue d'une intégration citoyenne (citizen_proposal_id)
        $isCitizenBadge = (!empty($proposal['citizen_proposal']))
            || (isset($proposal['data']) && is_array($proposal['data']))
            || (!empty($proposal['citizen_proposal_id']));

        // Source des données pour le contenu: préférer les données citoyennes si présentes, sinon données équipe
        $hasCitizenData = isset($proposal['data']) && is_array($proposal['data']);
        $title = $hasCitizenData ? ($proposal['data']['titre'] ?? '') : ($proposal['title'] ?? '');
        $description = $hasCitizenData ? ($proposal['data']['description'] ?? '') : ($proposal['description'] ?? '');
        
        $html = '<div class="proposal-card approved-card">';
        $html .= '<div class="proposal-header">';
        $html .= '<h5>' . htmlspecialchars($title) . '</h5>';
        $html .= '<span class="status-badge approved">VALIDÉE</span>';
        $html .= '<span class="source-badge ' . ($isCitizenBadge ? 'citizen' : 'team') . '">';
        $html .= $isCitizenBadge ? '<i class="fas fa-user"></i> Citoyen' : '<i class="fas fa-users"></i> Équipe';
        $html .= '</span>';
        $html .= '</div>';
        
        $html .= '<div class="proposal-content">';
        $html .= '<p>' . htmlspecialchars($description) . '</p>';
        
        // Aperçu des points clés pour les deux types de cartes
        $items = $hasCitizenData ? ($proposal['data']['items'] ?? []) : ($proposal['items'] ?? []);
        if (!empty($items) && is_array($items)) {
            $html .= '<ul class="proposal-items">';
            foreach ($items as $item) {
                $html .= '<li>' . htmlspecialchars($item) . '</li>';
            }
            $html .= '</ul>';
        }
        $html .= '</div>';
        
        $html .= '<div class="proposal-actions">';
        // Préparer les données pour le modal d'édition avec un indicateur citoyen
        $proposalForJs = $proposal;
        if ($isCitizenBadge) {
            $proposalForJs['citizen_proposal'] = true;
        }
        $proposalForJsJson = htmlspecialchars(json_encode($proposalForJs, JSON_HEX_QUOT | JSON_HEX_APOS), ENT_NOQUOTES, 'UTF-8');
        $html .= '<button class="btn btn-sm btn-secondary" data-proposal=\'' . $proposalForJsJson . '\' onclick="openProposalModal(\'edit\', JSON.parse(this.dataset.proposal))">';
        $html .= '<i class="fas fa-edit"></i> Modifier';
        $html .= '</button>';
        
        // Boutons spécifiques aux propositions citoyennes
        if ($isCitizenBadge) {
            $html .= '<button class="btn btn-sm btn-warning" onclick="setCitizenProposalStatus(\'' . htmlspecialchars($proposal['id'], ENT_QUOTES, 'UTF-8') . '\', \'pending\')">';
            $html .= '<i class="fas fa-clock"></i> Mettre en attente';
            $html .= '</button>';
            $html .= '<button class="btn btn-sm btn-danger" onclick="setCitizenProposalStatus(\'' . htmlspecialchars($proposal['id'], ENT_QUOTES, 'UTF-8') . '\', \'rejected\')">';
            $html .= '<i class="fas fa-times"></i> Rejeter';
            $html .= '</button>';
        } else {
            $html .= '<button class="btn btn-sm btn-danger" onclick="deleteProposal(' . htmlspecialchars($proposal['id'], ENT_QUOTES, 'UTF-8') . ')">';
            $html .= '<i class="fas fa-trash"></i> Supprimer';
            $html .= '</button>';
        }
        $html .= '</div>';
        
        $html .= '</div>';
        return $html;
    }
    
    private function renderRejectedProposalCard($proposal) {
        $html = '<div class="proposal-card rejected-card">';
        $html .= '<div class="proposal-header">';
        $html .= '<h5>' . htmlspecialchars($proposal['data']['titre']) . '</h5>';
        $html .= '<span class="status-badge rejected">REJETÉE</span>';
        $html .= '</div>';
        
        $html .= '<div class="proposal-content">';
        $html .= '<p>' . htmlspecialchars($proposal['data']['description']) . '</p>';
        if (isset($proposal['rejection_reason'])) {
            $html .= '<div class="rejection-reason">';
            $html .= '<strong>Raison du rejet :</strong> ' . htmlspecialchars($proposal['rejection_reason']);
            $html .= '</div>';
        }
        $html .= '</div>';
        
        $html .= '<div class="proposal-actions">';
        $html .= '<button class="btn btn-sm btn-warning" onclick="restoreProposal(\'' . htmlspecialchars($proposal['id'], ENT_QUOTES, 'UTF-8') . '\')">';
        $html .= '<i class="fas fa-undo"></i> Restaurer';
        $html .= '</button>';
        $html .= '<button class="btn btn-sm btn-danger" onclick="deleteProposal(\'' . htmlspecialchars($proposal['id'], ENT_QUOTES, 'UTF-8') . '\')">';
        $html .= '<i class="fas fa-trash"></i> Supprimer définitivement';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        return $html;
    }
    
    public function processFormData($data) {
        return ['success' => true, 'message' => 'Section programme chargée'];
    }
    
    public function handleSubmission($data) {
        $action = $data['action'] ?? '';
        
        try {
            switch ($action) {
                // Alias pour compat JS ancien en cache
                case 'set_citizen_proposal_status':
                    $action = 'set_citizen_status';
                    // pas de break; on enchaîne vers le case suivant
                case 'add_proposal':
                    return $this->addProposal($data);
                case 'edit_proposal':
                    return $this->editProposal($data);
                case 'delete_proposal':
                    return $this->deleteProposal($data);
                case 'approve_proposal':
                    return $this->approveProposal($data);
                case 'reject_proposal':
                    return $this->rejectProposal($data);
                case 'set_citizen_status':
                    return $this->setCitizenProposalStatus($data);
                case 'restore_proposal':
                    return $this->restoreCitizenProposal($data);
                default:
                    throw new Exception('Action non reconnue: ' . $action);
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    private function addProposal($data) {
        $siteContentPath = DATA_PATH . '/site_content.json';
        if (!file_exists($siteContentPath)) {
            throw new Exception('Contenu du site introuvable');
        }
        $content = json_decode(file_get_contents($siteContentPath), true);
        if (!isset($content['programme']['proposals']) || !is_array($content['programme']['proposals'])) {
            $content['programme']['proposals'] = [];
        }

        $items = isset($data['items']) ? array_filter(array_map('trim', explode("\n", $data['items']))) : [];
        $maxId = 0;
        foreach ($content['programme']['proposals'] as $p) {
            if (isset($p['id']) && $p['id'] > $maxId) { $maxId = $p['id']; }
        }
        $newId = $maxId + 1;

        $new = [
            'id' => $newId,
            'title' => trim($data['title'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'pillar' => trim($data['pillar'] ?? 'proteger'),
            'icon' => 'default',
            'color' => $this->pillarToColor($data['pillar'] ?? ''),
            // Badge citoyen selon la case cochée dans l'admin
            'citizen_proposal' => (isset($data['display_citizen_badge']) && (string)$data['display_citizen_badge'] === '1'),
            'items' => $items,
        ];

        if (empty($new['title']) || empty($new['description']) || empty($items)) {
            throw new Exception('Champs obligatoires manquants');
        }

        // Convertir object/map en array indexée si besoin
        $proposals = array_values($content['programme']['proposals']);
        $proposals[] = $new;
        $content['programme']['proposals'] = $proposals;

        if (!file_put_contents($siteContentPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            throw new Exception('Échec de la sauvegarde');
        }
        return ['success' => true, 'message' => 'Proposition ajoutée avec succès'];
    }
    
    private function editProposal($data) {
        error_log("🔄 editProposal appelée avec: " . json_encode($data));
        $proposalId = $data['proposal_id'] ?? null;
        if (!$proposalId) {
            throw new Exception('ID de proposition manquant');
        }

        $isCitizen = isset($data['citizen_proposal']) && $data['citizen_proposal'] === '1';
        error_log("📝 ID: $proposalId, IsCitizen: " . ($isCitizen ? 'OUI' : 'NON'));

        if ($isCitizen) {
            // Modifier une proposition citoyenne (même si approuvée)
            $citizenProposalsFile = DATA_PATH . '/propositions.json';
            if (!file_exists($citizenProposalsFile)) {
                throw new Exception('Fichier des propositions citoyennes introuvable');
            }
            $citizenData = json_decode(file_get_contents($citizenProposalsFile), true);
            $proposals = $citizenData['propositions'] ?? [];

            $found = false;
            error_log("🔍 Recherche de la proposition ID: $proposalId parmi " . count($proposals) . " propositions citoyennes");
            foreach ($proposals as &$proposal) {
                error_log("🔍 Comparaison: '" . $proposal['id'] . "' === '$proposalId' ? " . ($proposal['id'] === $proposalId ? 'OUI' : 'NON'));
                if ($proposal['id'] === $proposalId) {
                    error_log("✅ Proposition citoyenne trouvée, mise à jour...");
                    if (isset($data['title'])) {
                        $proposal['data']['titre'] = $data['title'];
                        error_log("📝 Titre mis à jour: " . $data['title']);
                    }
                    if (isset($data['description'])) {
                        $proposal['data']['description'] = $data['description'];
                        error_log("📝 Description mise à jour");
                    }
                    if (isset($data['items'])) {
                        $items = array_filter(array_map('trim', explode("\n", $data['items'])));
                        $categories = '';
                        $beneficiaries = '';
                        $cleanItems = [];
                        foreach ($items as $item) {
                            if (strpos($item, 'Catégories: ') === 0) { $categories = substr($item, 12); }
                            elseif (strpos($item, 'Bénéficiaires: ') === 0) { $beneficiaries = substr($item, 15); }
                            else { $cleanItems[] = $item; }
                        }
                        $proposal['data']['items'] = $cleanItems;
                        if ($categories) { $proposal['data']['categories'] = $categories; }
                        if ($beneficiaries) { $proposal['data']['beneficiaries'] = $beneficiaries; }
                    }
                    $proposal['updated_at'] = date('Y-m-d H:i:s');
                    $found = true;
                    break;
                }
            }
            if (!$found) { throw new Exception('Proposition citoyenne non trouvée'); }
            $citizenData['propositions'] = $proposals;
            if (!file_put_contents($citizenProposalsFile, json_encode($citizenData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                throw new Exception('Erreur lors de la sauvegarde');
            }

            // Mettre à jour la carte publique si cette proposition citoyenne est déjà intégrée au site
            $siteContentPath = DATA_PATH . '/site_content.json';
            if (file_exists($siteContentPath)) {
                $siteContent = json_decode(file_get_contents($siteContentPath), true);
                if (isset($siteContent['programme']['proposals']) && is_array($siteContent['programme']['proposals'])) {
                    // Recalculer items et déterminer pilier/couleur si possible
                    $updatedItems = $proposal['data']['items'] ?? [];
                    $firstCategory = '';
                    if (!empty($proposal['data']['categories'])) {
                        if (is_array($proposal['data']['categories'])) {
                            $firstCategory = $proposal['data']['categories'][0];
                        } else {
                            $parts = array_map('trim', explode(',', $proposal['data']['categories']));
                            $firstCategory = $parts[0] ?? '';
                        }
                        $firstCategory = html_entity_decode($firstCategory, ENT_QUOTES, 'UTF-8');
                    }
                    $pillarMapping = [
                        'Urbanisme & Logement' => 'dessiner',
                        'Urbanisme &amp; Logement' => 'dessiner',
                        'Environnement & Nature' => 'proteger',
                        'Environnement &amp; Nature' => 'proteger',
                        'Mobilité & Transport' => 'dessiner',
                        'Vie sociale & Solidarité' => 'tisser',
                        'Éducation & Jeunesse' => 'ouvrir',
                        'Education & Jeunesse' => 'ouvrir',
                        'Santé & Bien-être' => 'proteger',
                        'Sante & Bien-etre' => 'proteger',
                        'Culture & Sport' => 'tisser',
                        'Économie & Commerce' => 'ouvrir',
                        'Economie & Commerce' => 'ouvrir',
                        'Services publics' => 'proteger',
                        'Autre' => 'tisser'
                    ];
                    $pillarColors = [
                        'proteger' => '#65ae99',
                        'tisser'   => '#fcc549',
                        'dessiner' => '#4e9eb0',
                        'ouvrir'   => '#004a6d'
                    ];
                    $newPillar = $pillarMapping[$firstCategory] ?? null; // ne pas forcer si non déterminable

                    foreach ($siteContent['programme']['proposals'] as $idx => $p) {
                        if (($p['citizen_proposal_id'] ?? null) === $proposalId) {
                            $siteContent['programme']['proposals'][$idx]['title'] = $proposal['data']['titre'] ?? ($p['title'] ?? '');
                            $siteContent['programme']['proposals'][$idx]['description'] = $proposal['data']['description'] ?? ($p['description'] ?? '');
                            if (!empty($updatedItems)) {
                                $siteContent['programme']['proposals'][$idx]['items'] = $updatedItems;
                            }
                            if ($newPillar) {
                                $siteContent['programme']['proposals'][$idx]['pillar'] = $newPillar;
                                $siteContent['programme']['proposals'][$idx]['color'] = $pillarColors[$newPillar] ?? ($p['color'] ?? '#fcc549');
                            }
                            break;
                        }
                    }
                    file_put_contents($siteContentPath, json_encode($siteContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }

            return ['success' => true, 'message' => 'Proposition citoyenne mise à jour'];
        }

        // Modifier une proposition d'équipe
        $siteContentPath = DATA_PATH . '/site_content.json';
        if (!file_exists($siteContentPath)) {
            throw new Exception('Contenu du site introuvable');
        }
        $content = json_decode(file_get_contents($siteContentPath), true);
        $proposals = array_values($content['programme']['proposals'] ?? []);

        $found = false;
        foreach ($proposals as &$p) {
            if (isset($p['id']) && (string)$p['id'] === (string)$proposalId) {
                if (isset($data['title'])) { $p['title'] = $data['title']; }
                if (isset($data['description'])) { $p['description'] = $data['description']; }
                if (isset($data['pillar'])) { $p['pillar'] = $data['pillar']; $p['color'] = $this->pillarToColor($data['pillar']); }
                if (isset($data['items'])) {
                    $p['items'] = array_filter(array_map('trim', explode("\n", $data['items'])));
                }
                // Mettre à jour le badge citoyen si case cochée
                if (isset($data['display_citizen_badge'])) {
                    $p['citizen_proposal'] = ((string)$data['display_citizen_badge'] === '1');
                }
                $found = true;
                break;
            }
        }
        if (!$found) { throw new Exception('Proposition non trouvée'); }
        $content['programme']['proposals'] = $proposals;
        if (!file_put_contents($siteContentPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            throw new Exception('Erreur lors de la sauvegarde');
        }
        return ['success' => true, 'message' => 'Proposition modifiée avec succès'];
    }
    
    private function deleteProposal($data) {
        $proposalId = $data['proposal_id'] ?? null;
        if (!$proposalId) {
            throw new Exception('ID de proposition manquant');
        }
        
        // 1) Tenter de supprimer une carte d'équipe (site_content.json par ID numérique)
        $siteContentPath = DATA_PATH . '/site_content.json';
        if (file_exists($siteContentPath)) {
            $content = json_decode(file_get_contents($siteContentPath), true);
            $proposals = array_values($content['programme']['proposals'] ?? []);
            $before = count($proposals);
            $proposals = array_values(array_filter($proposals, function($p) use ($proposalId) {
                return (string)$p['id'] !== (string)$proposalId;
            }));
            if (count($proposals) < $before) {
                $content['programme']['proposals'] = $proposals;
                if (!file_put_contents($siteContentPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                    throw new Exception('Erreur lors de la sauvegarde');
                }
                return ['success' => true, 'message' => 'Proposition supprimée avec succès'];
            }
        }

        // 2) Sinon, tenter d'archiver (soft delete) une proposition citoyenne (propositions.json par ID string)
        $citizenProposalsFile = DATA_PATH . '/propositions.json';
        if (file_exists($citizenProposalsFile)) {
            $citizenData = json_decode(file_get_contents($citizenProposalsFile), true);
            $citizenList = array_values($citizenData['propositions'] ?? []);
            $changed = false;
            foreach ($citizenList as &$p) {
                if (($p['id'] ?? '') === $proposalId) {
                    $p['status'] = 'deleted'; // soft delete pour suivi
                    $p['updated_at'] = date('Y-m-d H:i:s');
                    $changed = true;
                    break;
                }
            }
            if ($changed) {
                // Retirer aussi la carte publique liée le cas échéant
                if (file_exists($siteContentPath)) {
                    $content = json_decode(file_get_contents($siteContentPath), true);
                    $content['programme']['proposals'] = array_values(array_filter(
                        $content['programme']['proposals'] ?? [],
                        function($p) use ($proposalId) { return ($p['citizen_proposal_id'] ?? null) !== $proposalId; }
                    ));
                    file_put_contents($siteContentPath, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
                $citizenData['propositions'] = $citizenList;
                file_put_contents($citizenProposalsFile, json_encode($citizenData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                return ['success' => true, 'message' => 'Proposition citoyenne archivée (soft delete)'];
            }
        }

        throw new Exception('Proposition non trouvée ou déjà supprimée');
    }

    private function restoreCitizenProposal($data) {
        $proposalId = $data['proposal_id'] ?? null;
        if (!$proposalId) { throw new Exception('ID de proposition manquant'); }

        $citizenProposalsFile = DATA_PATH . '/propositions.json';
        if (!file_exists($citizenProposalsFile)) { throw new Exception('Fichier des propositions citoyennes introuvable'); }

        $citizenData = json_decode(file_get_contents($citizenProposalsFile), true);
        $proposals = $citizenData['propositions'] ?? [];
        $found = false;
        foreach ($proposals as &$p) {
            if (($p['id'] ?? null) === $proposalId) {
                $p['status'] = 'pending';
                unset($p['rejection_reason']);
                $p['updated_at'] = date('Y-m-d H:i:s');
                $found = true;
                break;
            }
        }
        if (!$found) { throw new Exception('Proposition citoyenne non trouvée'); }

        $citizenData['propositions'] = $proposals;
        file_put_contents($citizenProposalsFile, json_encode($citizenData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // La restauration ne ré-intègre pas automatiquement sur le site; cela se fera lors d'une approbation
        return ['success' => true, 'message' => 'Proposition restaurée en attente'];
    }
    
    private function approveProposal($data) {
        $proposalId = $data['proposal_id'] ?? null;
        if (!$proposalId) {
            throw new Exception('ID de proposition manquant');
        }

        // Charger les propositions citoyennes
        $citizenProposalsFile = DATA_PATH . '/propositions.json';
        if (!file_exists($citizenProposalsFile)) {
            throw new Exception('Fichier des propositions citoyennes introuvable');
        }

        $citizenData = json_decode(file_get_contents($citizenProposalsFile), true);
        $proposals = $citizenData['propositions'] ?? [];

        // Trouver et approuver la proposition
        $found = false;
        $approvedProposal = null;
        foreach ($proposals as &$proposal) {
            if ($proposal['id'] == $proposalId) {
                $proposal['status'] = 'approved';
                $proposal['integrated'] = true;
                $proposal['integrated_at'] = date('Y-m-d H:i:s');
                $proposal['updated_at'] = date('Y-m-d H:i:s');
                
                // Mettre à jour les données avec les modifications du formulaire
                if (isset($data['title'])) {
                    $proposal['data']['titre'] = $data['title'];
                }
                if (isset($data['description'])) {
                    $proposal['data']['description'] = $data['description'];
                }
                if (isset($data['items'])) {
                    $items = array_filter(array_map('trim', explode("\n", $data['items'])));
                    $categories = '';
                    $beneficiaries = '';
                    $cleanItems = [];
                    
                    // Extraire catégories et bénéficiaires des points clés
                    foreach ($items as $item) {
                        if (strpos($item, 'Catégories: ') === 0) {
                            $categories = substr($item, 12);
                        } elseif (strpos($item, 'Bénéficiaires: ') === 0) {
                            $beneficiaries = substr($item, 15);
                        } else {
                            $cleanItems[] = $item;
                        }
                    }
                    
                    $proposal['data']['items'] = $cleanItems;
                    if ($categories) {
                        $proposal['data']['categories'] = $categories;
                    }
                    if ($beneficiaries) {
                        $proposal['data']['beneficiaries'] = $beneficiaries;
                    }
                }
                $approvedProposal = $proposal;
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception('Proposition non trouvée');
        }

        // Sauvegarder
        $citizenData['propositions'] = $proposals;
        if (!file_put_contents($citizenProposalsFile, json_encode($citizenData, JSON_PRETTY_PRINT))) {
            throw new Exception('Erreur lors de la sauvegarde');
        }

        // Envoyer l'email d'acceptation au citoyen
        if ($approvedProposal && isset($approvedProposal['data']['email'])) {
            try {
                require_once __DIR__ . '/../../../forms/email-service.php';
                $emailResult = EmailService::sendStatusUpdateEmail(
                    $approvedProposal['data']['email'], 
                    $approvedProposal, 
                    'approved'
                );
                error_log('Email d\'acceptation envoyé: ' . ($emailResult['success'] ? 'SUCCESS' : 'FAILED') . ' - ' . ($emailResult['error'] ?? ''));
            } catch (Exception $e) {
                error_log('Erreur envoi email acceptation: ' . $e->getMessage());
            }
        }

        // Synchroniser avec le site public (site_content.json)
        if ($approvedProposal) {
            $siteContentPath = DATA_PATH . '/site_content.json';
            if (!file_exists($siteContentPath)) {
                throw new Exception('Contenu du site introuvable');
            }
            $siteContent = json_decode(file_get_contents($siteContentPath), true);
            if (!isset($siteContent['programme']['proposals']) || !is_array($siteContent['programme']['proposals'])) {
                $siteContent['programme']['proposals'] = [];
            }

            // Déterminer le pilier/couleur à partir de la première catégorie si possible
            $pillarMapping = [
                'Urbanisme & Logement' => 'dessiner',
                'Urbanisme &amp; Logement' => 'dessiner',
                'Environnement & Nature' => 'proteger',
                'Environnement &amp; Nature' => 'proteger',
                'Mobilité & Transport' => 'dessiner',
                'Vie sociale & Solidarité' => 'tisser',
                'Éducation & Jeunesse' => 'ouvrir',
                'Education & Jeunesse' => 'ouvrir',
                'Santé & Bien-être' => 'proteger',
                'Sante & Bien-etre' => 'proteger',
                'Culture & Sport' => 'tisser',
                'Économie & Commerce' => 'ouvrir',
                'Economie & Commerce' => 'ouvrir',
                'Services publics' => 'proteger',
                'Autre' => 'tisser'
            ];
            $pillarColors = [
                'proteger' => '#65ae99',
                'tisser'   => '#fcc549',
                'dessiner' => '#4e9eb0',
                'ouvrir'   => '#004a6d'
            ];
            $firstCategory = '';
            if (!empty($approvedProposal['data']['categories'])) {
                if (is_array($approvedProposal['data']['categories'])) {
                    $firstCategory = $approvedProposal['data']['categories'][0];
                } else {
                    // Catégories en string séparées par virgules
                    $parts = array_map('trim', explode(',', $approvedProposal['data']['categories']));
                    $firstCategory = $parts[0] ?? '';
                }
                $firstCategory = html_entity_decode($firstCategory, ENT_QUOTES, 'UTF-8');
            }
            $pillar = $pillarMapping[$firstCategory] ?? 'tisser';
            $color = $pillarColors[$pillar] ?? '#fcc549';

            // Rechercher une carte déjà intégrée pour ce citizen_proposal_id
            $existingIndex = -1;
            foreach ($siteContent['programme']['proposals'] as $idx => $p) {
                if (($p['citizen_proposal_id'] ?? null) === $approvedProposal['id']) {
                    $existingIndex = $idx;
                    break;
                }
            }

            $cardPayload = [
                'title' => $approvedProposal['data']['titre'] ?? '',
                'description' => $approvedProposal['data']['description'] ?? '',
                'pillar' => $pillar,
                'color' => $color,
                'items' => $approvedProposal['data']['items'] ?? [],
                'source' => 'citoyenne',
                'citizen_proposal' => true,
                'citizen_proposal_id' => $approvedProposal['id']
            ];

            if ($existingIndex >= 0) {
                // Mettre à jour la carte existante
                $siteContent['programme']['proposals'][$existingIndex] = array_merge(
                    $siteContent['programme']['proposals'][$existingIndex],
                    $cardPayload
                );
            } else {
                // Générer un nouvel ID numérique
                $maxId = 0;
                foreach ($siteContent['programme']['proposals'] as $p) {
                    if (isset($p['id']) && is_numeric($p['id']) && $p['id'] > $maxId) {
                        $maxId = $p['id'];
                    }
                }
                $cardPayload['id'] = $maxId + 1;
                $siteContent['programme']['proposals'][] = $cardPayload;
            }

            if (!file_put_contents($siteContentPath, json_encode($siteContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                throw new Exception('Erreur lors de la mise à jour du site');
            }
        }

        return ['success' => true, 'message' => 'Proposition approuvée avec succès'];
    }
    
    private function rejectProposal($data) {
        $proposalId = $data['proposal_id'] ?? null;
        $rejectionReason = $data['rejection_reason'] ?? '';
        
        if (!$proposalId) {
            throw new Exception('ID de proposition manquant');
        }
        if (empty($rejectionReason)) {
            throw new Exception('La raison du rejet est obligatoire');
        }

        // Charger les propositions citoyennes
        $citizenProposalsFile = DATA_PATH . '/propositions.json';
        if (!file_exists($citizenProposalsFile)) {
            throw new Exception('Fichier des propositions citoyennes introuvable');
        }

        $citizenData = json_decode(file_get_contents($citizenProposalsFile), true);
        $proposals = $citizenData['propositions'] ?? [];

        // Trouver et rejeter la proposition
        $found = false;
        $citizenEmail = null;
        foreach ($proposals as &$proposal) {
            if ($proposal['id'] == $proposalId) {
                $proposal['status'] = 'rejected';
                $proposal['rejection_reason'] = $rejectionReason;
                $proposal['updated_at'] = date('Y-m-d H:i:s');
                $citizenEmail = $proposal['data']['email'] ?? null;
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception('Proposition non trouvée');
        }

        // Sauvegarder
        $citizenData['propositions'] = $proposals;
        if (!file_put_contents($citizenProposalsFile, json_encode($citizenData, JSON_PRETTY_PRINT))) {
            throw new Exception('Erreur lors de la sauvegarde');
        }

        // Envoyer l'email de rejet si possible
        if ($citizenEmail) {
            try {
                require_once __DIR__ . '/../../../forms/email-service.php';
                $proposalTitle = $proposals[array_search($proposalId, array_column($proposals, 'id'))]['data']['titre'] ?? 'Votre proposition';
                $proposalData = $proposals[array_search($proposalId, array_column($proposals, 'id'))];
                $proposalData['rejection_reason'] = $rejectionReason;
                $emailResult = EmailService::sendStatusUpdateEmail($citizenEmail, $proposalData, 'rejected');
                error_log('Email de rejet envoyé: ' . ($emailResult['success'] ? 'SUCCESS' : 'FAILED') . ' - ' . ($emailResult['error'] ?? ''));
            } catch (Exception $e) {
                // Log l'erreur mais ne pas faire échouer l'opération
                error_log('Erreur envoi email rejet: ' . $e->getMessage());
            }
        }

        return ['success' => true, 'message' => 'Proposition rejetée et email envoyé au citoyen'];
    }

    private function setCitizenProposalStatus($data) {
        error_log("🔄 setCitizenProposalStatus appelée avec: " . json_encode($data));
        $proposalId = $data['proposal_id'] ?? null;
        $status = $data['status'] ?? '';
        error_log("📝 ID: $proposalId, Status: $status");
        if (!$proposalId) { throw new Exception('ID de proposition manquant'); }
        if (!in_array($status, ['pending', 'rejected', 'approved'], true)) {
            throw new Exception('Statut invalide');
        }

        $citizenProposalsFile = DATA_PATH . '/propositions.json';
        if (!file_exists($citizenProposalsFile)) {
            throw new Exception('Fichier des propositions citoyennes introuvable');
        }
        $citizenData = json_decode(file_get_contents($citizenProposalsFile), true);
        $proposals = $citizenData['propositions'] ?? [];

        $found = false;
        error_log("🔍 Recherche de la proposition ID: $proposalId parmi " . count($proposals) . " propositions");
        foreach ($proposals as &$proposal) {
            error_log("🔍 Comparaison: '" . $proposal['id'] . "' === '$proposalId' ? " . ($proposal['id'] === $proposalId ? 'OUI' : 'NON'));
            if ($proposal['id'] === $proposalId) {
                $proposal['status'] = $status;
                $proposal['updated_at'] = date('Y-m-d H:i:s');
                $found = true;
                error_log("✅ Proposition trouvée et mise à jour");
                break;
            }
        }
        if (!$found) { 
            error_log("❌ Proposition non trouvée avec ID: $proposalId");
            throw new Exception('Proposition non trouvée');
        }
        $citizenData['propositions'] = $proposals;
        if (!file_put_contents($citizenProposalsFile, json_encode($citizenData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            throw new Exception('Erreur lors de la sauvegarde');
        }
        
        // Synchroniser le site public: retirer la carte si elle n'est plus approuvée
        $siteContentPath = DATA_PATH . '/site_content.json';
        if (file_exists($siteContentPath)) {
            $siteContent = json_decode(file_get_contents($siteContentPath), true);
            if (isset($siteContent['programme']['proposals']) && is_array($siteContent['programme']['proposals'])) {
                if (in_array($status, ['pending', 'rejected'], true)) {
                    $before = count($siteContent['programme']['proposals']);
                    $siteContent['programme']['proposals'] = array_values(array_filter(
                        $siteContent['programme']['proposals'],
                        function($p) use ($proposalId) {
                            return ($p['citizen_proposal_id'] ?? null) !== $proposalId;
                        }
                    ));
                    $after = count($siteContent['programme']['proposals']);
                    error_log("🧹 Synchronisation site: suppression cartes citoyennes liées ($before -> $after)");
                }
                // si status redevient approved, l'ajout est géré par approveProposal
            }
            file_put_contents($siteContentPath, json_encode($siteContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return ['success' => true, 'message' => 'Statut de la proposition mis à jour'];
    }

    private function pillarToColor($pillar) {
        switch ($pillar) {
            case 'proteger': return '#65ae99';
            case 'tisser': return '#fcc549';
            case 'dessiner': return '#4e9eb0';
            case 'ouvrir': return '#004a6d';
            default: return '#65ae99';
        }
    }
    
    // Les modals sont maintenant gérés par le modal unifié dans schema_admin_new.php
}
