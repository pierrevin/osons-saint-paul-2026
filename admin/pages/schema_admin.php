<?php
// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/image_processor.php';

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Vérification CSRF simplifiée (pour le développement)
    // En production, utiliser une vérification plus robuste avec sessions persistantes
    if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token'])) {
        die('Erreur de sécurité - Token CSRF manquant');
    }
    
    $action = $_POST['action'];
    
    switch ($action) {
        case 'add_proposal':
            // Ajouter une nouvelle proposition
            $content = get_json_data('site_content.json');
            
            // Générer un nouvel ID
            $max_id = 0;
            foreach ($content['programme']['proposals'] as $proposal) {
                if ($proposal['id'] > $max_id) {
                    $max_id = $proposal['id'];
                }
            }
            $new_id = $max_id + 1;
            
            // Déterminer la couleur selon le pilier
            $pillar_colors = [
                'proteger' => '#65ae99',
                'tisser' => '#fcc549', 
                'dessiner' => '#4e9eb0',
                'ouvrir' => '#004a6d'
            ];
            
            $pillar = $_POST['pillar'] ?? 'proteger';
            $color = $pillar_colors[$pillar] ?? '#65ae99';
            
            // Traiter les points clés
            $items = [];
            if (!empty($_POST['items'])) {
                $items = array_filter(array_map('trim', explode("\n", $_POST['items'])));
            }
            
            // Créer la nouvelle proposition
            $new_proposal = [
                'id' => $new_id,
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'icon' => 'default',
                'color' => $color,
                'pillar' => $pillar,
                'citizen_proposal' => isset($_POST['citizen_proposal']),
                'items' => $items
            ];
            
            // Ajouter à la liste
            $content['programme']['proposals'][] = $new_proposal;
            
            // Sauvegarder
            if (save_json_data('site_content.json', $content)) {
                $_SESSION['success_message'] = 'Proposition ajoutée avec succès !';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la sauvegarde.';
            }
            
            // Rediriger pour éviter la resoumission
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
            break;
            
        case 'edit_proposal':
            // Modifier une proposition existante
            $content = get_json_data('site_content.json');
            $proposal_id = (int)$_POST['proposal_id'];
            
            // Trouver et modifier la proposition
            foreach ($content['programme']['proposals'] as &$proposal) {
                if ($proposal['id'] == $proposal_id) {
                    // Déterminer la couleur selon le pilier
                    $pillar_colors = [
                        'proteger' => '#65ae99',
                        'tisser' => '#fcc549',
                        'dessiner' => '#4e9eb0', 
                        'ouvrir' => '#004a6d'
                    ];
                    
                    $pillar = $_POST['pillar'] ?? 'proteger';
                    $color = $pillar_colors[$pillar] ?? '#65ae99';
                    
                    // Traiter les points clés
                    $items = [];
                    if (!empty($_POST['items'])) {
                        $items = array_filter(array_map('trim', explode("\n", $_POST['items'])));
                    }
                    
                    // Mettre à jour la proposition
                    $proposal['title'] = trim($_POST['title']);
                    $proposal['description'] = trim($_POST['description']);
                    $proposal['color'] = $color;
                    $proposal['pillar'] = $pillar;
                    $proposal['citizen_proposal'] = isset($_POST['citizen_proposal']);
                    $proposal['items'] = $items;
                    
                    break;
                }
            }
            
            // Sauvegarder
            if (save_json_data('site_content.json', $content)) {
                $_SESSION['success_message'] = 'Proposition modifiée avec succès !';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la sauvegarde.';
            }
            
            // Rediriger pour éviter la resoumission
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
            break;
            
        case 'delete_proposal':
            // Supprimer une proposition
            $content = get_json_data('site_content.json');
            $proposal_id = (int)$_POST['proposal_id'];
            
            // Supprimer la proposition
            $content['programme']['proposals'] = array_filter(
                $content['programme']['proposals'],
                function($proposal) use ($proposal_id) {
                    return $proposal['id'] != $proposal_id;
                }
            );
            
            // Sauvegarder
            if (save_json_data('site_content.json', $content)) {
                $_SESSION['success_message'] = 'Proposition supprimée avec succès !';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la sauvegarde.';
            }
            
            // Rediriger pour éviter la resoumission
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
            break;
            
        case 'update_hero':
            $content = get_json_data('site_content.json');
            $content['hero']['title'] = $_POST['title'] ?? '';
            $content['hero']['button_primary'] = $_POST['button_primary'] ?? '';
            $content['hero']['button_secondary'] = $_POST['button_secondary'] ?? '';
            
            // Gestion de l'upload d'image avec traitement automatique
            if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../uploads/';
                $processor = new ImageProcessor(85, 1920, 1080);
                
                $result = $processor->processImage($_FILES['background_image'], $upload_dir, 'hero-bg');
                
                if ($result['success']) {
                    $content['hero']['background_image'] = 'uploads/' . $result['filename'];
                    
                    // Créer une miniature
                    $processor->createThumbnail($result['path'], $upload_dir . '/thumbs', 300, 200);
                } else {
                    $_SESSION['error_message'] = 'Erreur lors du traitement de l\'image : ' . $result['error'];
                }
            }
            
            if (save_json_data('site_content.json', $content)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Section Hero mise à jour avec succès']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            exit;
            }
            break;
            
        case 'update_programme_section':
            $content = get_json_data('site_content.json');
            $content['programme']['title'] = $_POST['title'] ?? '';
            $content['programme']['subtitle'] = $_POST['subtitle'] ?? '';
            $content['programme']['description'] = $_POST['description'] ?? '';
            
            if (save_json_data('site_content.json', $content)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Section Programme mise à jour avec succès']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            exit;
            }
            break;
            
        case 'update_equipe_section':
            $content = get_json_data('site_content.json');
            $content['equipe']['title'] = $_POST['title'] ?? '';
            $content['equipe']['subtitle'] = $_POST['subtitle'] ?? '';
            $content['equipe']['description'] = $_POST['description'] ?? '';
            
            if (save_json_data('site_content.json', $content)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Section Équipe mise à jour avec succès']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            exit;
            }
            break;
            
        case 'reorder_proposals':
            // Réordonner les propositions
            $content = get_json_data('site_content.json');
            $order = json_decode($_POST['order'], true);
            
            if ($order && is_array($order)) {
                $proposals = $content['programme']['proposals'] ?? [];
                $reordered_proposals = [];
                
                foreach ($order as $id) {
                    foreach ($proposals as $proposal) {
                        if ($proposal['id'] == $id) {
                            $reordered_proposals[] = $proposal;
                            break;
                        }
                    }
                }
                
                $content['programme']['proposals'] = $reordered_proposals;
                
                if (save_json_data('site_content.json', $content)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erreur de sauvegarde']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Ordre invalide']);
            }
            exit;
            break;
            
        case 'reorder_members':
            // Réordonner les membres
            $content = get_json_data('site_content.json');
            $order = json_decode($_POST['order'], true);
            
            if ($order && is_array($order)) {
                $members = $content['equipe']['members'] ?? [];
                $reordered_members = [];
                
                foreach ($order as $id) {
                    foreach ($members as $member) {
                        if ($member['id'] == $id) {
                            $reordered_members[] = $member;
                            break;
                        }
                    }
                }
                
                $content['equipe']['members'] = $reordered_members;
                
                if (save_json_data('site_content.json', $content)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erreur de sauvegarde']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Ordre invalide']);
            }
            exit;
            break;
            
        case 'update_citation1':
            $content = get_json_data('site_content.json');
            $content['citations']['citation1']['text'] = $_POST['text'] ?? '';
            $content['citations']['citation1']['author'] = $_POST['author'] ?? '';
            
            // Gestion de l'upload d'image avec traitement automatique
            if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../uploads/';
                $processor = new ImageProcessor(85, 1920, 1080);
                
                $result = $processor->processImage($_FILES['background_image'], $upload_dir, 'citation1-bg');
                
                if ($result['success']) {
                    $content['citations']['citation1']['background_image'] = 'uploads/' . $result['filename'];
                    
                    // Créer une miniature
                    $processor->createThumbnail($result['path'], $upload_dir . '/thumbs', 300, 200);
                } else {
                    $_SESSION['error_message'] = 'Erreur lors du traitement de l\'image : ' . $result['error'];
                }
            }
            
            if (save_json_data('site_content.json', $content)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Citation 1 mise à jour avec succès']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            exit;
            }
            break;
            
        case 'update_citation2':
            $content = get_json_data('site_content.json');
            $content['citations']['citation2']['text'] = $_POST['text'] ?? '';
            $content['citations']['citation2']['author'] = $_POST['author'] ?? '';
            
            // Gestion de l'upload d'image
            if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $processor = new ImageProcessor(85, 1920, 1080);
                
                $result = $processor->processImage($_FILES['background_image'], $upload_dir, 'citation2-bg');
                
                if ($result['success']) {
                    $content['citations']['citation2']['background_image'] = 'uploads/' . $result['filename'];
                    
                    // Créer une miniature
                    $processor->createThumbnail($result['path'], $upload_dir . '/thumbs', 300, 200);
                } else {
                    $_SESSION['error_message'] = 'Erreur lors du traitement de l\'image : ' . $result['error'];
                }
            }
            
            if (save_json_data('site_content.json', $content)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Citation 2 mise à jour avec succès']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
            exit;
            }
            break;
            
        case 'update_rendez_vous_section':
            $content['rendez_vous']['h2'] = $_POST['h2'] ?? '';
            $content['rendez_vous']['h3'] = $_POST['h3'] ?? '';
            
            if (save_json_data('site_content.json', $content)) {
                $_SESSION['success_message'] = 'Section Rendez-vous mise à jour avec succès';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la mise à jour';
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
            break;
            
        case 'update_charte_section':
            $content = get_json_data('site_content.json');
            $content['charte']['h2'] = $_POST['h2'] ?? '';
            $content['charte']['h3'] = $_POST['h3'] ?? '';
            $content['charte']['intro_text'] = $_POST['intro_text'] ?? '';
            $content['charte']['intro_highlight'] = $_POST['intro_highlight'] ?? '';
            
            if (save_json_data('site_content.json', $content)) {
                $_SESSION['success_message'] = 'Section Charte mise à jour avec succès';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la mise à jour';
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
            break;
            
        case 'update_citation3':
            $content = get_json_data('site_content.json');
            $content['citations']['citation3']['text'] = $_POST['text'] ?? '';
            $content['citations']['citation3']['author'] = $_POST['author'] ?? '';
            
            // Gestion de l'upload d'image avec traitement automatique
            if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../uploads/';
                $processor = new ImageProcessor(85, 1920, 1080);
                
                $result = $processor->processImage($_FILES['background_image'], $upload_dir, 'citation3-bg');
                
                if ($result['success']) {
                    $content['citations']['citation3']['background_image'] = 'uploads/' . $result['filename'];
                    
                    // Créer une miniature
                    $processor->createThumbnail($result['path'], $upload_dir . '/thumbs', 300, 200);
                } else {
                    $_SESSION['error_message'] = 'Erreur lors du traitement de l\'image : ' . $result['error'];
                }
            }
            
            if (save_json_data('site_content.json', $content)) {
                $_SESSION['success_message'] = 'Citation 3 mise à jour avec succès';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la mise à jour';
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
            break;
            
        case 'update_citation4':
            $content = get_json_data('site_content.json');
            $content['citations']['citation4']['text'] = $_POST['text'] ?? '';
            $content['citations']['citation4']['author'] = $_POST['author'] ?? '';
            
            // Gestion de l'upload d'image
            if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $processor = new ImageProcessor(85, 1920, 1080);
                
                $result = $processor->processImage($_FILES['background_image'], $upload_dir, 'citation4-bg');
                
                if ($result['success']) {
                    $content['citations']['citation4']['background_image'] = 'uploads/' . $result['filename'];
                    
                    // Créer une miniature
                    $processor->createThumbnail($result['path'], $upload_dir . '/thumbs', 300, 200);
                } else {
                    $_SESSION['error_message'] = 'Erreur lors du traitement de l\'image : ' . $result['error'];
                }
            }
            
            if (save_json_data('site_content.json', $content)) {
                $_SESSION['success_message'] = 'Citation 4 mise à jour avec succès';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la mise à jour';
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
            break;
            
        case 'update_idees_section':
            $content = get_json_data('site_content.json');
            $content['idees']['title'] = $_POST['title'] ?? '';
            $content['idees']['subtitle'] = $_POST['subtitle'] ?? '';
            $content['idees']['description'] = $_POST['description'] ?? '';
            $content['idees']['contact_email'] = $_POST['contact_email'] ?? '';
            $content['idees']['contact_phone'] = $_POST['contact_phone'] ?? '';
            
            if (save_json_data('site_content.json', $content)) {
                $_SESSION['success_message'] = 'Section Idées mise à jour avec succès';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la mise à jour';
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
            break;
            
        case 'update_mediatheque_section':
            $content = get_json_data('site_content.json');
            $content['mediatheque']['title'] = $_POST['title'] ?? '';
            $content['mediatheque']['subtitle'] = $_POST['subtitle'] ?? '';
            $content['mediatheque']['description'] = $_POST['description'] ?? '';
            $content['mediatheque']['drive_url'] = $_POST['drive_url'] ?? '';
            
            if (save_json_data('site_content.json', $content)) {
                $_SESSION['success_message'] = 'Section Médiathèque mise à jour avec succès';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la mise à jour';
            }
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
            break;
            
        case 'update_member':
            // Modifier un membre d'équipe
            $content = get_json_data('site_content.json');
            $member_id = $_POST['id'] ?? null;
            
            // Log de debug
            error_log('update_member - member_id: ' . $member_id);
            error_log('update_member - POST data: ' . print_r($_POST, true));
            
            if ($member_id) {
                foreach ($content['equipe']['members'] as &$member) {
                    if ($member['id'] == $member_id) {
                        $member['name'] = $_POST['name'] ?? '';
                        $member['role'] = $_POST['role'] ?? '';
                        $member['description'] = $_POST['description'] ?? '';
                        
                        // Gérer l'upload d'image
                        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $upload_dir = '../../uploads/';
                            if (!is_dir($upload_dir)) {
                                mkdir($upload_dir, 0755, true);
                            }
                            
                            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                            $new_filename = 'member_' . $member_id . '_' . time() . '.' . $file_extension;
                            $upload_path = $upload_dir . $new_filename;
                            
                            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                                $member['image'] = 'uploads/' . $new_filename;
                            }
                        } else {
                            // Garder l'image existante si pas de nouvel upload
                            $member['image'] = $member['image'] ?? '';
                        }
                        break;
                    }
                }
                
                if (save_json_data('site_content.json', $content)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Membre mis à jour avec succès']);
                    exit;
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde']);
                    exit;
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID membre manquant']);
                exit;
            }
            break;
            
        case 'add_member':
            // Ajouter un nouveau membre
            $content = get_json_data('site_content.json');
            
            $max_id = 0;
            foreach ($content['equipe']['members'] as $member) {
                if ($member['id'] > $max_id) {
                    $max_id = $member['id'];
                }
            }
            $new_id = $max_id + 1;
            
            $new_member = [
                'id' => $new_id,
                'name' => $_POST['name'] ?? '',
                'role' => $_POST['role'] ?? '',
                'description' => $_POST['description'] ?? '',
                'image' => ''
            ];
            
            // Gérer l'upload d'image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../../uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $new_filename = 'member_' . $new_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $new_member['image'] = 'uploads/' . $new_filename;
                }
            }
            
            $content['equipe']['members'][] = $new_member;
            
            // Log de debug
            error_log('add_member - Nouveau membre: ' . print_r($new_member, true));
            error_log('add_member - Nombre total de membres: ' . count($content['equipe']['members']));
            
            if (save_json_data('site_content.json', $content)) {
                error_log('add_member - Sauvegarde réussie');
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Membre ajouté avec succès']);
                exit;
            } else {
                error_log('add_member - Erreur de sauvegarde');
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde']);
                exit;
            }
            break;
            
        case 'delete_member':
            // Supprimer un membre d'équipe
            $content = get_json_data('site_content.json');
            $member_id = $_POST['member_id'] ?? null;
            
            if ($member_id) {
                // Trouver et supprimer le membre
                foreach ($content['equipe']['members'] as $key => $member) {
                    if ($member['id'] == $member_id) {
                        unset($content['equipe']['members'][$key]);
                        // Réindexer le tableau
                        $content['equipe']['members'] = array_values($content['equipe']['members']);
                        break;
                    }
                }
                
                if (save_json_data('site_content.json', $content)) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Membre supprimé avec succès']);
                    exit;
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
                    exit;
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID membre manquant']);
                exit;
            }
            break;
            
        case 'update_event':
            // Ajouter ou modifier un événement (section rendez_vous)
            $content = get_json_data('site_content.json');
            $event_id = $_POST['id'] ?? null;
            
            if (!isset($content['rendez_vous']['events']) || !is_array($content['rendez_vous']['events'])) {
                $content['rendez_vous']['events'] = [];
            }
            
            if ($event_id) {
                // Modification d'un événement existant
                foreach ($content['rendez_vous']['events'] as &$event) {
                    if ($event['id'] == $event_id) {
                        $event['title'] = $_POST['title'] ?? '';
                        $event['description'] = $_POST['description'] ?? '';
                        $event['date'] = $_POST['date'] ?? '';
                        $event['location'] = $_POST['location'] ?? '';
                        break;
                    }
                }
            } else {
                // Ajout d'un nouvel événement
                $max_id = 0;
                foreach ($content['rendez_vous']['events'] as $event) {
                    if (($event['id'] ?? 0) > $max_id) {
                        $max_id = $event['id'];
                    }
                }
                $new_id = $max_id + 1;
                
                $new_event = [
                    'id' => $new_id,
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'date' => $_POST['date'] ?? '',
                    'location' => $_POST['location'] ?? ''
                ];
                
                $content['rendez_vous']['events'][] = $new_event;
            }
            
            if (save_json_data('site_content.json', $content)) {
                // Préparer l'événement renvoyé
                $returned = null;
                if ($event_id) {
                    foreach ($content['rendez_vous']['events'] as $ev) {
                        if (($ev['id'] ?? null) == $event_id) { $returned = $ev; break; }
                    }
                } else {
                    $returned = $new_event;
                }
                // Retourner une réponse JSON pour les requêtes AJAX
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Événement enregistré', 'event' => $returned]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde']);
                exit;
            }
            break;
        
        case 'delete_event':
            // Supprimer un événement (section rendez_vous)
            $content = get_json_data('site_content.json');
            $event_id = $_POST['event_id'] ?? null;
            if (!$event_id) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID événement manquant']);
                exit;
            }
            if (!isset($content['rendez_vous']['events']) || !is_array($content['rendez_vous']['events'])) {
                $content['rendez_vous']['events'] = [];
            }
            foreach ($content['rendez_vous']['events'] as $index => $event) {
                if (($event['id'] ?? null) == $event_id) {
                    unset($content['rendez_vous']['events'][$index]);
                }
            }
            // Réindexer
            $content['rendez_vous']['events'] = array_values($content['rendez_vous']['events']);
            if (save_json_data('site_content.json', $content)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Événement supprimé']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
                exit;
            }
            break;
            
        case 'update_principle':
            // Ajouter ou modifier un principe de la charte
            $content = get_json_data('site_content.json');
            $id = $_POST['id'] ?? null;
            if ($id) {
                foreach ($content['charte']['principles'] as &$p) {
                    if ($p['id'] == $id) {
                        $p['title'] = $_POST['title'] ?? '';
                        $p['description'] = $_POST['description'] ?? '';
                        $p['thematique'] = $_POST['thematique'] ?? ($p['thematique'] ?? ($p['source'] ?? ''));
                        break;
                    }
                }
                $returned = ['id' => (int)$id, 'title' => $_POST['title'] ?? '', 'description' => $_POST['description'] ?? '', 'thematique' => $_POST['thematique'] ?? ''];
            } else {
                $max_id = 0;
                foreach ($content['charte']['principles'] as $principle) {
                    if (($principle['id'] ?? 0) > $max_id) {
                        $max_id = $principle['id'];
                    }
                }
                $new_id = $max_id + 1;
                $new_principle = [
                    'id' => $new_id,
                    'title' => $_POST['title'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'thematique' => $_POST['thematique'] ?? ''
                ];
                $content['charte']['principles'][] = $new_principle;
                $returned = $new_principle;
            }
            if (save_json_data('site_content.json', $content)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Principe enregistré', 'principle' => $returned]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la sauvegarde']);
                exit;
            }
            
        case 'delete_principle':
            // Supprimer un principe de la charte
            $content = get_json_data('site_content.json');
            $principle_id = $_POST['principle_id'] ?? null;
            if (!$principle_id) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'ID principe manquant']);
                exit;
            }
            foreach ($content['charte']['principles'] as $idx => $p) {
                if (($p['id'] ?? null) == $principle_id) {
                    unset($content['charte']['principles'][$idx]);
                }
            }
            $content['charte']['principles'] = array_values($content['charte']['principles']);
            if (save_json_data('site_content.json', $content)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Principe supprimé']);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
                exit;
            }
            break;
    }
}

// Charger les données du site
$content = get_json_data('site_content.json');
$equipe_count = count($content['equipe']['members'] ?? []);
$programme_count = count($content['programme']['proposals'] ?? []);
$rendez_vous_count = count($content['rendez_vous']['events'] ?? []);
$charte_count = count($content['charte']['principles'] ?? []);
$idees_count = count($content['idees']['suggestions'] ?? []);
$mediatheque_count = count($content['mediatheque']['items'] ?? []);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Vue Schématique</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Cropper.js pour le recadrage d'images -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <style>
        .schema-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .schema-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: linear-gradient(135deg, #2d5a3d, #4a7c59);
            color: white;
            border-radius: 12px;
        }
        
        .schema-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2.5rem;
        }
        
        .schema-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .blocks-grid {
            display: flex;
            flex-direction: column;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .block-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
            width: 100%;
        }
        
        .block-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
            border-color: #2d5a3d;
        }
        
        .block-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .block-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #2d5a3d, #4a7c59);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        .block-title {
            flex: 1;
        }
        
        .block-title h3 {
            margin: 0 0 0.25rem 0;
            color: #1f2937;
            font-size: 1.25rem;
        }
        
        .block-count {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .block-preview {
            margin: 1rem 0;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 4px solid #2d5a3d;
        }
        
        .block-preview h4 {
            margin: 0 0 0.5rem 0;
            color: #374151;
            font-size: 0.9rem;
        }
        
        .block-preview p {
            margin: 0;
            color: #6b7280;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        
        .block-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn-block {
            flex: 1;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #2d5a3d;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1e3d2a;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .status-indicator {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #10b981;
        }
        
        .status-indicator.warning {
            background: #f59e0b;
        }
        
        .status-indicator.error {
            background: #ef4444;
        }
        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2d5a3d;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .block-quote {
            border-left: 4px solid #f59e0b;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
        }
        
        .block-quote .block-icon {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .block-quote .block-preview {
            background: rgba(255, 255, 255, 0.7);
            border-left: 4px solid #d97706;
        }
        
        .components-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .component-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.2s ease;
        }
        
        .component-card:hover {
            background: #f1f5f9;
            border-color: #2d5a3d;
        }
        
        .component-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .component-description {
            color: #64748b;
            font-size: 0.8rem;
            line-height: 1.4;
        }
        
        .component-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
        
        .btn-component {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-component.edit {
            background: #3b82f6;
            color: white;
        }
        
        .btn-component.edit:hover {
            background: #2563eb;
        }
        
        .btn-component.delete {
            background: #ef4444;
            color: white;
        }
        
        .btn-component.delete:hover {
            background: #dc2626;
        }
        
        .member-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
        }
        
        .proposal-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-bottom: 0.5rem;
        }
        
        .event-date {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        
        /* Styles pour les modals */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .modal-container {
            background: white;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #1f2937;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .form-help {
            color: #6b7280;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        
        .current-image-preview {
            margin: 10px 0;
            padding: 10px;
            border: 2px dashed #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
        }
        
        .image-thumbnail {
            max-width: 200px;
            max-height: 120px;
            width: auto;
            height: auto;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: block;
            margin-bottom: 8px;
        }
        
        .image-placeholder {
            width: 200px;
            height: 120px;
            background: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 6px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        
        .image-placeholder i {
            font-size: 2rem;
            margin-bottom: 8px;
            opacity: 0.5;
        }
        
        .modal-large {
            max-width: 800px;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #2d5a3d;
        }
        
        .form-section h4 {
            margin: 0 0 1rem 0;
            color: #1f2937;
            font-size: 1.1rem;
        }
        
        .form-section h4 i {
            color: #2d5a3d;
            margin-right: 0.5rem;
        }
        
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            font-size: 0.9rem;
        }
        
        select:focus {
            outline: none;
            border-color: #2d5a3d;
            box-shadow: 0 0 0 3px rgba(45, 90, 61, 0.1);
        }
        
        input[type="checkbox"] {
            margin-right: 0.5rem;
            vertical-align: middle;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-group label {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Formulaires intégrés dans les sections */
        .block-edit-form {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .block-edit-form .form-group {
            margin-bottom: 1rem;
        }
        
        .block-edit-form label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }
        
        .block-edit-form input[type="text"],
        .block-edit-form input[type="email"],
        .block-edit-form textarea,
        .block-edit-form select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        
        .block-edit-form input[type="text"]:focus,
        .block-edit-form input[type="email"]:focus,
        .block-edit-form textarea:focus,
        .block-edit-form select:focus {
            outline: none;
            border-color: #2d5a3d;
            box-shadow: 0 0 0 3px rgba(45, 90, 61, 0.1);
        }
        
        .form-actions {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Accordéon pour les sections */
        .block-header {
            cursor: pointer;
            user-select: none;
        }
        
        .block-header:hover {
            background-color: #f9fafb;
            border-radius: 8px;
            transition: background-color 0.2s;
        }
        
        .block-content {
            display: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .block-content.expanded {
            display: block;
        }
        
        .block-edit-form {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .block-header .expand-icon {
            margin-left: auto;
            transition: transform 0.3s ease;
            color: #6b7280;
        }
        
        .block-header.expanded .expand-icon {
            transform: rotate(180deg);
        }
        
        /* Onglets pour les propositions */
        .proposals-tabs {
            margin-top: 2rem;
        }
        
        .tab-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .tab-button {
            background: none;
            border: none;
            padding: 1rem 1.5rem;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            color: #6b7280;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .tab-button:hover {
            color: #2d5a3d;
            background: rgba(45, 90, 61, 0.05);
        }
        
        .tab-button.active {
            color: #2d5a3d;
            border-bottom-color: #2d5a3d;
            background: rgba(45, 90, 61, 0.1);
        }
        
        .citizen-count {
            background: #ec654f;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.8rem;
            min-width: 20px;
            text-align: center;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Conteneur des propositions citoyennes */
        .citizen-proposals-container {
            background: #f9fafb;
            border-radius: 10px;
            padding: 1.5rem;
        }
        
        .citizen-proposals-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .citizen-proposals-header h4 {
            margin: 0;
            color: #2d5a3d;
            font-size: 1.1rem;
        }
        
        .citizen-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
            border-radius: 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.3s ease;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-primary {
            background: #2d5a3d;
            color: white;
        }
        
        .btn-sm:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        
        .citizen-proposals-list {
            min-height: 200px;
        }
        
        .loading-message {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
        
        .citizen-proposal-card {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #ec654f;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .citizen-proposal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.5rem;
        }
        
        .citizen-proposal-title {
            font-weight: 600;
            color: #2d5a3d;
            margin: 0;
        }
        
        .citizen-proposal-status {
            padding: 0.2rem 0.6rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .citizen-proposal-meta {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .citizen-proposal-description {
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 1rem;
        }
        
        .citizen-proposal-details {
            margin: 1rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 3px solid var(--coral);
        }
        
        .detail-row {
            margin: 0.5rem 0;
            font-size: 0.9rem;
            color: #495057;
        }
        
        .detail-row strong {
            color: #2d5a3d;
        }
        
        .citizen-proposal-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 0.3rem 0.6rem;
            border: none;
            border-radius: 15px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-integrate {
            background: #ec654f;
            color: white;
        }
        
        .btn-action:hover {
            transform: translateY(-1px);
        }
        
        /* Filtres pour les propositions citoyennes */
        .citizen-filters {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 0.4rem 0.8rem;
            border: 2px solid #e5e7eb;
            background: white;
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover {
            border-color: var(--coral);
            background: rgba(236, 101, 79, 0.05);
        }
        
        .filter-btn.active {
            background: var(--coral);
            color: white;
            border-color: var(--coral);
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Badge pour les propositions citoyennes */
        .citizen-badge {
            background: #ec654f;
            color: white;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 0.5rem;
            display: inline-block;
        }
        
        /* Indicateurs rapides */
        .admin-indicators {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            border: 1px solid #dee2e6;
        }
        
        .indicator-card {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
        }
        
        .indicator-card:hover {
            transform: translateY(-2px);
            border-color: var(--coral);
            box-shadow: 0 4px 12px rgba(236, 101, 79, 0.2);
            text-decoration: none;
            color: inherit;
        }
        
        .indicator-icon {
            font-size: 2rem;
            margin-right: 1rem;
            width: 50px;
            text-align: center;
        }
        
        .indicator-content {
            flex: 1;
        }
        
        .indicator-title {
            font-weight: 600;
            color: #2d5a3d;
            margin-bottom: 0.25rem;
        }
        
        .indicator-subtitle {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        #citizen-indicator-count {
            color: var(--coral);
            font-weight: bold;
        }
        
        /* Modal de modification */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }
        
        .modal-content {
            background: white;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #2d5a3d;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6c757d;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-close:hover {
            color: #dc3545;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        .modal-body .form-group {
            margin-bottom: 1rem;
        }
        
        .modal-body label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d5a3d;
        }
        
        .modal-body input,
        .modal-body textarea,
        .modal-body select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .modal-body input:focus,
        .modal-body textarea:focus,
        .modal-body select:focus {
            outline: none;
            border-color: var(--coral);
        }
        
        .modal-body select[multiple] {
            height: 120px;
        }
        
        /* Cartes d'ajout */
        .add-card {
            border: 2px dashed #d1d5db;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: 200px;
        }
        
        .add-card:hover {
            border-color: #2d5a3d;
            background: #f0fdf4;
            transform: translateY(-2px);
        }
        
        .add-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #2d5a3d, #4a7c59);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .add-card .component-title {
            color: #2d5a3d;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .add-card .component-description {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        /* Glisser-déposer */
        .drag-handle {
            position: absolute;
            top: 8px;
            right: 8px;
            color: #9ca3af;
            cursor: grab;
            padding: 4px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .drag-handle:hover {
            color: #6b7280;
            background: rgba(0, 0, 0, 0.05);
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        .component-card {
            position: relative;
            transition: all 0.3s ease;
        }
        
        .component-card.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
            z-index: 1000;
        }
        
        .component-card.drag-over {
            border: 2px dashed #2d5a3d;
            background: rgba(45, 90, 61, 0.05);
        }
        
        .components-grid {
            position: relative;
            min-height: 200px;
        }
        
        .components-grid::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 1;
        }
        
        .drop-indicator {
            width: 4px;
            height: 60px;
            background: linear-gradient(180deg, #2d5a3d 0%, #4a7c59 100%);
            border-radius: 2px;
            margin: 5px 0;
            opacity: 0;
            transition: all 0.15s ease;
            position: relative;
            box-shadow: 0 0 15px rgba(45, 90, 61, 0.6);
            flex-shrink: 0;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .drop-indicator.active {
            opacity: 1;
            transform: scaleY(1.1);
        }
        
        .drop-indicator::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            background: #2d5a3d;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .drop-indicator::after {
            content: '';
            position: absolute;
            left: -4px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
        }
        
        .components-grid.drag-active {
            gap: 20px;
        }
        
        .component-card.drag-placeholder {
            opacity: 0.3;
            transform: scale(0.95);
        }
        
        
        /* Bouton de sauvegarde flottant */
        .floating-save-btn {
            position: fixed;
            bottom: 40px;
            right: 40px;
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 18px 28px;
            font-size: 16px;
            font-weight: 700;
            box-shadow: 0 8px 32px rgba(5, 150, 105, 0.4);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            display: none;
            align-items: center;
            gap: 12px;
            min-width: 180px;
            justify-content: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .floating-save-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 40px rgba(5, 150, 105, 0.5);
            background: linear-gradient(135deg, #047857, #059669);
        }
        
        .floating-save-btn:active {
            transform: translateY(-1px) scale(0.98);
        }
        
        .floating-save-btn.saving {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            animation: pulse 1.5s infinite;
        }
        
        .floating-save-btn.success {
            background: linear-gradient(135deg, #10b981, #059669);
            animation: success-bounce 0.6s ease-out;
        }
        
        .floating-save-btn.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            animation: error-shake 0.5s ease-out;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @keyframes success-bounce {
            0% { transform: translateY(-3px) scale(1.02); }
            50% { transform: translateY(-6px) scale(1.05); }
            100% { transform: translateY(-3px) scale(1.02); }
        }
        
        @keyframes error-shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .floating-save-btn.visible {
            display: flex;
        }
        
        .floating-save-btn i {
            font-size: 18px;
        }
        
        /* Auto-save indicator */
        .auto-save-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3);
            z-index: 1001;
            display: none;
            align-items: center;
            gap: 8px;
        }
        
        .auto-save-indicator.visible {
            display: flex;
        }
        
        /* Notifications toast */
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px 20px;
            font-size: 14px;
            font-weight: 500;
            z-index: 1002;
            display: none;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            min-width: 300px;
            animation: slideInRight 0.3s ease-out;
        }
        
        .toast-notification.visible {
            display: flex;
        }
        
        .toast-notification.success {
            border-left: 4px solid #10b981;
            background: #f0fdf4;
            color: #166534;
        }
        
        .toast-notification.error {
            border-left: 4px solid #ef4444;
            background: #fef2f2;
            color: #991b1b;
        }
        
        .toast-notification.warning {
            border-left: 4px solid #f59e0b;
            background: #fffbeb;
            color: #92400e;
        }
        
        .toast-notification i {
            font-size: 18px;
        }
        
        .toast-notification .toast-content {
            flex: 1;
        }
        
        .toast-notification .toast-close {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .toast-notification .toast-close:hover {
            background: rgba(0, 0, 0, 0.05);
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .auto-save-indicator i {
            font-size: 16px;
        }
        
        .pillar-option {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 4px;
            margin-bottom: 0.25rem;
        }
        
        .pillar-option:hover {
            background: #f3f4f6;
        }
        
        /* ===== MODAL DE RECADRAGE D'IMAGE ===== */
        #cropImageModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        #cropImageModal .modal-content {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-height: 90vh;
            display: flex;
            flex-direction: column;
        }
        
        #cropImageModal .modal-body {
            overflow-y: auto;
            padding: 1.5rem;
        }
        
        #cropImageModal .modal-close {
            background: none;
            border: none;
            font-size: 2rem;
            cursor: pointer;
            color: #666;
            line-height: 1;
            padding: 0;
            width: 30px;
            height: 30px;
        }
        
        #cropImageModal .modal-close:hover {
            color: #ec654f;
        }
        
        /* Cropper.js container */
        .cropper-container {
            max-height: 400px;
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-container">
        <!-- Sidebar vertical de navigation -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <img src="../../uploads/Osons1.png" alt="Logo" class="sidebar-logo" />
                <h2>Administration</h2>
            </div>
            <ul class="sidebar-menu">
                <li class="menu-item"><a href="#" onclick="selectSection('hero'); return false;"><i class="fas fa-home"></i> Hero</a></li>
                <li class="menu-item"><a href="#" onclick="selectSection('programme'); return false;"><i class="fas fa-list-alt"></i> Programme</a></li>
                <li class="menu-item"><a href="#" onclick="selectSection('citation1'); return false;"><i class="fas fa-quote-left"></i> Transition 1</a></li>
                <li class="menu-item"><a href="#" onclick="selectSection('equipe'); return false;"><i class="fas fa-users"></i> Équipe</a></li>
                <li class="menu-item"><a href="#" onclick="selectSection('citation2'); return false;"><i class="fas fa-quote-left"></i> Transition 2</a></li>
                <li class="menu-item"><a href="#" onclick="selectSection('rendez_vous'); return false;"><i class="fas fa-calendar"></i> Rendez-vous</a></li>
                <li class="menu-item"><a href="#" onclick="selectSection('citation3'); return false;"><i class="fas fa-quote-left"></i> Transition 3</a></li>
                <li class="menu-item"><a href="#" onclick="selectSection('charte'); return false;"><i class="fas fa-handshake"></i> Charte</a></li>
                <li class="menu-item"><a href="#" onclick="selectSection('citation4'); return false;"><i class="fas fa-quote-left"></i> Transition 4</a></li>
                <li class="menu-item"><a href="#" onclick="selectSection('idees'); return false;"><i class="fas fa-lightbulb"></i> Idées</a></li>
                <li class="menu-item"><a href="#" onclick="selectSection('mediatheque'); return false;"><i class="fas fa-photo-video"></i> Médiathèque</a></li>
                <li class="menu-item"><a href="#" onclick="selectTransitionsAll(); return false;"><i class="fas fa-quote-right"></i> Transitions (toutes)</a></li>
                <li class="menu-item"><a href="#" onclick="switchTab('citizen-proposals'); return false;"><i class="fas fa-user-edit"></i> Propositions</a></li>
                <li class="menu-item"><a href="propositions-analytics.php" target="_blank"><i class="fas fa-chart-bar"></i> Analyse</a></li>
            </ul>
            <div class="sidebar-footer">
                <a class="logout-btn" href="../index.php"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>
        </aside>
        <?php if (isset($_SESSION['success_message'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast('<?= addslashes($_SESSION['success_message']) ?>', 'success');
                });
            </script>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast('<?= addslashes($_SESSION['error_message']) ?>', 'error');
                });
            </script>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
        

        <!-- Main Content -->
        <main class="admin-main">
            <div class="schema-container">
                <!-- Header supprimé (obsolète) -->

                <!-- Indicateurs rapides supprimés (doublon) -->

                <!-- Workspace unique: zone d'affichage dynamique des sections -->
                <div id="adminWorkspace" class="admin-workspace">
                    <div class="workspace-hint" style="padding: 1rem; color: #6c757d; font-style: italic;">
                        Cliquez sur une section dans le menu de gauche pour ouvrir les modifications.
                    </div>
                </div>


                <!-- Blocks Grid - Dans l'ordre de la page -->
                <div class="blocks-grid" id="legacyBlocks" style="display:none;">
                    <!-- 1. Hero Section -->
                    <div class="block-card">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('hero')">
                            <div class="block-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="block-title">
                                <h3>1. Section Hero</h3>
                                <div class="block-count">Page d'accueil</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="hero-content">
                            <!-- Formulaire de modification -->
                            <div class="block-edit-form">
                                <form method="POST" action="" id="hero-form-element" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_hero">
                                    
                                    <div class="form-group">
                                        <label for="hero_title">Titre principal *</label>
                                        <input type="text" id="hero_title" name="title" value="<?= htmlspecialchars($content['hero']['title'] ?? '') ?>" required onchange="markFormChanged('hero')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="hero_button_primary">Bouton principal</label>
                                        <input type="text" id="hero_button_primary" name="button_primary" value="<?= htmlspecialchars($content['hero']['button_primary'] ?? '') ?>" onchange="markFormChanged('hero')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="hero_button_secondary">Bouton secondaire</label>
                                        <input type="text" id="hero_button_secondary" name="button_secondary" value="<?= htmlspecialchars($content['hero']['button_secondary'] ?? '') ?>" onchange="markFormChanged('hero')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="hero_background_image">Image de fond</label>
                                        <?php 
                                        $hero_image = $content['hero']['background_image'] ?? 'hero-bg.jpg';
                                        if (file_exists(__DIR__ . '/../../' . $hero_image)): ?>
                                        <div class="current-image-preview">
                                            <img src="../<?= htmlspecialchars($hero_image) ?>" alt="Image actuelle" class="image-thumbnail">
                                            <small class="form-help">Image actuelle : <?= htmlspecialchars($hero_image) ?></small>
                                        </div>
                                        <?php else: ?>
                                        <small class="form-help">Aucune image trouvée : <?= htmlspecialchars($hero_image) ?></small>
                                        <?php endif; ?>
                                        <input type="file" id="hero_background_image" name="background_image" accept="image/*" onchange="markFormChanged('hero')" style="width: 100%; margin-top: 10px;">
                                    </div>
                                    
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Programme Section -->
                    <div class="block-card">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('programme')">
                            <div class="block-icon">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <div class="block-title">
                                <h3>2. Section Programme</h3>
                                <div class="block-count"><?= $programme_count ?> propositions + propositions citoyennes</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="programme-content">
                            <!-- Formulaire de modification des titres -->
                            <div class="block-edit-form">
                                <form method="POST" action="" id="programme-form-element">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_programme_section">
                                    
                                    <div class="form-group">
                                        <label for="programme_title">Titre principal (H2) *</label>
                                        <input type="text" id="programme_title" name="title" value="<?= htmlspecialchars($content['programme']['title'] ?? 'Notre Programme') ?>" required onchange="markFormChanged('programme')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="programme_subtitle">Sous-titre (H3)</label>
                                        <input type="text" id="programme_subtitle" name="subtitle" value="<?= htmlspecialchars($content['programme']['subtitle'] ?? 'Osons intégrer vos idées') ?>" onchange="markFormChanged('programme')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="programme_description">Description</label>
                                        <textarea id="programme_description" name="description" rows="3" onchange="markFormChanged('programme')"><?= htmlspecialchars($content['programme']['description'] ?? 'Un programme co-construit avec les habitants de Saint-Paul') ?></textarea>
                                    </div>
                                    
                                </form>
                            </div>
                            
                            <!-- Onglets pour les propositions -->
                            <div class="proposals-tabs">
                                <div class="tab-buttons">
                                    <button class="tab-button active" onclick="switchTab('programme-proposals')">
                                        📋 Propositions du programme (<?= $programme_count ?>)
                                    </button>
                                    <button class="tab-button" onclick="switchTab('citizen-proposals')">
                                        💡 Propositions citoyennes
                                        <span class="citizen-count" id="citizen-count">0</span>
                                    </button>
                                </div>
                                
                                <!-- Onglet Propositions du programme -->
                                <div class="tab-content active" id="programme-proposals">
                            <div class="components-grid" id="programme-grid">
                                <?php foreach($content['programme']['proposals'] ?? [] as $proposal): ?>
                                    <div class="component-card" draggable="true" data-id="<?= $proposal['id'] ?>">
                                        <div class="drag-handle">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                        <div class="proposal-color" style="background-color: <?= htmlspecialchars($proposal['color'] ?? '#2d5a3d') ?>"></div>
                                        <div class="component-title">
                                            <?= htmlspecialchars($proposal['title']) ?>
                                            <?php if (isset($proposal['source']) && $proposal['source'] === 'citoyenne'): ?>
                                                <span class="citizen-badge">💡 Citoyenne</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="component-description">
                                            <?= htmlspecialchars($proposal['description']) ?><br>
                                            <small>
                                                <?php foreach($proposal['items'] ?? [] as $item): ?>
                                                    • <?= htmlspecialchars($item) ?><br>
                                                <?php endforeach; ?>
                                            </small>
                                        </div>
                                        <div class="component-actions">
                                            <button class="btn-component edit" onclick="openEditProgrammeProposalModal(<?= $proposal['id'] ?>)">
                                                <i class="fas fa-edit"></i> Modifier
                                            </button>
                                            <button class="btn-component delete" onclick="deleteProposal(<?= $proposal['id'] ?>)">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Carte d'ajout de proposition -->
                                <div class="component-card add-card" onclick="openAddProposalModal()">
                                    <div class="add-icon">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="component-title">Ajouter une proposition</div>
                                    <div class="component-description">
                                        Cliquez pour créer une nouvelle proposition
                                    </div>
                                </div>
                                    </div>
                                </div>
                                
                                <!-- Onglet Propositions citoyennes -->
                                <div class="tab-content" id="citizen-proposals">
                                    <div class="citizen-proposals-container">
                                        <div class="citizen-proposals-header">
                                            <h4>💡 Propositions citoyennes</h4>
                                            <div class="citizen-actions">
                                                <a href="../../forms/proposition-citoyenne.php" target="_blank" class="btn btn-secondary btn-sm">
                                                    📝 Nouveau formulaire
                                                </a>
                                                <button onclick="loadCitizenProposals()" class="btn btn-primary btn-sm">
                                                    🔄 Actualiser
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Filtres -->
                                        <div class="citizen-filters">
                                            <button class="filter-btn active" onclick="filterProposals('all')">Toutes</button>
                                            <button class="filter-btn" onclick="filterProposals('pending')">En attente</button>
                                            <button class="filter-btn" onclick="filterProposals('approved')">Approuvées</button>
                                            <button class="filter-btn" onclick="filterProposals('rejected')">Rejetées</button>
                                        </div>
                                        
                                        <div class="citizen-proposals-list" id="citizen-proposals-list">
                                            <!-- Les propositions citoyennes seront chargées ici via AJAX -->
                                            <div class="loading-message">
                                                <i class="fas fa-spinner fa-spin"></i> Chargement des propositions...
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Citation 1: Programme → Équipe -->
                    <div class="block-card block-quote">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('citation1')">
                            <div class="block-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <div class="block-title">
                                <h3>Citation 1</h3>
                                <div class="block-count">Programme → Équipe</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="citation1-content">
                            <div class="block-edit-form">
                                <form method="POST" action="" id="citation1-form-element" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_citation1">
                                    
                                    <div class="form-group">
                                        <label for="citation1_text">Texte de la citation *</label>
                                        <textarea id="citation1_text" name="text" rows="3" required onchange="markFormChanged('citation1')"><?= htmlspecialchars($content['citations']['citation1']['text'] ?? 'Considérer l\'être humain et la préservation de la nature comme composante centrale de l\'action publique') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="citation1_author">Auteur</label>
                                        <input type="text" id="citation1_author" name="author" value="<?= htmlspecialchars($content['citations']['citation1']['author'] ?? '') ?>" onchange="markFormChanged('citation1')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="citation1_background_image">Image de fond</label>
                                        <?php 
                                        $citation1_image = $content['citations']['citation1']['background_image'] ?? 'Images/hero_test.png';
                                        if (file_exists(__DIR__ . '/../../' . $citation1_image)): ?>
                                        <div class="current-image-preview">
                                            <img src="../<?= htmlspecialchars($citation1_image) ?>" alt="Image actuelle" class="image-thumbnail">
                                            <small class="form-help">Image actuelle : <?= htmlspecialchars(basename($citation1_image)) ?></small>
                                        </div>
                                        <?php else: ?>
                                        <div class="current-image-preview">
                                            <div class="image-placeholder">
                                                <i class="fas fa-image"></i>
                                                <span>Aucune image</span>
                                            </div>
                                            <small class="form-help">Aucune image trouvée</small>
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" id="citation1_background_image" name="background_image" accept="image/*" onchange="markFormChanged('citation1')" style="width: 100%; margin-top: 10px;">
                                    </div>
                                    
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Équipe Section -->
                    <div class="block-card">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('equipe')">
                            <div class="block-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="block-title">
                                <h3>3. Section Équipe</h3>
                                <div class="block-count"><?= $equipe_count ?> membres</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="equipe-content">
                            <!-- Formulaire de modification des titres -->
                            <div class="block-edit-form">
                                <form method="POST" action="" id="equipe-form-element">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_equipe_section">
                                    
                                    <div class="form-group">
                                        <label for="equipe_title">Titre principal (H2) *</label>
                                        <input type="text" id="equipe_title" name="title" value="<?= htmlspecialchars($content['equipe']['title'] ?? 'Notre Équipe') ?>" required onchange="markFormChanged('equipe')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="equipe_subtitle">Sous-titre (H3)</label>
                                        <input type="text" id="equipe_subtitle" name="subtitle" value="<?= htmlspecialchars($content['equipe']['subtitle'] ?? 'Des citoyens engagés') ?>" onchange="markFormChanged('equipe')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="equipe_description">Description</label>
                                        <textarea id="equipe_description" name="description" rows="3" onchange="markFormChanged('equipe')"><?= htmlspecialchars($content['equipe']['description'] ?? 'Une équipe de citoyens engagés pour Saint-Paul') ?></textarea>
                                    </div>
                                    
                                </form>
                            </div>
                            
                            <!-- Composants de l'équipe -->
                            <div class="components-grid" id="equipe-grid">
                                <?php foreach($content['equipe']['members'] ?? [] as $member): ?>
                                    <div class="component-card" draggable="true" data-id="<?= $member['id'] ?>">
                                        <div class="drag-handle">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                        <div class="member-photo">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="component-title"><?= htmlspecialchars($member['name']) ?></div>
                                        <div class="component-description">
                                            <strong><?= htmlspecialchars($member['role']) ?></strong><br>
                                            <?= htmlspecialchars($member['quote'] ?? '') ?>
                                        </div>
                                        <div class="component-actions">
                                            <button class="btn-component edit" onclick="editMember(<?= $member['id'] ?>)">
                                                <i class="fas fa-edit"></i> Modifier
                                            </button>
                                            <button class="btn-component delete" onclick="deleteMember(<?= $member['id'] ?>)">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Carte d'ajout de membre -->
                                <div class="component-card add-card" onclick="openAddMemberModal()">
                                    <div class="add-icon">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="component-title">Ajouter un membre</div>
                                    <div class="component-description">
                                        Cliquez pour ajouter un nouveau membre
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Citation 2: Équipe → Rendez-vous -->
                    <div class="block-card block-quote">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('citation2')">
                            <div class="block-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <div class="block-title">
                                <h3>Citation 2</h3>
                                <div class="block-count">Équipe → Rendez-vous</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="citation2-content">
                            <div class="block-edit-form">
                                <form method="POST" action="" id="citation2-form-element">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_citation2">
                                    
                                    <div class="form-group">
                                        <label for="citation2_text">Texte de la citation *</label>
                                        <textarea id="citation2_text" name="text" rows="3" required onchange="markFormChanged('citation2')"><?= htmlspecialchars($content['citations']['citation2']['text'] ?? 'Favoriser, au sein du conseil municipal, le débat d\'idées dans le respect des points de vue') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="citation2_author">Auteur</label>
                                        <input type="text" id="citation2_author" name="author" value="<?= htmlspecialchars($content['citations']['citation2']['author'] ?? '') ?>" onchange="markFormChanged('citation2')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="citation2_background_image">Image de fond</label>
                                        <?php 
                                        $citation2_image = $content['citations']['citation2']['background_image'] ?? 'Images/hero_test.png';
                                        if (file_exists(__DIR__ . '/../../' . $citation2_image)): ?>
                                        <div class="current-image-preview">
                                            <img src="../<?= htmlspecialchars($citation2_image) ?>" alt="Image actuelle" class="image-thumbnail">
                                            <small class="form-help">Image actuelle : <?= htmlspecialchars(basename($citation2_image)) ?></small>
                                        </div>
                                        <?php else: ?>
                                        <div class="current-image-preview">
                                            <div class="image-placeholder">
                                                <i class="fas fa-image"></i>
                                                <span>Aucune image</span>
                                            </div>
                                            <small class="form-help">Aucune image trouvée</small>
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" id="citation2_background_image" name="background_image" accept="image/*" onchange="markFormChanged('citation2')" style="width: 100%; margin-top: 10px;">
                                    </div>
                                    
                                </form>
                            </div>
                        </div>
                    </div>


                    <!-- 4. Rendez-vous Section -->
                    <div class="block-card">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('rendez_vous')">
                            <div class="block-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="block-title">
                                <h3>4. Section Rendez-vous</h3>
                                <div class="block-count"><?= $rendez_vous_count ?> événements</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="rendez_vous-content">
                            <!-- Formulaire de modification des titres -->
                            <div class="block-edit-form">
                                <form method="POST" action="" id="rendez_vous-form-element">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_rendez_vous_section">
                                    
                                    <div class="form-group">
                                        <label for="rendez_vous_h2">Titre de section (H2) *</label>
                                        <input type="text" id="rendez_vous_h2" name="h2" value="<?= htmlspecialchars($content['rendez_vous']['h2'] ?? 'Nos Rendez-vous') ?>" required onchange="markFormChanged('rendez_vous')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="rendez_vous_h3">Sous-titre (H3)</label>
                                        <input type="text" id="rendez_vous_h3" name="h3" value="<?= htmlspecialchars($content['rendez_vous']['h3'] ?? 'Osons échanger') ?>" onchange="markFormChanged('rendez_vous')">
                                    </div>
                                    
                                </form>
                            </div>
                            
                            <!-- Composants des rendez-vous -->
                            <div class="components-grid" id="rendez_vous-grid">
                                <?php foreach($content['rendez_vous']['events'] ?? [] as $event): ?>
                                    <div class="component-card" draggable="true" data-id="<?= $event['id'] ?>">
                                        <div class="drag-handle">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                        <div class="event-date">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?= htmlspecialchars($event['date'] ?? 'Date à définir') ?>
                                        </div>
                                        <div class="component-title"><?= htmlspecialchars($event['title']) ?></div>
                                        <div class="component-description">
                                            <?= htmlspecialchars($event['description'] ?? '') ?><br>
                                            <small>
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?= htmlspecialchars($event['location'] ?? 'Lieu à définir') ?>
                                            </small>
                                        </div>
                                        <div class="component-actions">
                                            <button class="btn-component edit" onclick="editEvent(<?= $event['id'] ?>)">
                                                <i class="fas fa-edit"></i> Modifier
                                            </button>
                                            <button class="btn-component delete" onclick="deleteEvent(<?= $event['id'] ?>)">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Carte d'ajout d'événement -->
                                <div class="component-card add-card" onclick="openAddEventModal()">
                                    <div class="add-icon">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="component-title">Ajouter un événement</div>
                                    <div class="component-description">
                                        Cliquez pour créer un nouveau rendez-vous
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Citation 3: Rendez-vous → Charte -->
                    <div class="block-card block-quote">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('citation3')">
                            <div class="block-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <div class="block-title">
                                <h3>Citation 3</h3>
                                <div class="block-count">Rendez-vous → Charte</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="citation3-content">
                            <div class="block-edit-form">
                                <form method="POST" action="" id="citation3-form-element" enctype="multipart/form-data">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_citation3">
                                    
                                    <div class="form-group">
                                        <label for="citation3_text">Texte de la citation *</label>
                                        <textarea id="citation3_text" name="text" rows="3" required onchange="markFormChanged('citation3')"><?= htmlspecialchars($content['citations']['citation3']['text'] ?? 'Ensemble, nous sommes plus forts') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="citation3_author">Auteur</label>
                                        <input type="text" id="citation3_author" name="author" value="<?= htmlspecialchars($content['citations']['citation3']['author'] ?? '') ?>" onchange="markFormChanged('citation3')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="citation3_background_image">Image de fond</label>
                                        <?php 
                                        $citation3_image = $content['citations']['citation3']['background_image'] ?? 'Images/hero_test.png';
                                        if (file_exists(__DIR__ . '/../../' . $citation3_image)): ?>
                                        <div class="current-image-preview">
                                            <img src="../<?= htmlspecialchars($citation3_image) ?>" alt="Image actuelle" class="image-thumbnail">
                                            <small class="form-help">Image actuelle : <?= htmlspecialchars(basename($citation3_image)) ?></small>
                                        </div>
                                        <?php else: ?>
                                        <div class="current-image-preview">
                                            <div class="image-placeholder">
                                                <i class="fas fa-image"></i>
                                                <span>Aucune image</span>
                                            </div>
                                            <small class="form-help">Aucune image trouvée</small>
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" id="citation3_background_image" name="background_image" accept="image/*" onchange="markFormChanged('citation3')" style="width: 100%; margin-top: 10px;">
                                    </div>
                                    
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Charte Section -->
                    <div class="block-card">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('charte')">
                            <div class="block-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="block-title">
                                <h3>5. Section Charte</h3>
                                <div class="block-count"><?= $charte_count ?> principes</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="charte-content">
                            <!-- Formulaire de modification des titres -->
                            <div class="block-edit-form">
                                <form method="POST" action="" id="charte-form-element">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_charte_section">
                                    
                                    <div class="form-group">
                                        <label for="charte_h2">Titre de section (H2) *</label>
                                        <input type="text" id="charte_h2" name="h2" value="<?= htmlspecialchars($content['charte']['h2'] ?? 'Notre Charte') ?>" required onchange="markFormChanged('charte')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="charte_h3">Sous-titre (H3)</label>
                                        <input type="text" id="charte_h3" name="h3" value="<?= htmlspecialchars($content['charte']['h3'] ?? 'Nos valeurs communes') ?>" onchange="markFormChanged('charte')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="charte_intro_text">Texte d'introduction</label>
                                        <textarea id="charte_intro_text" name="intro_text" rows="3" onchange="markFormChanged('charte')"><?= htmlspecialchars($content['charte']['intro_text'] ?? 'S\'engager en tant qu\'élu sur une liste municipale, c\'est choisir de mettre ses compétences, son énergie et son temps au service de l\'intérêt général sur plusieurs années, avec une participation active aux réunions, commissions et projets municipaux.') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="charte_intro_highlight">Texte en gras (surligné)</label>
                                        <input type="text" id="charte_intro_highlight" name="intro_highlight" value="<?= htmlspecialchars($content['charte']['intro_highlight'] ?? 'Voici les principes sur lesquels nous nous engageons et qui fonderont nos actions :') ?>" onchange="markFormChanged('charte')">
                                    </div>
                                    
                                </form>
                            </div>
                            
                            <!-- Composants de la charte -->
                            <div class="components-grid" id="charte-grid">
                                <?php foreach($content['charte']['principles'] ?? [] as $principle): ?>
                                    <div class="component-card" draggable="true" data-id="<?= $principle['id'] ?>">
                                        <div class="drag-handle">
                                            <i class="fas fa-grip-vertical"></i>
                                        </div>
                                        <div class="component-title"><?= htmlspecialchars($principle['title']) ?></div>
                                        <div class="component-description">
                                            <?= htmlspecialchars($principle['description'] ?? '') ?>
                                        </div>
                                        <div class="component-actions">
                                            <button class="btn-component edit" onclick="editPrinciple(<?= $principle['id'] ?>)">
                                                <i class="fas fa-edit"></i> Modifier
                                            </button>
                                            <button class="btn-component delete" onclick="deletePrinciple(<?= $principle['id'] ?>)">
                                                <i class="fas fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <!-- Carte d'ajout de principe -->
                                <div class="component-card add-card" onclick="openAddPrincipleModal()">
                                    <div class="add-icon">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <div class="component-title">Ajouter un principe</div>
                                    <div class="component-description">
                                        Cliquez pour créer un nouveau principe
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Citation 4: Charte → Idées -->
                    <div class="block-card block-quote">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('citation4')">
                            <div class="block-icon">
                                <i class="fas fa-quote-left"></i>
                            </div>
                            <div class="block-title">
                                <h3>Citation 4</h3>
                                <div class="block-count">Charte → Idées</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="citation4-content">
                            <div class="block-edit-form">
                                <form method="POST" action="" id="citation4-form-element">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_citation4">
                                    
                                    <div class="form-group">
                                        <label for="citation4_text">Texte de la citation *</label>
                                        <textarea id="citation4_text" name="text" rows="3" required onchange="markFormChanged('citation4')"><?= htmlspecialchars($content['citations']['citation4']['text'] ?? 'L\'innovation sociale naît de la collaboration entre citoyens et institutions') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="citation4_author">Auteur</label>
                                        <input type="text" id="citation4_author" name="author" value="<?= htmlspecialchars($content['citations']['citation4']['author'] ?? '') ?>" onchange="markFormChanged('citation4')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="citation4_background_image">Image de fond</label>
                                        <?php 
                                        $citation4_image = $content['citations']['citation4']['background_image'] ?? 'Images/hero_test.png';
                                        if (file_exists(__DIR__ . '/../../' . $citation4_image)): ?>
                                        <div class="current-image-preview">
                                            <img src="../<?= htmlspecialchars($citation4_image) ?>" alt="Image actuelle" class="image-thumbnail">
                                            <small class="form-help">Image actuelle : <?= htmlspecialchars(basename($citation4_image)) ?></small>
                                        </div>
                                        <?php else: ?>
                                        <div class="current-image-preview">
                                            <div class="image-placeholder">
                                                <i class="fas fa-image"></i>
                                                <span>Aucune image</span>
                                            </div>
                                            <small class="form-help">Aucune image trouvée</small>
                                        </div>
                                        <?php endif; ?>
                                        <input type="file" id="citation4_background_image" name="background_image" accept="image/*" onchange="markFormChanged('citation4')" style="width: 100%; margin-top: 10px;">
                                    </div>
                                    
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Idées Section -->
                    <div class="block-card">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('idees')">
                            <div class="block-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <div class="block-title">
                                <h3>6. Section Idées</h3>
                                <div class="block-count">Propositions citoyennes</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="idees-content">
                            <div class="block-edit-form">
                                <form method="POST" action="" id="idees-form-element">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_idees_section">
                                    
                                    <div class="form-group">
                                        <label for="idees_title">Titre principal (H2) *</label>
                                        <input type="text" id="idees_title" name="title" value="<?= htmlspecialchars($content['idees']['title'] ?? 'Vos idées comptent') ?>" required onchange="markFormChanged('idees')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="idees_subtitle">Sous-titre (H3)</label>
                                        <input type="text" id="idees_subtitle" name="subtitle" value="<?= htmlspecialchars($content['idees']['subtitle'] ?? 'Osez proposer') ?>" onchange="markFormChanged('idees')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="idees_description">Description</label>
                                        <textarea id="idees_description" name="description" rows="3" onchange="markFormChanged('idees')"><?= htmlspecialchars($content['idees']['description'] ?? 'Partagez vos idées et propositions pour enrichir notre programme') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="idees_contact_email">Email de contact</label>
                                        <input type="email" id="idees_contact_email" name="contact_email" value="<?= htmlspecialchars($content['idees']['contact_email'] ?? '') ?>" onchange="markFormChanged('idees')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="idees_contact_phone">Téléphone de contact</label>
                                        <input type="tel" id="idees_contact_phone" name="contact_phone" value="<?= htmlspecialchars($content['idees']['contact_phone'] ?? '') ?>" onchange="markFormChanged('idees')">
                                    </div>
                                    
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- 7. Médiathèque Section -->
                    <div class="block-card">
                        <div class="status-indicator"></div>
                        <div class="block-header" onclick="toggleSection('mediatheque')">
                            <div class="block-icon">
                                <i class="fas fa-folder"></i>
                            </div>
                            <div class="block-title">
                                <h3>7. Section Médiathèque</h3>
                                <div class="block-count">Documents et ressources</div>
                            </div>
                            <div class="expand-icon">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                        
                        <div class="block-content" id="mediatheque-content">
                            <div class="block-edit-form">
                                <form method="POST" action="" id="mediatheque-form-element">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                    <input type="hidden" name="action" value="update_mediatheque_section">
                                    
                                    <div class="form-group">
                                        <label for="mediatheque_title">Titre principal (H2) *</label>
                                        <input type="text" id="mediatheque_title" name="title" value="<?= htmlspecialchars($content['mediatheque']['title'] ?? 'Médiathèque') ?>" required onchange="markFormChanged('mediatheque')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="mediatheque_subtitle">Sous-titre (H3)</label>
                                        <input type="text" id="mediatheque_subtitle" name="subtitle" value="<?= htmlspecialchars($content['mediatheque']['subtitle'] ?? 'Osons partager') ?>" onchange="markFormChanged('mediatheque')">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="mediatheque_description">Description</label>
                                        <textarea id="mediatheque_description" name="description" rows="3" onchange="markFormChanged('mediatheque')"><?= htmlspecialchars($content['mediatheque']['description'] ?? 'Retrouvez tous nos documents, photos, vidéos et actualités dans notre espace de partage.') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="mediatheque_drive_url">URL Google Drive</label>
                                        <input type="url" id="mediatheque_drive_url" name="drive_url" value="<?= htmlspecialchars($content['mediatheque']['drive_url'] ?? '') ?>" onchange="markFormChanged('mediatheque')">
                                    </div>
                                    
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Bouton de sauvegarde flottant -->
        <button class="floating-save-btn" id="floating-save-btn" onclick="saveAllChanges()">
            <i class="fas fa-save"></i>
            Sauvegarder tout
        </button>
        
        <!-- Indicateur d'auto-sauvegarde -->
        <div class="auto-save-indicator" id="auto-save-indicator">
            <i class="fas fa-check"></i>
            Sauvegardé automatiquement
        </div>
        
        <!-- Container pour les notifications toast -->
        <div id="toast-container"></div>
    </div>
    
    
    <!-- Modals -->
    <!-- Modal Hero -->
    <div id="heroModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Modifier la Section Hero</h3>
                <button onclick="closeModal('heroModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="hero-form" class="admin-form">
                    
                    <div class="form-group">
                        <label for="hero_title">Titre principal *</label>
                        <input type="text" id="hero_title" name="title" value="<?= htmlspecialchars($content['hero']['title'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hero_subtitle">Sous-titre</label>
                        <input type="text" id="hero_subtitle" name="subtitle" value="<?= htmlspecialchars($content['hero']['subtitle'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="hero_description">Description</label>
                        <textarea id="hero_description" name="description" rows="3"><?= htmlspecialchars($content['hero']['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="hero_button_primary">Bouton principal</label>
                        <input type="text" id="hero_button_primary" name="button_primary" value="<?= htmlspecialchars($content['hero']['button_primary'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="hero_button_secondary">Bouton secondaire</label>
                        <input type="text" id="hero_button_secondary" name="button_secondary" value="<?= htmlspecialchars($content['hero']['button_secondary'] ?? '') ?>">
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" onclick="closeModal('heroModal')" class="btn btn-secondary">Annuler</button>
                        <button type="button" onclick="saveHero()" class="btn btn-primary">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajout Membre -->
    <div id="addMemberModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Ajouter un Membre</h3>
                <button onclick="forceCloseModal('addMemberModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-member-form" enctype="multipart/form-data" class="admin-form">
                    
                    <div class="form-group">
                        <label for="member_name">Nom complet *</label>
                        <input type="text" id="member_name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="member_role">Rôle/Fonction *</label>
                        <input type="text" id="member_role" name="role" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="member_quote">Citation</label>
                        <textarea id="member_quote" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="member_photo">Photo</label>
                        <input type="file" id="member_photo" name="image" accept="image/*">
                        <small class="form-help">Formats acceptés : JPG, PNG, GIF, WebP (max 5MB)</small>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" onclick="closeModal('addMemberModal')" class="btn btn-secondary">Annuler</button>
                        <button type="button" onclick="saveAddMember()" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Gestion Programme -->
    <div id="programmeModal" class="modal-overlay" style="display: none;">
        <div class="modal-container modal-large">
            <div class="modal-header">
                <h3>Gérer la Section Programme</h3>
                <button onclick="closeModal('programmeModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="programme-form" class="admin-form">
                    
                    <!-- En-tête de la section -->
                    <div class="form-section">
                        <h4><i class="fas fa-heading"></i> En-tête de la section</h4>
                        <div class="form-group">
                            <label for="programme_title">Titre principal (H2) *</label>
                            <input type="text" id="programme_title" name="title" value="<?= htmlspecialchars($content['programme']['title'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="programme_subtitle">Sous-titre (H3) *</label>
                            <input type="text" id="programme_subtitle" name="subtitle" value="<?= htmlspecialchars($content['programme']['subtitle'] ?? '') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="programme_description">Description *</label>
                            <textarea id="programme_description" name="description" rows="2" required><?= htmlspecialchars($content['programme']['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" onclick="closeModal('programmeModal')" class="btn btn-secondary">Annuler</button>
                        <button type="button" onclick="saveProgramme()" class="btn btn-primary">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajout Proposition -->
    <div id="addProposalModal" class="modal-overlay" style="display: none;">
        <div class="modal-container modal-large">
            <div class="modal-header">
                <h3>Ajouter une Proposition</h3>
                <button onclick="closeModal('addProposalModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="add_proposal">
                    
                    <!-- RECTO - Face visible de la carte -->
                    <div class="form-section">
                        <h4><i class="fas fa-eye"></i> Recto - Face visible de la carte</h4>
                        <div class="form-group">
                            <label for="proposal_title">Titre de la proposition *</label>
                            <input type="text" id="proposal_title" name="title" required placeholder="Ex: Préserver nos espaces naturels">
                        </div>
                        
                        <div class="form-group">
                            <label for="proposal_pillar">Pilier *</label>
                            <select id="proposal_pillar" name="pillar" required>
                                <option value="">Sélectionner un pilier</option>
                                <option value="proteger" data-color="#65ae99">🛡️ Osons protéger</option>
                                <option value="tisser" data-color="#fcc549">🤝 Osons tisser des liens</option>
                                <option value="dessiner" data-color="#4e9eb0">🎨 Osons dessiner</option>
                                <option value="ouvrir" data-color="#004a6d">🔓 Osons ouvrir</option>
                            </select>
                            <small class="form-help">La couleur sera automatiquement appliquée selon le pilier</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" id="proposal_citizen" name="citizen_proposal" value="1">
                                    Proposition citoyenne
                                </label>
                            </div>
                            <small class="form-help">Cochez si c'est une proposition issue des citoyens (icône citoyen ajoutée)</small>
                        </div>
                    </div>
                    
                    <!-- VERSO - Dos de la carte -->
                    <div class="form-section">
                        <h4><i class="fas fa-file-alt"></i> Verso - Dos de la carte</h4>
                        <div class="form-group">
                            <label for="proposal_description">Description *</label>
                            <textarea id="proposal_description" name="description" rows="3" required placeholder="Ex: Création d'un parc naturel communal et protection des zones humides."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="proposal_items">Points clés (un par ligne)</label>
                            <textarea id="proposal_items" name="items" rows="4" placeholder="Aménagement de sentiers pédagogiques&#10;Protection de la biodiversité locale&#10;Éducation environnementale"></textarea>
                            <small class="form-help">Chaque ligne deviendra un point dans la liste</small>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" onclick="closeModal('addProposalModal')" class="btn btn-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Modifier Proposition -->
    <div id="editProposalModal" class="modal-overlay" style="display: none;">
        <div class="modal-container modal-large">
            <div class="modal-header">
                <h3>Modifier la Proposition</h3>
                <button onclick="closeModal('editProposalModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form method="POST" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="edit_proposal">
                    <input type="hidden" id="edit_proposal_id" name="proposal_id" value="">
                    
                    <!-- RECTO - Face visible de la carte -->
                    <div class="form-section">
                        <h4><i class="fas fa-eye"></i> Recto - Face visible de la carte</h4>
                        <div class="form-group">
                            <label for="edit_proposal_title">Titre de la proposition *</label>
                            <input type="text" id="edit_proposal_title" name="title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_proposal_pillar">Pilier *</label>
                            <select id="edit_proposal_pillar" name="pillar" required>
                                <option value="">Sélectionner un pilier</option>
                                <option value="proteger" data-color="#65ae99">🛡️ Osons protéger</option>
                                <option value="tisser" data-color="#fcc549">🤝 Osons tisser des liens</option>
                                <option value="dessiner" data-color="#4e9eb0">🎨 Osons dessiner</option>
                                <option value="ouvrir" data-color="#004a6d">🔓 Osons ouvrir</option>
                            </select>
                            <small class="form-help">La couleur sera automatiquement appliquée selon le pilier</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" id="edit_proposal_citizen" name="citizen_proposal" value="1">
                                    Proposition citoyenne
                                </label>
                            </div>
                            <small class="form-help">Cochez si c'est une proposition issue des citoyens (icône citoyen ajoutée)</small>
                        </div>
                    </div>
                    
                    <!-- VERSO - Dos de la carte -->
                    <div class="form-section">
                        <h4><i class="fas fa-file-alt"></i> Verso - Dos de la carte</h4>
                        <div class="form-group">
                            <label for="edit_proposal_description">Description *</label>
                            <textarea id="edit_proposal_description" name="description" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_proposal_items">Points clés (un par ligne)</label>
                            <textarea id="edit_proposal_items" name="items" rows="4"></textarea>
                            <small class="form-help">Chaque ligne deviendra un point dans la liste</small>
                        </div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" onclick="closeModal('editProposalModal')" class="btn btn-secondary">Annuler</button>
                        <button type="submit" class="btn btn-primary">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Citation -->
    <div id="citationModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Modifier la Citation</h3>
                <button onclick="closeModal('citationModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="citation-form" class="admin-form">
                    <input type="hidden" id="citation_id" name="citation_id" value="">
                    
                    <div class="form-group">
                        <label for="citation_text">Texte de la citation *</label>
                        <textarea id="citation_text" name="text" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="citation_author">Auteur</label>
                        <input type="text" id="citation_author" name="author">
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" onclick="closeModal('citationModal')" class="btn btn-secondary">Annuler</button>
                        <button type="button" onclick="saveCitation()" class="btn btn-primary">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Données des propositions pour JavaScript
        const proposalsData = <?= json_encode($content['programme']['proposals'] ?? []) ?>;
        // Workspace unique: logique d'affichage d'une seule section
        function selectSection(sectionName) {
            try {
                const workspace = document.getElementById('adminWorkspace');
                if (!workspace) return;

                // Récupère le contenu de la section existante
                const contentEl = document.getElementById(`${sectionName}-content`);
                if (!contentEl) {
                    console.warn('Section introuvable:', sectionName);
                    return;
                }

                // Injecte uniquement le contenu HTML (évite de dupliquer l'ID conteneur)
                workspace.innerHTML = contentEl.innerHTML;

                // S'assurer que la zone est visible et au focus
                workspace.style.display = 'block';
                const firstInput = workspace.querySelector('input, textarea, select, button');
                if (firstInput) firstInput.focus();

                // Met à jour l'URL (hash) sans scroll
                history.replaceState(null, '', `#${sectionName}-content`);
                
                // Mémorise la dernière section ouverte pour restauration après reload
                try { localStorage.setItem('adminLastSection', sectionName); } catch(_) {}
                
                // Fait apparaître la zone d'édition
                workspace.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (e) {
                console.error('selectSection error', e);
            }
        }

        // Désactiver le scroll/ancres hérités
        function scrollToSection() { /* désactivé par workspace unique */ }
        function toggleSection() { /* désactivé par workspace unique */ }

        // Restaurer automatiquement la dernière section ouverte
        document.addEventListener('DOMContentLoaded', function() {
            try {
                const last = localStorage.getItem('adminLastSection');
                if (last) {
                    // Laisse un petit délai pour que le DOM soit prêt
                    setTimeout(() => selectSection(last), 50);
                }
            } catch(_) {}
        });

        // Vue combinée des 4 transitions
        function selectTransitionsAll() {
            try {
                const workspace = document.getElementById('adminWorkspace');
                if (!workspace) return;

                const ids = ['citation1-content','citation2-content','citation3-content','citation4-content'];
                const parts = ids.map(id => {
                    const el = document.getElementById(id);
                    return el ? `<section class=\"transition-group\">${el.innerHTML}</section>` : '';
                });
                workspace.innerHTML = parts.join('');
                history.replaceState(null, '', '#transitions');
                workspace.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (e) {
                console.error('selectTransitionsAll error', e);
            }
        }

        
        // Fonctions pour ouvrir/fermer les modals
        function openModal(modalId) {
            console.log('Tentative d\'ouverture du modal:', modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                modal.style.opacity = '0';
                modal.style.transition = 'opacity 0.3s ease';
                
                // Forcer le reflow pour que l'animation fonctionne
                modal.offsetHeight;
                
                modal.style.opacity = '1';
                console.log('Modal ouvert:', modalId);
            } else {
                console.error('Modal non trouvé:', modalId);
            }
        }
        
        function closeModal(modalId) {
            console.log('Tentative de fermeture du modal:', modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                // Fermeture immédiate sans animation pour éviter les problèmes
                modal.style.display = 'none';
                modal.style.opacity = '1';
                modal.style.transition = '';
                console.log('Modal fermé avec succès:', modalId);
            } else {
                console.error('Modal non trouvé:', modalId);
            }
        }
        
        function forceCloseModal(modalId) {
            console.log('Fermeture forcée du modal:', modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                modal.style.opacity = '1';
                modal.style.transition = '';
                console.log('Modal fermé de force:', modalId);
            }
        }
        
        // Fonctions spécifiques pour chaque modal
        function openHeroModal() {
            openModal('heroModal');
        }
        
        function openAddMemberModal() {
            console.log('Tentative d\'ouverture du modal addMemberModal');
            openModal('addMemberModal');
        }
        
        function openAddProposalModal() {
            openModal('addProposalModal');
        }
        
        function openCitationModal(citationId) {
            // Remplir les données de la citation selon l'ID
            const citations = {
                1: {
                    text: "Considérer l'être humain et la préservation de la nature comme composante centrale de l'action publique",
                    author: "Notre vision"
                },
                2: {
                    text: "Favoriser, au sein du conseil municipal, le débat d'idées dans le respect des points de vue",
                    author: "Notre méthode de travail"
                },
                3: {
                    text: "Porter une politique en faveur des plus fragiles par des actions favorisant l'inclusion et l'autonomie",
                    author: "Notre engagement social"
                },
                4: {
                    text: "S'engager à être cohérent.es entre nos intentions et nos actes",
                    author: "Notre cohérence"
                }
            };
            
            const citation = citations[citationId];
            if (citation) {
                document.getElementById('citation_id').value = citationId;
                document.getElementById('citation_text').value = citation.text;
                document.getElementById('citation_author').value = citation.author;
            }
            openModal('citationModal');
        }
        
        function openEquipeModal() {
            openModal('equipeModal');
        }
        
        function openProgrammeModal() {
            openModal('programmeModal');
        }
        
        function openEditProgrammeProposalModal(proposalId) {
            // Trouver la proposition dans les données
            const proposal = proposalsData.find(p => p.id == proposalId);
            
            if (proposal) {
                // Remplir le formulaire avec les données existantes
                document.getElementById('edit_proposal_id').value = proposalId;
                document.getElementById('edit_proposal_title').value = proposal.title || '';
                document.getElementById('edit_proposal_description').value = proposal.description || '';
                
                // Définir le pilier selon la couleur (logique inverse)
                const pillarSelect = document.getElementById('edit_proposal_pillar');
                const color = proposal.color || '#2d5a3d';
                
                // Trouver l'option correspondant à la couleur
                for (let option of pillarSelect.options) {
                    if (option.dataset.color === color) {
                        option.selected = true;
                        break;
                    }
                }
                
                // Cocher la case "proposition citoyenne" si applicable
                document.getElementById('edit_proposal_citizen').checked = proposal.citizen_proposal || false;
                
                // Remplir les points clés
                const items = proposal.items || [];
                document.getElementById('edit_proposal_items').value = items.join('\n');
            }
            
            openModal('editProposalModal');
        }
        
        function deleteProposal(proposalId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette proposition ?')) {
                // Créer un formulaire de suppression
                const form = document.createElement('form');
                form.method = 'POST';
                form.style.display = 'none';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete_proposal';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'proposal_id';
                idInput.value = proposalId;
                
                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = 'csrf_token';
                tokenInput.value = '<?= generate_csrf_token() ?>';
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                form.appendChild(tokenInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        
        // Fermer les modals avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal-overlay');
                modals.forEach(modal => {
                    if (modal.style.display === 'flex') {
                        modal.style.display = 'none';
                    }
                });
            }
        });
        
        // Fermer les modals en cliquant sur l'overlay
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                e.target.style.display = 'none';
            }
        });
        
        // Fonctions pour gérer les membres
        function editMember(id) {
            console.log('editMember appelé avec ID:', id);
            // Remplir le modal avec les données du membre
            const member = getMemberById(id);
            console.log('Membre trouvé:', member);
            if (member) {
                document.getElementById('edit-member-id').value = member.id;
                document.getElementById('edit-member-name').value = member.name || '';
                document.getElementById('edit-member-role').value = member.role || '';
                document.getElementById('edit-member-description').value = member.description || '';
                // Ne pas définir la valeur d'un input type=file (bloqué par le navigateur)
                console.log('Modal editMemberModal ouvert');
                openModal('editMemberModal');
            } else {
                console.error('Membre non trouvé avec ID:', id);
            }
        }
        
        function getMemberById(id) {
            // Récupérer le membre depuis les données PHP
            const members = <?= json_encode($content['equipe']['members'] ?? []) ?>;
            console.log('Recherche membre ID:', id, 'dans:', members);
            return members.find(member => member.id == id);
        }
        
        function deleteMember(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce membre ?')) {
                const formData = new FormData();
                formData.append('action', 'delete_member');
                formData.append('member_id', id);
                formData.append('csrf_token', '<?= generate_csrf_token() ?>');
                
                console.log('Suppression du membre ID:', id);
                
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Réponse suppression:', data);
                    if (data.success) {
                        showToast(data.message + ' - Rechargez la page pour voir les changements', 'success');
                        // Optionnel : recharger automatiquement
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la suppression:', error);
                    showToast('Erreur lors de la suppression', 'error');
                });
            }
        }
        
        // Fonctions pour gérer les propositions
        function editProposal(id) {
            // Utiliser la fonction existante pour les propositions du programme
            openEditProgrammeProposalModal(id);
        }
        
        function deleteProposal(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette proposition ?')) {
                // Créer un formulaire pour la suppression
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'programme.php';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = 'csrf_token';
                csrfToken.value = '<?= generate_csrf_token() ?>';
                
                const action = document.createElement('input');
                action.type = 'hidden';
                action.name = 'action';
                action.value = 'delete_proposal';
                
                const proposalId = document.createElement('input');
                proposalId.type = 'hidden';
                proposalId.name = 'proposal_id';
                proposalId.value = id;
                
                form.appendChild(csrfToken);
                form.appendChild(action);
                form.appendChild(proposalId);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Fonctions pour gérer les événements
        function openAddEventModal() {
            try {
                const form = document.getElementById('add-event-form');
                if (form) form.reset();
            } catch(e) { console.warn('reset add-event-form error', e); }
            openModal('addEventModal');
        }
        
        function openAddPrincipleModal() {
            openModal('addPrincipleModal');
        }
        
        function editPrinciple(id) {
            try {
                const principles = <?= json_encode($content['charte']['principles'] ?? []) ?>;
                const p = principles.find(pr => pr.id == id);
                if (!p) {
                    showToast('Principe introuvable', 'error');
                    return;
                }
                document.getElementById('edit-principle-id').value = p.id;
                document.getElementById('edit-principle-title').value = p.title || '';
                document.getElementById('edit-principle-description').value = p.description || '';
                document.getElementById('edit-principle-theme').value = p.thematique || p.source || '';
                openModal('editPrincipleModal');
            } catch(e) {
                console.error('editPrinciple error', e);
            }
        }

        function deletePrinciple(id) {
            if (!confirm('Supprimer ce principe ?')) return;
            const formData = new FormData();
            formData.append('action', 'delete_principle');
            formData.append('principle_id', id);
            formData.append('csrf_token', '<?= generate_csrf_token() ?>');
            fetch('', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        showToast(data.message || 'Suppression échouée', 'error');
                    }
                })
                .catch(e => { console.error(e); showToast('Erreur lors de la suppression', 'error'); });
        }
        
        function editEvent(id) {
            // Remplir le modal avec les données de l'événement
            const event = getEventById(id);
            console.log('Événement trouvé:', event);
            if (event) {
                document.getElementById('edit-event-id').value = event.id;
                document.getElementById('edit-event-title').value = event.title || '';
                document.getElementById('edit-event-description').value = event.description || '';
                document.getElementById('edit-event-date').value = event.date || '';
                document.getElementById('edit-event-location').value = event.location || '';
                console.log('Ouverture du modal editEventModal');
                openModal('editEventModal');
            } else {
                console.log('Événement non trouvé pour ID:', id);
            }
        }
        
        function getEventById(id) {
            // Récupérer l'événement depuis les données PHP (rendez_vous)
            const events = <?= json_encode($content['rendez_vous']['events'] ?? []) ?>;
            console.log('Recherche événement ID:', id, 'dans:', events);
            return events.find(event => event.id == id);
        }
        
        function deleteEvent(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')) {
                // Créer un formulaire pour la suppression
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = 'csrf_token';
                csrfToken.value = '<?= generate_csrf_token() ?>';
                
                const action = document.createElement('input');
                action.type = 'hidden';
                action.name = 'action';
                action.value = 'delete_event';
                
                const eventId = document.createElement('input');
                eventId.type = 'hidden';
                eventId.name = 'event_id';
                eventId.value = id;
                
                form.appendChild(csrfToken);
                form.appendChild(action);
                form.appendChild(eventId);
                document.body.appendChild(form);
                fetch('', { method: 'POST', body: new FormData(form) })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            showToast(data.message, 'success');
                            setTimeout(() => window.location.reload(), 800);
                        } else {
                            showToast(data.message || 'Suppression échouée', 'error');
                        }
                    })
                    .catch(e => {
                        console.error(e);
                        showToast('Erreur lors de la suppression', 'error');
                    });
            }
        }
        
        // Fonction pour basculer l'accordéon des sections (une seule ouverte à la fois)
        function toggleSection(sectionName) {
            const header = document.querySelector(`[onclick="toggleSection('${sectionName}')"]`);
            const content = document.getElementById(`${sectionName}-content`);
            
            // Fermer toutes les autres sections
            const allHeaders = document.querySelectorAll('.block-header');
            const allContents = document.querySelectorAll('.block-content');
            
            allHeaders.forEach(h => h.classList.remove('expanded'));
            allContents.forEach(c => c.classList.remove('expanded'));
            
            // Ouvrir la section cliquée si elle était fermée
            if (!content.classList.contains('expanded')) {
                content.classList.add('expanded');
                header.classList.add('expanded');
            }
        }
        
        // Fonction pour changer d'onglet
        function switchTab(tabName) {
            // Désactiver tous les onglets
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Activer l'onglet sélectionné
            document.querySelector(`[onclick="switchTab('${tabName}')"]`).classList.add('active');
            document.getElementById(tabName).classList.add('active');
            
            // Charger les propositions citoyennes si c'est l'onglet sélectionné
            if (tabName === 'citizen-proposals') {
                loadCitizenProposals();
            }
        }
        
        // Fonction pour charger les propositions citoyennes
        function loadCitizenProposals() {
            const container = document.getElementById('citizen-proposals-list');
            container.innerHTML = '<div class="loading-message"><i class="fas fa-spinner fa-spin"></i> Chargement des propositions...</div>';
            
            fetch('../../data/propositions.json')
                .then(response => response.json())
                .then(data => {
                    displayCitizenProposals(data.propositions || []);
                    updateCitizenCount(data.propositions || []);
                })
                .catch(error => {
                    console.error('Erreur lors du chargement:', error);
                    container.innerHTML = '<div class="loading-message">❌ Erreur lors du chargement des propositions</div>';
                });
        }
        
        // Charger automatiquement les propositions au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Charger les propositions citoyennes si l'onglet est actif
            if (document.getElementById('citizen-proposals').classList.contains('active')) {
                loadCitizenProposals();
            }
        });
        
        // Fonction pour afficher les propositions citoyennes
        function displayCitizenProposals(propositions) {
            const container = document.getElementById('citizen-proposals-list');
            
            if (propositions.length === 0) {
                container.innerHTML = '<div class="loading-message">📝 Aucune proposition citoyenne</div>';
                return;
            }
            
            // Afficher toutes les propositions avec des filtres
            const pendingProposals = propositions.filter(p => p.status === 'pending');
            const approvedProposals = propositions.filter(p => p.status === 'approved');
            const rejectedProposals = propositions.filter(p => p.status === 'rejected');
            
            let html = '';
            
            // Fonction pour générer le HTML d'une proposition
            function generateProposalHTML(proposal) {
                let actions = '';
                if (proposal.status === 'pending') {
                    actions = `
                        <button class="btn-action btn-approve" onclick="approveAndEditProposal('${proposal.id}')">
                            ✅ Approuver & Modifier
                        </button>
                        <button class="btn-action btn-reject" onclick="updateCitizenProposalStatus('${proposal.id}', 'rejected')">
                            ❌ Rejeter
                        </button>
                    `;
                } else if (proposal.status === 'approved' && !proposal.integrated) {
                    actions = `
                        <button class="btn-action btn-integrate" onclick="integrateCitizenProposal('${proposal.id}')">
                            🚀 Intégrer au programme
                        </button>
                    `;
                } else if (proposal.integrated) {
                    actions = `
                        <span class="btn-action" style="background: #28a745; color: white; cursor: default;">
                            ✅ Intégrée
                        </span>
                    `;
                }
                
                // Traduction des statuts
                const statusTranslations = {
                    'pending': 'En attente',
                    'approved': 'Approuvée',
                    'rejected': 'Rejetée',
                    'integrated': 'Intégrée'
                };
                
                return `
                    <div class="citizen-proposal-card" data-status="${proposal.status}">
                        <div class="citizen-proposal-header">
                            <h5 class="citizen-proposal-title">${escapeHtml(proposal.data.titre)}</h5>
                            <span class="citizen-proposal-status status-${proposal.status}">${statusTranslations[proposal.status] || proposal.status}</span>
                        </div>
                        <div class="citizen-proposal-meta">
                            📅 ${new Date(proposal.date).toLocaleDateString('fr-FR')} | 
                            📧 ${escapeHtml(proposal.data.email)} | 
                            🆔 ${proposal.id}
                            ${proposal.data.telephone ? ' | 📞 ' + escapeHtml(proposal.data.telephone) : ''}
                            ${proposal.data.commune ? ' | 🏘️ ' + escapeHtml(proposal.data.commune) : ''}
                        </div>
                        <div class="citizen-proposal-description">
                            <strong>Description :</strong> ${escapeHtml(proposal.data.description.substring(0, 150))}...
                        </div>
                        <div class="citizen-proposal-details">
                            <div class="detail-row">
                                <strong>Catégories :</strong> ${(proposal.data.categories || []).map(cat => escapeHtml(cat)).join(', ')}
                            </div>
                            ${proposal.data.beneficiaires ? `<div class="detail-row"><strong>Bénéficiaires :</strong> ${escapeHtml(proposal.data.beneficiaires)}</div>` : ''}
                            ${proposal.data.cout ? `<div class="detail-row"><strong>Coût :</strong> ${escapeHtml(proposal.data.cout)}</div>` : ''}
                            ${proposal.data.engagement ? `<div class="detail-row"><strong>Engagement :</strong> ✅ Oui ${proposal.data.engagement_details ? '- ' + escapeHtml(proposal.data.engagement_details) : ''}</div>` : ''}
                        </div>
                        <div class="citizen-proposal-actions">
                            ${actions}
                            <a href="../../forms/admin/manage-proposition.php?id=${proposal.id}" target="_blank" class="btn-action" style="background: #6c757d; color: white; text-decoration: none;">
                                📝 Détails
                            </a>
                        </div>
                    </div>
                `;
            }
            
            // Afficher toutes les propositions
            propositions.forEach(proposal => {
                html += generateProposalHTML(proposal);
            });
            
            container.innerHTML = html;
        }
        
        // Fonction pour mettre à jour le compteur
        function updateCitizenCount(propositions) {
            const totalCount = propositions.length;
            const pendingCount = propositions.filter(p => p.status === 'pending').length;
            const approvedCount = propositions.filter(p => p.status === 'approved').length;
            const rejectedCount = propositions.filter(p => p.status === 'rejected').length;
            
            // Compteur dans l'onglet
            const countElement = document.getElementById('citizen-count');
            if (countElement) {
                countElement.textContent = pendingCount;
                countElement.style.display = pendingCount > 0 ? 'inline' : 'none';
            }
            
            // Compteurs dans les indicateurs
            const totalElement = document.getElementById('citizen-total-count');
            const pendingElement = document.getElementById('citizen-pending-count');
            const approvedElement = document.getElementById('citizen-approved-count');
            const rejectedElement = document.getElementById('citizen-rejected-count');
            
            if (totalElement) totalElement.textContent = totalCount;
            if (pendingElement) pendingElement.textContent = pendingCount;
            if (approvedElement) approvedElement.textContent = approvedCount;
            if (rejectedElement) rejectedElement.textContent = rejectedCount;
        }
        
        // Fonction pour faire défiler vers une section
        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                // Faire défiler vers la section
                setTimeout(() => {
                    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    // Mettre à jour l'URL avec l'ancre
                    window.history.pushState(null, null, '#' + sectionId);
                }, 100);
            }
        }
        
        // Gérer les ancres au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash) {
                const sectionId = window.location.hash.substring(1);
                setTimeout(() => {
                    scrollToSection(sectionId);
                }, 500);
            }
        });
        
        // Fonction pour filtrer les propositions
        function filterProposals(status) {
            // Mettre à jour les boutons de filtre
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filtrer les cartes
            const cards = document.querySelectorAll('.citizen-proposal-card');
            cards.forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Fonction pour approuver et modifier une proposition
        function approveAndEditProposal(proposalId) {
            // D'abord approuver la proposition
            updateCitizenProposalStatus(proposalId, 'approved', function() {
                // Puis ouvrir le modal de modification
                openEditProposalModal(proposalId);
            });
        }
        
        // Fonction pour ouvrir le modal de modification d'une proposition citoyenne
        function openEditProposalModal(proposalId) {
            // Récupérer les données de la proposition
            fetch('../../data/propositions.json')
                .then(response => response.json())
                .then(data => {
                    const proposal = data.propositions.find(p => p.id === proposalId);
                    if (proposal) {
                        showEditProposalModal(proposal);
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement:', error);
                });
        }
        
        // Fonction pour afficher le modal de modification
        function showEditProposalModal(proposal) {
            const modal = document.createElement('div');
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>✏️ Modifier la proposition</h3>
                        <button class="modal-close" onclick="closeModal()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="edit-proposal-form">
                            <input type="hidden" id="edit-proposal-id" value="${proposal.id}">
                            
                            <div class="form-group">
                                <label for="edit-titre">Titre *</label>
                                <input type="text" id="edit-titre" value="${escapeHtml(proposal.data.titre)}" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit-description">Description *</label>
                                <textarea id="edit-description" rows="4" required>${escapeHtml(proposal.data.description)}</textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit-categories">Catégories</label>
                                <select id="edit-categories" multiple>
                                    <option value="Urbanisme & Logement" ${proposal.data.categories.includes('Urbanisme & Logement') ? 'selected' : ''}>Urbanisme & Logement</option>
                                    <option value="Environnement & Nature" ${proposal.data.categories.includes('Environnement & Nature') ? 'selected' : ''}>Environnement & Nature</option>
                                    <option value="Mobilité & Transport" ${proposal.data.categories.includes('Mobilité & Transport') ? 'selected' : ''}>Mobilité & Transport</option>
                                    <option value="Vie sociale & Solidarité" ${proposal.data.categories.includes('Vie sociale & Solidarité') ? 'selected' : ''}>Vie sociale & Solidarité</option>
                                    <option value="Éducation & Jeunesse" ${proposal.data.categories.includes('Éducation & Jeunesse') ? 'selected' : ''}>Éducation & Jeunesse</option>
                                    <option value="Santé & Bien-être" ${proposal.data.categories.includes('Santé & Bien-être') ? 'selected' : ''}>Santé & Bien-être</option>
                                    <option value="Culture & Sport" ${proposal.data.categories.includes('Culture & Sport') ? 'selected' : ''}>Culture & Sport</option>
                                    <option value="Économie & Commerce" ${proposal.data.categories.includes('Économie & Commerce') ? 'selected' : ''}>Économie & Commerce</option>
                                    <option value="Services publics" ${proposal.data.categories.includes('Services publics') ? 'selected' : ''}>Services publics</option>
                                    <option value="Autre" ${proposal.data.categories.includes('Autre') ? 'selected' : ''}>Autre</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit-beneficiaires">Bénéficiaires</label>
                                <input type="text" id="edit-beneficiaires" value="${escapeHtml(proposal.data.beneficiaires || '')}">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit-cout">Coût estimé</label>
                                <input type="text" id="edit-cout" value="${escapeHtml(proposal.data.cout || '')}">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                        <button class="btn btn-primary" onclick="saveEditedProposal()">💾 Sauvegarder & Intégrer</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        }
        
        // Fonction pour fermer le modal
        
        // Fonction pour sauvegarder la proposition modifiée
        function saveEditedProposal() {
            const proposalId = document.getElementById('edit-proposal-id').value;
            const titre = document.getElementById('edit-titre').value;
            const description = document.getElementById('edit-description').value;
            const categories = Array.from(document.getElementById('edit-categories').selectedOptions).map(option => option.value);
            const beneficiaires = document.getElementById('edit-beneficiaires').value;
            const cout = document.getElementById('edit-cout').value;
            
            if (!titre || !description) {
                alert('Le titre et la description sont obligatoires');
                return;
            }
            
            // Envoyer les modifications
            fetch('citizen-proposals-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'edit_and_integrate',
                    proposalId: proposalId,
                    data: {
                        titre: titre,
                        description: description,
                        categories: categories,
                        beneficiaires: beneficiaires,
                        cout: cout
                    }
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Proposition modifiée et intégrée au programme avec succès !');
                    closeModal();
                    loadCitizenProposals();
                } else {
                    alert('Erreur: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la sauvegarde');
            });
        }
        
        // Fonction pour échapper le HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Fonction pour mettre à jour le statut d'une proposition citoyenne
        function updateCitizenProposalStatus(proposalId, status, callback = null) {
            if (!confirm(`Êtes-vous sûr de vouloir ${status === 'approved' ? 'approuver' : 'rejeter'} cette proposition ?`)) {
                return;
            }
            
            fetch('citizen-proposals-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update_status',
                    proposalId: proposalId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCitizenProposals();
                    if (callback && typeof callback === 'function') {
                        callback();
                    }
                } else {
                    alert('Erreur: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la mise à jour');
            });
        }
        
        // Fonction pour intégrer une proposition au programme
        function integrateCitizenProposal(proposalId) {
            if (!confirm('Êtes-vous sûr de vouloir intégrer cette proposition au programme principal ?')) {
                return;
            }
            
            fetch('citizen-proposals-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'integrate',
                    proposalId: proposalId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Proposition intégrée au programme avec succès !');
                    loadCitizenProposals();
                } else {
                    alert('Erreur: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'intégration');
            });
        }
        
        // Variables globales pour la gestion des modifications
        let hasUnsavedChanges = false;
        let autoSaveTimeout = null;
        let autoSaveInterval = null;
        
        
        // Fonction pour marquer un formulaire comme modifié
        function markFormChanged(sectionName) {
            console.log('Changement détecté dans la section:', sectionName);
            
            // Marquer qu'il y a des changements non sauvegardés
            hasUnsavedChanges = true;
            showFloatingSaveBtn();
            
            // Programmer l'auto-sauvegarde
            scheduleAutoSave();
        }
        
        // Fonction pour afficher le bouton de sauvegarde flottant
        function showFloatingSaveBtn() {
            const floatingBtn = document.getElementById('floating-save-btn');
            if (floatingBtn && hasUnsavedChanges) {
                floatingBtn.classList.add('visible');
                floatingBtn.innerHTML = '<i class="fas fa-save"></i> Sauvegarder tout';
                floatingBtn.classList.remove('saving', 'success', 'error');
            }
        }
        
        // Fonction pour masquer le bouton de sauvegarde flottant
        function hideFloatingSaveBtn() {
            const floatingBtn = document.getElementById('floating-save-btn');
            if (floatingBtn) {
                floatingBtn.classList.remove('visible', 'saving', 'success', 'error');
                floatingBtn.innerHTML = '<i class="fas fa-save"></i> Sauvegarder tout';
            }
        }
        
        // Fonction pour programmer l'auto-sauvegarde
        function scheduleAutoSave() {
            // Annuler l'auto-sauvegarde précédente
            if (autoSaveTimeout) {
                clearTimeout(autoSaveTimeout);
            }
            
            // Programmer une nouvelle auto-sauvegarde dans 3 secondes
            autoSaveTimeout = setTimeout(() => {
                if (hasUnsavedChanges) {
                    autoSaveAllChanges();
                }
            }, 3000);
        }
        
        // Fonction pour l'auto-sauvegarde
        function autoSaveAllChanges() {
            const forms = document.querySelectorAll('form[id$="-form-element"]');
            let savedCount = 0;
            
            forms.forEach(form => {
                const saveBtn = form.querySelector('.btn-save');
                if (saveBtn && !saveBtn.disabled) {
                    // Simuler la soumission du formulaire
                    const formData = new FormData(form);
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        savedCount++;
                        saveBtn.disabled = true;
                        
                        if (savedCount === forms.length) {
                            hasUnsavedChanges = false;
                            hideFloatingSaveBtn();
                            showAutoSaveIndicator();
                        }
                    })
                    .catch(error => {
                        console.error('Erreur auto-sauvegarde:', error);
                    });
                }
            });
        }
        
        // Fonction pour sauvegarder manuellement tous les changements
        function saveAllChanges() {
            const floatingBtn = document.getElementById('floating-save-btn');
            
            // Vérifier s'il y a des changements
            if (!hasUnsavedChanges) {
                showToast('Aucune modification à sauvegarder', 'warning');
                return;
            }
            
            // Animation de sauvegarde
            floatingBtn.classList.add('saving');
            floatingBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sauvegarde...';
            
            // Trouver tous les formulaires modifiés
            const forms = document.querySelectorAll('form[id$="-form-element"]');
            let pendingSubmissions = 0;
            let completedSubmissions = 0;
            let hasErrors = false;
            
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input, textarea, select');
                let formHasChanges = false;
                
                // Vérifier si le formulaire a des changements
                inputs.forEach(input => {
                    if (input.type === 'file' && input.files.length > 0) {
                        formHasChanges = true;
                    } else if (input.value !== input.defaultValue) {
                        formHasChanges = true;
                    }
                });
                
                if (formHasChanges) {
                    pendingSubmissions++;
                    
                    const formData = new FormData(form);
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (response.ok) {
                            completedSubmissions++;
                            checkAllSubmissionsComplete();
                        } else {
                            hasErrors = true;
                            completedSubmissions++;
                            checkAllSubmissionsComplete();
                        }
                    })
                    .catch(error => {
                        console.error('Erreur de sauvegarde:', error);
                        hasErrors = true;
                        completedSubmissions++;
                        checkAllSubmissionsComplete();
                    });
                }
            });
            
            function checkAllSubmissionsComplete() {
                if (completedSubmissions >= pendingSubmissions) {
                    if (hasErrors) {
                        floatingBtn.classList.remove('saving');
                        showToast('Erreur lors de la sauvegarde', 'error');
                        showFloatingSaveBtn();
                    } else {
                        floatingBtn.classList.remove('saving');
                        showToast('Toutes les modifications ont été sauvegardées', 'success');
                        
                        // Réinitialiser les changements
                        hasUnsavedChanges = false;
                        setTimeout(() => {
                            hideFloatingSaveBtn();
                        }, 1500);
                    }
                }
            }
            
            // Si aucun formulaire à soumettre
            if (pendingSubmissions === 0) {
                floatingBtn.classList.remove('saving');
                showToast('Aucun formulaire à sauvegarder', 'info');
                showFloatingSaveBtn();
            }
        }
        
        // Fonction pour afficher l'indicateur d'auto-sauvegarde
        function showAutoSaveIndicator() {
            const indicator = document.getElementById('auto-save-indicator');
            if (indicator) {
                indicator.classList.add('visible');
                setTimeout(() => {
                    indicator.classList.remove('visible');
                }, 3000);
            }
        }
        
        // Fonction pour afficher des notifications toast
        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            
            // Créer l'élément toast
            const toast = document.createElement('div');
            toast.className = `toast-notification ${type} visible`;
            
            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };
            
            toast.innerHTML = `
                <i class="${icons[type] || icons.info}"></i>
                <div class="toast-content">${message}</div>
                <button class="toast-close" onclick="hideToast(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            // Auto-hide après la durée spécifiée
            setTimeout(() => {
                hideToast(toast.querySelector('.toast-close'));
            }, duration);
        }
        
        // Fonction pour masquer un toast
        function hideToast(closeBtn) {
            const toast = closeBtn.closest('.toast-notification');
            if (toast) {
                toast.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        }
        
        // Réinitialiser les boutons de sauvegarde au chargement
        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser le glisser-déposer
            initializeDragAndDrop();
            
            // Ajouter des event listeners pour tous les champs
            document.addEventListener('input', function(e) {
                if (e.target.matches('input, textarea, select')) {
                    const form = e.target.closest('form[id$="-form-element"]');
                    if (form) {
                        const sectionName = form.id.replace('-form-element', '');
                        markFormChanged(sectionName);
                    }
                }
            });
            
            // Event listener spécial pour les uploads de fichiers
            document.addEventListener('change', function(e) {
                if (e.target.matches('input[type="file"]')) {
                    const form = e.target.closest('form[id$="-form-element"]');
                    if (form) {
                        const sectionName = form.id.replace('-form-element', '');
                        markFormChanged(sectionName);
                    }
                }
            });
        });
        
        // Fonction pour initialiser le glisser-déposer
        function initializeDragAndDrop() {
            const grids = ['programme-grid', 'equipe-grid', 'rendez_vous-grid', 'charte-grid'];
            
            grids.forEach(gridId => {
                const grid = document.getElementById(gridId);
                if (!grid) return;
                
                // Ajouter les événements au conteneur
                grid.addEventListener('dragover', handleDragOver);
                grid.addEventListener('drop', handleDrop);
                grid.addEventListener('dragenter', handleDragEnter);
                grid.addEventListener('dragleave', handleDragLeave);
                
                const cards = grid.querySelectorAll('.component-card[draggable="true"]');
                
                cards.forEach(card => {
                    card.addEventListener('dragstart', handleDragStart);
                    card.addEventListener('dragend', handleDragEnd);
                });
            });
        }
        
        let draggedElement = null;
        let dropIndicator = null;
        let lastDragOverTime = 0;
        
        function handleDragStart(e) {
            draggedElement = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', this.getAttribute('data-id'));
            
            // Activer l'écartement des cartes
            const grid = this.closest('.components-grid');
            grid.classList.add('drag-active');
            
            // Ajouter la classe placeholder aux autres cartes
            const cards = grid.querySelectorAll('.component-card[draggable="true"]:not(.dragging)');
            cards.forEach(card => card.classList.add('drag-placeholder'));
        }
        
        function handleDragEnd(e) {
            this.classList.remove('dragging');
            draggedElement = null;
            
            // Nettoyer les classes et indicateurs
            const cards = document.querySelectorAll('.component-card');
            cards.forEach(card => {
                card.classList.remove('drag-over', 'drag-placeholder');
            });
            
            // Désactiver l'écartement des cartes
            const grids = document.querySelectorAll('.components-grid');
            grids.forEach(grid => grid.classList.remove('drag-active'));
            
            if (dropIndicator) {
                dropIndicator.remove();
                dropIndicator = null;
            }
        }
        
        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            
            if (!draggedElement) return;
            
            // Throttling pour éviter le scintillement
            const now = Date.now();
            if (now - lastDragOverTime < 16) { // ~60fps
                return;
            }
            lastDragOverTime = now;
            
            const grid = e.currentTarget;
            const afterElement = getDragAfterElement(grid, e.clientY);
            
            // Éviter de recréer l'indicateur si la position n'a pas changé
            const currentIndicator = grid.querySelector('.drop-indicator');
            if (currentIndicator) {
                const currentPosition = currentIndicator.nextSibling;
                if (currentPosition === afterElement) {
                    return; // Position inchangée, ne pas recréer
                }
            }
            
            // Supprimer l'ancien indicateur
            if (dropIndicator) {
                dropIndicator.remove();
                dropIndicator = null;
            }
            
            // Créer le nouvel indicateur
            dropIndicator = document.createElement('div');
            dropIndicator.className = 'drop-indicator active';
            
            if (afterElement === null) {
                // Insérer à la fin
                grid.appendChild(dropIndicator);
            } else {
                // Insérer avant l'élément cible
                grid.insertBefore(dropIndicator, afterElement);
            }
        }
        
        function handleDragEnter(e) {
            e.preventDefault();
        }
        
        function handleDragLeave(e) {
            // Ne pas supprimer l'indicateur si on reste dans le conteneur
            if (!e.currentTarget.contains(e.relatedTarget)) {
                if (dropIndicator) {
                    dropIndicator.remove();
                    dropIndicator = null;
                }
            }
        }
        
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.component-card[draggable="true"]:not(.dragging)')];
            
            // Si pas d'éléments, retourner null (insérer à la fin)
            if (draggableElements.length === 0) {
                return null;
            }
            
            // Trouver l'élément le plus proche du curseur avec une logique plus précise
            for (let i = 0; i < draggableElements.length; i++) {
                const element = draggableElements[i];
                const rect = element.getBoundingClientRect();
                const elementTop = rect.top;
                const elementBottom = rect.bottom;
                const elementHeight = elementBottom - elementTop;
                
                // Zone de détection : 30% du haut et 30% du bas de chaque carte
                const detectionZone = elementHeight * 0.3;
                const topZone = elementTop + detectionZone;
                const bottomZone = elementBottom - detectionZone;
                
                // Si le curseur est dans la zone du haut, insérer avant cet élément
                if (y >= elementTop && y <= topZone) {
                    return element;
                }
                
                // Si le curseur est dans la zone du bas, insérer après cet élément
                if (y >= bottomZone && y <= elementBottom) {
                    return element.nextSibling;
                }
                
                // Si le curseur est entre deux éléments
                if (i < draggableElements.length - 1) {
                    const nextElement = draggableElements[i + 1];
                    const nextRect = nextElement.getBoundingClientRect();
                    
                    if (y > elementBottom && y < nextRect.top) {
                        return nextElement;
                    }
                }
            }
            
            // Si on arrive ici, insérer à la fin
            return null;
        }
        
        function handleDrop(e) {
            e.preventDefault();
            
            if (!draggedElement) return;
            
            const grid = e.currentTarget;
            const afterElement = getDragAfterElement(grid, e.clientY);
            
            // Insérer l'élément à la position calculée
            if (afterElement === null) {
                // Insérer à la fin
                grid.appendChild(draggedElement);
            } else {
                // Insérer avant l'élément cible
                grid.insertBefore(draggedElement, afterElement);
            }
            
            // Sauvegarder le nouvel ordre
            saveNewOrder(grid);
            
            // Nettoyer l'indicateur
            if (dropIndicator) {
                dropIndicator.remove();
                dropIndicator = null;
            }
        }
        
        function saveNewOrder(grid) {
            const cards = grid.querySelectorAll('.component-card[draggable="true"]');
            const order = Array.from(cards).map(card => card.getAttribute('data-id'));
            
            const gridId = grid.id;
            let type = 'members'; // par défaut
            
            if (gridId.includes('programme')) {
                type = 'proposals';
            } else if (gridId.includes('equipe')) {
                type = 'members';
            } else if (gridId.includes('rendez_vous')) {
                type = 'events';
            } else if (gridId.includes('charte')) {
                type = 'principles';
            }
            
            // Envoyer la nouvelle ordre au serveur
            const formData = new FormData();
            formData.append('action', 'reorder_' + type);
            formData.append('order', JSON.stringify(order));
            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log('Ordre sauvegardé:', data);
                // Optionnel: afficher un message de succès
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde:', error);
            });
        }
        
        // Fonctions de sauvegarde pour les nouveaux modals
        function saveMember() {
            const form = document.getElementById('edit-member-form');
            const formData = new FormData(form);
            
            // Ajouter l'action
            formData.append('action', 'update_member');
            formData.append('section', 'equipe');
            
            // Ajouter le token CSRF
            formData.append('csrf_token', '<?= generate_csrf_token() ?>');
            
            // Envoyer les données
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse serveur:', data);
                if (data.success) {
                    forceCloseModal('editMemberModal');
                    showToast(data.message + ' - Rechargement automatique...', 'success');
                    // Recharger automatiquement après 1 seconde
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde:', error);
                showToast('Erreur lors de la sauvegarde', 'error');
            });
        }
        
        function saveAddMember() {
            const form = document.getElementById('add-member-form');
            if (!form) {
                console.error('Formulaire add-member-form non trouvé');
                showToast('Erreur: formulaire non trouvé', 'error');
                return;
            }
            
            const formData = new FormData(form);
            
            // Ajouter l'action
            formData.append('action', 'add_member');
            formData.append('section', 'equipe');
            formData.append('csrf_token', '<?= generate_csrf_token() ?>');
            
            console.log('Envoi des données pour ajout membre:', Object.fromEntries(formData));
            
            // Envoyer les données
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse serveur:', data);
                if (data.success) {
                    forceCloseModal('addMemberModal');
                    showToast(data.message + ' - Rechargement automatique...', 'success');
                    // Recharger automatiquement après 1 seconde
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde:', error);
                showToast('Erreur lors de la sauvegarde', 'error');
            });
        }
        
        function saveEvent() {
            // Choisir le bon formulaire selon le modal ouvert ou la présence d'un id
            const addForm = document.getElementById('add-event-form');
            const editForm = document.getElementById('edit-event-form');
            const editModal = document.getElementById('editEventModal');
            const addModal = document.getElementById('addEventModal');
            let form = null;
            if (editModal && editModal.style.display !== 'none') {
                form = editForm;
            } else if (addModal && addModal.style.display !== 'none') {
                form = addForm;
            } else if (editForm && (editForm.querySelector('#edit-event-id')?.value || '').toString().length > 0) {
                form = editForm;
            } else if (addForm) {
                form = addForm;
            }
            if (!form) {
                console.error('Formulaire non trouvé');
                showToast('Erreur: formulaire non trouvé', 'error');
                return;
            }
            
            const formData = new FormData(form);
            
            // Ajouter l'action
            formData.append('action', 'update_event');
            formData.append('section', 'rendez_vous');
            formData.append('csrf_token', '<?= generate_csrf_token() ?>');
            
            console.log('Envoi des données:', Object.fromEntries(formData));
            
            // Envoyer les données
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Réponse reçue:', response.status, response.statusText);
                return response.json();
            })
            .then(data => {
                console.log('Réponse serveur:', data);
                if (data.success) {
                    closeModal('addEventModal');
                    closeModal('editEventModal');
                    showToast(data.message, 'success');
                    // Mettre à jour la grille sans reload
                    try {
                        if (data.event && data.event.id) {
                            const grid = document.getElementById('rendez_vous-grid');
                            if (grid) {
                                const existing = grid.querySelector(`.component-card[data-id="${data.event.id}"]`);
                                const cardHtml = `
                                    <div class="component-card" draggable="true" data-id="${data.event.id}">
                                        <div class="drag-handle"><i class="fas fa-grip-vertical"></i></div>
                                        <div class="event-date"><i class="fas fa-calendar-alt"></i> ${data.event.date || ''}</div>
                                        <div class="component-title">${data.event.title || ''}</div>
                                        <div class="component-description">${data.event.description || ''}<br>
                                            <small><i class="fas fa-map-marker-alt"></i> ${data.event.location || ''}</small>
                                        </div>
                                        <div class="component-actions">
                                            <button class="btn-component edit" onclick="editEvent(${data.event.id})"><i class="fas fa-edit"></i> Modifier</button>
                                            <button class="btn-component delete" onclick="deleteEvent(${data.event.id})"><i class="fas fa-trash"></i> Supprimer</button>
                                        </div>
                                    </div>`;
                                if (existing) {
                                    existing.outerHTML = cardHtml;
                                } else {
                                    // Insérer avant la carte d'ajout
                                    const addCard = grid.querySelector('.add-card');
                                    if (addCard) {
                                        addCard.insertAdjacentHTML('beforebegin', cardHtml);
                                    } else {
                                        grid.insertAdjacentHTML('beforeend', cardHtml);
                                    }
                                }
                            }
                        }
                    } catch(e) { console.warn('update grid error', e); }
                    // Rester sur la même section
                    const last = 'rendez_vous';
                    try { localStorage.setItem('adminLastSection', last); } catch(_) {}
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde:', error);
                showToast('Erreur lors de la sauvegarde', 'error');
            });
        }
        
        function savePrinciple() {
            const form = document.getElementById('add-principle-form');
            const formData = new FormData(form);
            formData.append('title', document.getElementById('add-principle-title').value || '');
            formData.append('description', document.getElementById('add-principle-description').value || '');
            formData.append('thematique', document.getElementById('add-principle-theme').value || '');
            
            // Ajouter l'action
            formData.append('action', 'update_principle');
            formData.append('section', 'charte');
            formData.append('csrf_token', '<?= generate_csrf_token() ?>');
            
            // Envoyer les données
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Principe sauvegardé:', data);
                if (data.success) {
                    closeModal('addPrincipleModal');
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde:', error);
                showToast('Erreur lors de la sauvegarde', 'error');
            });
        }

        function saveEditedPrinciple() {
            const form = document.getElementById('edit-principle-form');
            const formData = new FormData(form);
            formData.append('action', 'update_principle');
            formData.append('section', 'charte');
            formData.append('csrf_token', '<?= generate_csrf_token() ?>');
            fetch('', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeModal('editPrincipleModal');
                        showToast('Principe mis à jour', 'success');
                        setTimeout(() => window.location.reload(), 800);
                    } else {
                        showToast(data.message || 'Erreur lors de la sauvegarde', 'error');
                    }
                })
                .catch(e => { console.error(e); showToast('Erreur lors de la sauvegarde', 'error'); });
        }
        
        function saveHero() {
            const form = document.getElementById('hero-form');
            if (!form) {
                console.error('Formulaire hero non trouvé');
                showToast('Erreur: formulaire non trouvé', 'error');
                return;
            }
            
            const formData = new FormData(form);
            
            // Ajouter l'action
            formData.append('action', 'update_hero');
            formData.append('section', 'hero');
            formData.append('csrf_token', '<?= generate_csrf_token() ?>');
            
            console.log('Envoi des données hero:', Object.fromEntries(formData));
            
            // Envoyer les données
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse serveur hero:', data);
                if (data.success) {
                    closeModal('heroModal');
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde hero:', error);
                showToast('Erreur lors de la sauvegarde', 'error');
            });
        }
        
        function saveProgramme() {
            const form = document.getElementById('programme-form');
            if (!form) {
                console.error('Formulaire programme non trouvé');
                showToast('Erreur: formulaire non trouvé', 'error');
                return;
            }
            
            const formData = new FormData(form);
            
            // Ajouter l'action
            formData.append('action', 'update_programme_section');
            formData.append('section', 'programme');
            formData.append('csrf_token', '<?= generate_csrf_token() ?>');
            
            console.log('Envoi des données programme:', Object.fromEntries(formData));
            
            // Envoyer les données
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse serveur programme:', data);
                if (data.success) {
                    closeModal('programmeModal');
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde programme:', error);
                showToast('Erreur lors de la sauvegarde', 'error');
            });
        }
        
        function saveCitation() {
            const form = document.getElementById('citation-form');
            if (!form) {
                console.error('Formulaire citation non trouvé');
                showToast('Erreur: formulaire non trouvé', 'error');
                return;
            }
            
            const formData = new FormData(form);
            const citationId = document.getElementById('citation_id').value;
            
            // Ajouter l'action selon l'ID de la citation
            formData.append('action', 'update_citation' + citationId);
            formData.append('section', 'citations');
            formData.append('csrf_token', '<?= generate_csrf_token() ?>');
            
            console.log('Envoi des données citation:', Object.fromEntries(formData));
            
            // Envoyer les données
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse serveur citation:', data);
                if (data.success) {
                    closeModal('citationModal');
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Erreur lors de la sauvegarde citation:', error);
                showToast('Erreur lors de la sauvegarde', 'error');
            });
        }
    </script>

    <!-- Modal Équipe -->
    <div id="equipeModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>👥 Gestion de l'Équipe</h3>
                <button onclick="closeModal('equipeModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Gestion complète des membres de l'équipe</p>
                <p>Fonctionnalité en cours de développement...</p>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('equipeModal')" class="btn btn-secondary">Fermer</button>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Membre -->
    <div id="editMemberModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Modifier le Membre</h3>
                <button onclick="forceCloseModal('editMemberModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-member-form" enctype="multipart/form-data">
                    <input type="hidden" id="edit-member-id" name="id" value="">
                    
                    <div class="form-group">
                        <label for="edit-member-name">Nom *</label>
                        <input type="text" id="edit-member-name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-member-role">Rôle *</label>
                        <input type="text" id="edit-member-role" name="role" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-member-description">Description</label>
                        <textarea id="edit-member-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-member-image">Image du membre</label>
                        <input type="file" id="edit-member-image" name="image" accept="image/*">
                        <small class="form-text">Formats acceptés : JPG, PNG, WebP (max 2MB)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editMemberModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="saveMember()" class="btn btn-primary">Sauvegarder</button>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Événement -->
    <div id="addEventModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📅 Ajouter un Événement</h3>
                <button onclick="closeModal('addEventModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-event-form">
                    <div class="form-group">
                        <label for="add-event-title">Titre *</label>
                        <input type="text" id="add-event-title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="add-event-description">Description</label>
                        <textarea id="add-event-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="add-event-date">Date *</label>
                        <input type="datetime-local" id="add-event-date" name="date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="add-event-location">Lieu</label>
                        <input type="text" id="add-event-location" name="location">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('addEventModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="saveEvent()" class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </div>

    <!-- Modal Modifier Événement -->
    <div id="editEventModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Modifier l'Événement</h3>
                <button onclick="closeModal('editEventModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-event-form">
                    <input type="hidden" id="edit-event-id" name="id" value="">
                    
                    <div class="form-group">
                        <label for="edit-event-title">Titre *</label>
                        <input type="text" id="edit-event-title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-event-description">Description</label>
                        <textarea id="edit-event-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-event-date">Date *</label>
                        <input type="datetime-local" id="edit-event-date" name="date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-event-location">Lieu</label>
                        <input type="text" id="edit-event-location" name="location">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editEventModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="saveEvent()" class="btn btn-primary">Sauvegarder</button>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Principe -->
    <div id="addPrincipleModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📜 Ajouter un Principe</h3>
                <button onclick="closeModal('addPrincipleModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-principle-form">
                    <div class="form-group">
                        <label for="add-principle-title">Titre *</label>
                        <input type="text" id="add-principle-title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="add-principle-description">Description *</label>
                        <textarea id="add-principle-description" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="add-principle-theme">Thématique (gris italique)</label>
                        <input type="text" id="add-principle-theme" placeholder="Ex: Identité locale">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('addPrincipleModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="savePrinciple()" class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </div>

    <!-- Modal Modifier Principe -->
    <div id="editPrincipleModal" class="modal-overlay" style="display:none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✏️ Modifier un principe</h3>
                <button onclick="closeModal('editPrincipleModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-principle-form">
                    <input type="hidden" id="edit-principle-id" name="id" value="">
                    <div class="form-group">
                        <label for="edit-principle-title">Titre *</label>
                        <input type="text" id="edit-principle-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-principle-description">Description *</label>
                        <textarea id="edit-principle-description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-principle-theme">Thématique (gris italique)</label>
                        <input type="text" id="edit-principle-theme" name="thematique" placeholder="Ex: Identité locale">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('editPrincipleModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="saveEditedPrinciple()" class="btn btn-primary">Enregistrer</button>
            </div>
        </div>
    </div>

    <!-- Modal de recadrage d'image -->
    <div id="cropImageModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 900px;">
            <div class="modal-header">
                <h2 id="cropModalTitle">Recadrer l'image</h2>
                <button onclick="closeCropModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 1rem; padding: 1rem; background: #f0f8ff; border-radius: 8px; border-left: 4px solid #4e9eb0;">
                    <p id="cropInstructions" style="margin: 0; color: #004a6d;">
                        <i class="fas fa-info-circle"></i> <strong>Instructions :</strong> Ajustez le cadrage en déplaçant et redimensionnant la zone de sélection.
                    </p>
                </div>
                <div style="max-height: 500px; overflow: hidden; background: #000; border-radius: 8px;">
                    <img id="cropImage" style="max-width: 100%; display: block;">
                </div>
                <div style="margin-top: 1rem; display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background: #f5f5f5; border-radius: 6px;">
                    <div style="font-size: 0.9rem; color: #666;">
                        <span id="cropDimensions">Dimensions : -</span>
                    </div>
                    <div>
                        <button onclick="cropper && cropper.reset()" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                            <i class="fas fa-undo"></i> Réinitialiser
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeCropModal()" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="validateCrop()" class="btn btn-primary">
                    <i class="fas fa-check"></i> Valider et optimiser
                </button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales pour le crop
        let cropper = null;
        let currentCropConfig = null;
        let currentInputFile = null;
        
        // Configuration des ratios selon le type d'image
        const cropPresets = {
            'hero': {
                ratio: 16 / 6, // ≈ 2.6667:1
                title: 'Recadrer l\'image Hero',
                instructions: 'Format 16:6 (panoramique) - Idéal pour le fond de la section principale'
            },
            'hero_mobile': {
                ratio: 4 / 3,
                title: 'Recadrer l\'image Hero (mobile)',
                instructions: 'Format 4:3 (mobile) - Garder toutes les personnes visibles'
            },
            'citation': {
                ratio: 4 / 3,
                title: 'Recadrer l\'image Citation/Transition',
                instructions: 'Format 4:3 (paysage) - Recommandé pour les transitions'
            },
            'member': {
                ratio: 3 / 4,
                title: 'Recadrer la photo Membre',
                instructions: 'Format 3:4 (portrait) - Cadrez le visage au centre avec espace au-dessus'
            },
            'standard': {
                ratio: 4 / 3,
                title: 'Recadrer l\'image',
                instructions: 'Format standard 4:3'
            }
        };
        
        // Ouvrir le modal de crop
        function openCropModal(file, inputElement, preset = 'hero') {
            console.log('🚀 openCropModal appelée avec:', { file: file.name, preset, inputElement });
            
            currentInputFile = inputElement;
            currentCropConfig = cropPresets[preset] || cropPresets.standard;
            
            console.log('📋 Configuration crop:', currentCropConfig);
            
            // Vérifier que les éléments existent
            const modalTitle = document.getElementById('cropModalTitle');
            const modalInstructions = document.getElementById('cropInstructions');
            const modalImage = document.getElementById('cropImage');
            const modal = document.getElementById('cropImageModal');
            
            console.log('🔍 Éléments trouvés:', {
                modalTitle: !!modalTitle,
                modalInstructions: !!modalInstructions,
                modalImage: !!modalImage,
                modal: !!modal
            });
            
            if (!modalTitle || !modalInstructions || !modalImage || !modal) {
                console.error('❌ Éléments du modal manquants !');
                alert('Erreur: Éléments du modal de crop non trouvés. Vérifiez la console.');
                return;
            }
            
            // Mettre à jour le titre et les instructions
            modalTitle.textContent = currentCropConfig.title;
            modalInstructions.innerHTML = 
                '<i class="fas fa-info-circle"></i> <strong>Instructions :</strong> ' + currentCropConfig.instructions;
            
            console.log('📝 Titre et instructions mis à jour');
            
            // Lire le fichier
            const reader = new FileReader();
            reader.onload = function(e) {
                console.log('📖 Fichier lu, taille:', e.target.result.length);
                
                modalImage.src = e.target.result;
                
                // Afficher le modal
                modal.style.display = 'flex';
                console.log('👁️ Modal affiché');
                
                // Vérifier que Cropper.js est disponible
                if (typeof Cropper === 'undefined') {
                    console.error('❌ Cropper.js non chargé !');
                    alert('Erreur: Cropper.js n\'est pas chargé. Vérifiez la connexion internet.');
                    return;
                }
                
                // Initialiser Cropper.js
                if (cropper) {
                    cropper.destroy();
                }
                
                console.log('🎨 Initialisation de Cropper.js...');
                cropper = new Cropper(modalImage, {
                    aspectRatio: currentCropConfig.ratio,
                    viewMode: 2,
                    dragMode: 'move',
                    guides: true,
                    center: true,
                    highlight: true,
                    background: true,
                    autoCropArea: 0.9,
                    responsive: true,
                    restore: true,
                    checkCrossOrigin: true,
                    checkOrientation: true,
                    crop: function(event) {
                        // Afficher les dimensions pendant le crop
                        const width = Math.round(event.detail.width);
                        const height = Math.round(event.detail.height);
                        document.getElementById('cropDimensions').textContent = 
                            'Dimensions : ' + width + ' × ' + height + ' px';
                    }
                });
                
                console.log('✅ Cropper.js initialisé avec succès');
            };
            
            reader.onerror = function(e) {
                console.error('❌ Erreur lecture fichier:', e);
                alert('Erreur lors de la lecture du fichier image.');
            };
            
            reader.readAsDataURL(file);
        }
        
        // Fermer le modal
        function closeCropModal() {
            document.getElementById('cropImageModal').style.display = 'none';
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            // Ne pas réinitialiser l'input file ici pour conserver le fichier croppé
        }
        
        // Valider le crop et uploader
        function validateCrop() {
            if (!cropper) return;
            
            const button = event.target;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Optimisation...';
            
            // Récupérer l'image croppée
            cropper.getCroppedCanvas({
                // Export plus défini pour limiter la pixellisation (16:6 ~ 2560x960)
                maxWidth: 2560,
                maxHeight: 960,
                fillColor: '#fff',
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            }).toBlob(function(blob) {
                // Créer le nom de fichier
                const timestamp = Date.now();
                const filename = 'cropped_' + timestamp + '.jpg';
                
                // Créer un File object à partir du Blob
                const file = new File([blob], filename, { type: 'image/jpeg' });
                
                // Créer un DataTransfer pour mettre à jour l'input
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                
                // Mettre à jour l'input file original
                if (currentInputFile) {
                    currentInputFile.files = dataTransfer.files;
                }
                
                // Fermer le modal
                closeCropModal();
                
                // Soumettre le formulaire automatiquement
                const form = currentInputFile.closest('form');
                if (form && currentInputFile) {
                    // Marquer le formulaire comme modifié, en déduisant la section depuis l'ID du formulaire
                    const formId = form.getAttribute('id') || '';
                    let sectionName = '';
                    if (formId.endsWith('-form-element')) {
                        sectionName = formId.replace('-form-element', '');
                    } else if (formId.endsWith('-form')) {
                        sectionName = formId.replace('-form', '');
                    }
                    if (sectionName) {
                        markFormChanged(sectionName);
                    } else {
                        // À défaut, forcer l'état "modifié" pour activer le bouton
                        hasUnsavedChanges = true;
                        showFloatingSaveBtn();
                    }
                    
                    // Message de succès
                    alert('Image recadrée avec succès ! Cliquez sur "Sauvegarder" pour appliquer les changements.');
                }
                
                button.disabled = false;
                button.innerHTML = originalText;
            }, 'image/jpeg', 0.95);
        }
        
        // Intercepter les inputs file pour afficher le modal
        function setupCropListeners() {
            console.log('🔧 Configuration des listeners de crop...');
            
            // Hero background
            const heroInput = document.getElementById('hero_background_image');
            console.log('Hero input trouvé:', heroInput);
            if (heroInput) {
                // Supprimer l'ancien onchange et le remplacer
                heroInput.removeAttribute('onchange');
                heroInput.onchange = function(e) {
                    console.log('📸 Hero image sélectionnée:', this.files[0]);
                    if (this.files && this.files[0]) {
                        openCropModal(this.files[0], this, 'hero');
                        // Marquer le formulaire comme modifié après le crop
                        markFormChanged('hero');
                        // Empêcher la soumission immédiate
                        e.stopPropagation();
                        e.preventDefault();
                    }
                };
            }
            
            // Citations backgrounds
            ['citation1', 'citation2', 'citation3', 'citation4'].forEach(function(citationId) {
                const input = document.getElementById(citationId + '_background_image');
                if (input) {
                    // Supprimer l'ancien onchange et le remplacer
                    input.removeAttribute('onchange');
                    input.onchange = function(e) {
                        console.log('📸 Citation image sélectionnée:', this.files[0]);
                        if (this.files && this.files[0]) {
                            openCropModal(this.files[0], this, 'citation');
                            // Marquer le formulaire comme modifié après le crop
                            markFormChanged(citationId);
                            e.stopPropagation();
                            e.preventDefault();
                        }
                    };
                }
            });

            // Ajout membre (photo)
            const addMemberPhoto = document.getElementById('member_photo');
            if (addMemberPhoto) {
                addMemberPhoto.onchange = function(e) {
                    console.log('📸 Photo (ajout membre) sélectionnée:', this.files[0]);
                    if (this.files && this.files[0]) {
                        openCropModal(this.files[0], this, 'member');
                        // Marquer la section équipe comme modifiée
                        markFormChanged('equipe');
                        e.stopPropagation();
                        e.preventDefault();
                    }
                };
            }

            // Édition membre (image)
            const editMemberImage = document.getElementById('edit-member-image');
            if (editMemberImage) {
                editMemberImage.onchange = function(e) {
                    console.log('📸 Image (édition membre) sélectionnée:', this.files[0]);
                    if (this.files && this.files[0]) {
                        openCropModal(this.files[0], this, 'member');
                        // Marquer la section équipe comme modifiée
                        markFormChanged('equipe');
                        e.stopPropagation();
                        e.preventDefault();
                    }
                };
            }
        }
        
        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupCropListeners);
        } else {
            setupCropListeners();
        }
        
        // Reconfigurer après chaque changement de contenu (pour les modals dynamiques)
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    setupCropListeners();
                }
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    </script>

</body>
</html>

