<?php
// Endpoint AJAX pour gérer les propositions citoyennes
session_start();

// Chargement des services
require_once '../../forms/config.php';
require_once '../../forms/email-service.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Configuration
$PROPOSITIONS_FILE = '../../data/propositions.json';
$SITE_CONTENT_FILE = '../../data/site_content.json';

// Fonctions utilitaires
function loadPropositions() {
    global $PROPOSITIONS_FILE;
    if (file_exists($PROPOSITIONS_FILE)) {
        return json_decode(file_get_contents($PROPOSITIONS_FILE), true);
    }
    return ['propositions' => []];
}

function savePropositions($data) {
    global $PROPOSITIONS_FILE;
    $data['last_updated'] = date('c');
    return file_put_contents($PROPOSITIONS_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function loadSiteContent() {
    global $SITE_CONTENT_FILE;
    if (file_exists($SITE_CONTENT_FILE)) {
        return json_decode(file_get_contents($SITE_CONTENT_FILE), true);
    }
    return [];
}

function saveSiteContent($data) {
    global $SITE_CONTENT_FILE;
    return file_put_contents($SITE_CONTENT_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function updateStatistics($data) {
    $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    
    foreach ($data['propositions'] as $prop) {
        $stats['total']++;
        $stats[$prop['status']]++;
    }
    
    $data['statistics'] = $stats;
    return $data;
}

// Configuration des headers pour JSON
header('Content-Type: application/json');

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

// Récupération des données
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$proposalId = $input['proposalId'] ?? '';

if (empty($action) || empty($proposalId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres manquants']);
    exit;
}

$propositions_data = loadPropositions();

// Trouver la proposition
$proposition = null;
$proposition_index = -1;
foreach ($propositions_data['propositions'] as $index => $prop) {
    if ($prop['id'] === $proposalId) {
        $proposition = $prop;
        $proposition_index = $index;
        break;
    }
}

if (!$proposition) {
    http_response_code(404);
    echo json_encode(['error' => 'Proposition non trouvée']);
    exit;
}

try {
    switch ($action) {
        case 'update_status':
            $status = $input['status'] ?? '';
            if (!in_array($status, ['pending', 'approved', 'rejected'])) {
                throw new Exception('Statut invalide');
            }
            
            $propositions_data['propositions'][$proposition_index]['status'] = $status;
            $propositions_data['propositions'][$proposition_index]['updated_at'] = date('Y-m-d H:i:s');
            
            $propositions_data = updateStatistics($propositions_data);
            savePropositions($propositions_data);
            
            // Envoyer un email de notification au citoyen
            if ($status === 'approved' || $status === 'rejected') {
                $emailResult = EmailService::sendStatusUpdateEmail(
                    $proposition['data']['email'], 
                    $proposition, 
                    $status
                );
                logEmailAttempt($proposition['data']['email'], 'Mise à jour statut', $emailResult);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Statut mis à jour avec succès',
                'newStatus' => $status
            ]);
            break;
            
        case 'integrate':
            if ($proposition['status'] !== 'approved') {
                throw new Exception('Seules les propositions approuvées peuvent être intégrées');
            }
            
            // Ajouter au programme principal avec la structure attendue
            $site_content = loadSiteContent();
            
            // Déterminer le pilier selon la catégorie
            $pillar_mapping = [
                'Urbanisme & Logement' => 'dessiner',
                'Environnement & Nature' => 'proteger',
                'Mobilité & Transport' => 'dessiner',
                'Vie sociale & Solidarité' => 'tisser',
                'Éducation & Jeunesse' => 'ouvrir',
                'Santé & Bien-être' => 'proteger',
                'Culture & Sport' => 'tisser',
                'Économie & Commerce' => 'ouvrir',
                'Services publics' => 'proteger',
                'Autre' => 'tisser'
            ];
            
            $pillar_colors = [
                'proteger' => '#65ae99',
                'tisser' => '#fcc549', 
                'dessiner' => '#4e9eb0',
                'ouvrir' => '#004a6d'
            ];
            
            $category = $proposition['data']['categories'][0];
            $pillar = $pillar_mapping[$category] ?? 'tisser';
            $color = $pillar_colors[$pillar];
            
            // Générer un ID numérique pour le programme
            $max_id = 0;
            foreach ($site_content['programme']['proposals'] ?? [] as $existing_proposal) {
                if (isset($existing_proposal['id']) && is_numeric($existing_proposal['id']) && $existing_proposal['id'] > $max_id) {
                    $max_id = $existing_proposal['id'];
                }
            }
            $new_id = $max_id + 1;
            
            $new_proposal = [
                'id' => $new_id,
                'title' => $proposition['data']['titre'],
                'description' => $proposition['data']['description'],
                'pillar' => $pillar,
                'color' => $color,
                'items' => [], // Pas de bullet points pour les propositions citoyennes
                'source' => 'citoyenne',
                'citizen_proposal_id' => $proposition['id'],
                'citizen_contact' => [
                    'nom' => $proposition['data']['nom'],
                    'email' => $proposition['data']['email'],
                    'telephone' => $proposition['data']['telephone'] ?? '',
                    'commune' => $proposition['data']['commune'] ?? ''
                ],
                'date_added' => date('Y-m-d H:i:s')
            ];
            
            if (!isset($site_content['programme']['proposals'])) {
                $site_content['programme']['proposals'] = [];
            }
            $site_content['programme']['proposals'][] = $new_proposal;
            
            // Marquer comme intégrée
            $propositions_data['propositions'][$proposition_index]['integrated'] = true;
            $propositions_data['propositions'][$proposition_index]['integrated_at'] = date('Y-m-d H:i:s');
            $propositions_data['propositions'][$proposition_index]['programme_id'] = $new_proposal['id'];
            
            saveSiteContent($site_content);
            savePropositions($propositions_data);
            
            // Envoyer un email de notification au citoyen
            $emailResult = EmailService::sendStatusUpdateEmail(
                $proposition['data']['email'], 
                $proposition, 
                'integrated'
            );
            logEmailAttempt($proposition['data']['email'], 'Proposition intégrée', $emailResult);
            
            echo json_encode([
                'success' => true,
                'message' => 'Proposition intégrée au programme avec succès',
                'programmeId' => $new_proposal['id']
            ]);
            break;
            
        case 'edit_and_integrate':
            // Modifier et intégrer directement une proposition
            $proposition = $propositions_data['propositions'][$proposition_index];
            $new_data = $input['data'] ?? [];
            
            // Mettre à jour les données de la proposition
            $propositions_data['propositions'][$proposition_index]['data']['titre'] = $new_data['titre'] ?? $proposition['data']['titre'];
            $propositions_data['propositions'][$proposition_index]['data']['description'] = $new_data['description'] ?? $proposition['data']['description'];
            $propositions_data['propositions'][$proposition_index]['data']['categories'] = $new_data['categories'] ?? $proposition['data']['categories'];
            $propositions_data['propositions'][$proposition_index]['data']['beneficiaires'] = $new_data['beneficiaires'] ?? $proposition['data']['beneficiaires'];
            $propositions_data['propositions'][$proposition_index]['data']['cout'] = $new_data['cout'] ?? $proposition['data']['cout'];
            
            // Marquer comme approuvée et intégrée
            $propositions_data['propositions'][$proposition_index]['status'] = 'approved';
            $propositions_data['propositions'][$proposition_index]['integrated'] = true;
            $propositions_data['propositions'][$proposition_index]['integrated_at'] = date('Y-m-d H:i:s');
            
            // Intégrer au programme principal
            $site_content = loadSiteContent();
            
            // Déterminer le pilier selon la catégorie
            $pillar_mapping = [
                'Urbanisme & Logement' => 'dessiner',
                'Environnement & Nature' => 'proteger',
                'Mobilité & Transport' => 'dessiner',
                'Vie sociale & Solidarité' => 'tisser',
                'Éducation & Jeunesse' => 'ouvrir',
                'Santé & Bien-être' => 'proteger',
                'Culture & Sport' => 'tisser',
                'Économie & Commerce' => 'ouvrir',
                'Services publics' => 'proteger',
                'Autre' => 'tisser'
            ];
            
            $pillar_colors = [
                'proteger' => '#65ae99',
                'tisser' => '#fcc549', 
                'dessiner' => '#4e9eb0',
                'ouvrir' => '#004a6d'
            ];
            
            $category = $new_data['categories'][0] ?? $proposition['data']['categories'][0];
            $pillar = $pillar_mapping[$category] ?? 'tisser';
            $color = $pillar_colors[$pillar];
            
            // Générer un ID numérique pour le programme
            $max_id = 0;
            foreach ($site_content['programme']['proposals'] ?? [] as $existing_proposal) {
                if (isset($existing_proposal['id']) && is_numeric($existing_proposal['id']) && $existing_proposal['id'] > $max_id) {
                    $max_id = $existing_proposal['id'];
                }
            }
            $new_id = $max_id + 1;
            
            $new_proposal = [
                'id' => $new_id,
                'title' => $new_data['titre'] ?? $proposition['data']['titre'],
                'description' => $new_data['description'] ?? $proposition['data']['description'],
                'pillar' => $pillar,
                'color' => $color,
                'items' => [], // Pas de bullet points pour les propositions citoyennes
                'source' => 'citoyenne',
                'citizen_proposal_id' => $proposition['id'],
                'citizen_contact' => [
                    'nom' => $proposition['data']['nom'],
                    'email' => $proposition['data']['email'],
                    'telephone' => $proposition['data']['telephone'] ?? '',
                    'commune' => $proposition['data']['commune'] ?? ''
                ],
                'date_added' => date('Y-m-d H:i:s')
            ];
            
            if (!isset($site_content['programme']['proposals'])) {
                $site_content['programme']['proposals'] = [];
            }
            $site_content['programme']['proposals'][] = $new_proposal;
            
            // Marquer comme intégrée
            $propositions_data['propositions'][$proposition_index]['programme_id'] = $new_proposal['id'];
            
            saveSiteContent($site_content);
            savePropositions($propositions_data);
            
            // Envoyer un email de notification au citoyen
            $emailResult = EmailService::sendStatusUpdateEmail(
                $proposition['data']['email'], 
                $proposition, 
                'integrated'
            );
            logEmailAttempt($proposition['data']['email'], 'Proposition modifiée et intégrée', $emailResult);
            
            echo json_encode([
                'success' => true,
                'message' => 'Proposition modifiée et intégrée au programme avec succès',
                'programmeId' => $new_proposal['id']
            ]);
            break;
            
        case 'delete':
            array_splice($propositions_data['propositions'], $proposition_index, 1);
            $propositions_data = updateStatistics($propositions_data);
            savePropositions($propositions_data);
            
            echo json_encode([
                'success' => true,
                'message' => 'Proposition supprimée avec succès'
            ]);
            break;
            
        default:
            throw new Exception('Action non reconnue');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
