<?php
/**
 * Interface d'administration - Version refactorisée
 * Architecture modulaire et scalable
 */

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/AdminSection.php';
require_once __DIR__ . '/../includes/AdminModal.php';
require_once __DIR__ . '/sections/dashboard.php';
require_once __DIR__ . '/sections/hero.php';
require_once __DIR__ . '/sections/programme.php';
require_once __DIR__ . '/sections/citations.php';
require_once __DIR__ . '/sections/equipe.php';
require_once __DIR__ . '/sections/rendez_vous.php';
require_once __DIR__ . '/sections/charte.php';
require_once __DIR__ . '/sections/contact.php';
require_once __DIR__ . '/sections/mediatheque.php';
require_once __DIR__ . '/sections/gestion_utilisateurs.php';
require_once __DIR__ . '/sections/logs_securite.php';

// Vérifier l'authentification
check_auth();

// Charger les données du site
$site_content_file = __DIR__ . '/../../data/site_content.json';
$content = file_exists($site_content_file) ? json_decode(file_get_contents($site_content_file), true) : [];

// Calculer les statistiques
$programme_count = count($content['programme']['proposals'] ?? []);

// Traiter les soumissions de formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // Helpers lecture/écriture contenu site
        $siteFile = __DIR__ . '/../../data/site_content.json';
        $loadSite = function() use ($siteFile) {
            return file_exists($siteFile) ? json_decode(file_get_contents($siteFile), true) : [];
        };
        $saveSite = function($data) use ($siteFile) {
            if (!is_dir(dirname($siteFile))) { @mkdir(dirname($siteFile), 0755, true); }
            file_put_contents($siteFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        };
        
        switch ($action) {
            case 'update_hero':
                $heroSection = new HeroSection($content);
                $result = $heroSection->handleSubmission($_POST);
                break;
            case 'update_citations':
                $citationsSection = new CitationsSection($content);
                $result = $citationsSection->handleSubmission($_POST);
                break;
            case 'update_equipe':
                $equipeSection = new EquipeSection($content);
                $result = $equipeSection->handleSubmission($_POST);
                break;
            case 'update_contact':
                $contactSection = new ContactSection($content);
                $result = $contactSection->handleSubmission($_POST);
                break;
            case 'update_charte':
                $charteSection = new CharteSection($content);
                $result = $charteSection->handleSubmission($_POST);
                break;
            case 'update_mediatheque':
                $mediathequeSection = new MediathequeSection($content);
                $result = $mediathequeSection->handleSubmission($_POST);
                break;
            case 'update_rendez_vous':
                $rendezVousSection = new RendezVousSection($content);
                $result = $rendezVousSection->handleSubmission($_POST);
                break;
            // ===== CHARTE CRUD =====
            case 'add_principle': {
                $data = file_exists(__DIR__ . '/../../data/site_content.json') ? json_decode(file_get_contents(__DIR__ . '/../../data/site_content.json'), true) : [];
                $principles = $data['charte']['principles'] ?? [];
                $id = $_POST['id'] ?? uniqid('p_', true);
                $title = trim($_POST['title'] ?? '');
                if ($title === '') throw new Exception('Titre requis');
                $principles[] = [
                    'id' => $id,
                    'title' => $title,
                    'description' => trim($_POST['description'] ?? ''),
                    'thematique' => trim($_POST['thematique'] ?? ($_POST['source'] ?? ''))
                ];
                $data['charte']['principles'] = $principles;
                file_put_contents(__DIR__ . '/../../data/site_content.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $result = ['success' => true, 'message' => 'Principe ajouté'];
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                break; }
            case 'edit_principle': {
                $data = file_exists(__DIR__ . '/../../data/site_content.json') ? json_decode(file_get_contents(__DIR__ . '/../../data/site_content.json'), true) : [];
                $principles = $data['charte']['principles'] ?? [];
                $id = $_POST['id'] ?? '';
                if ($id === '') throw new Exception('ID manquant');
                foreach ($principles as &$p) {
                    if (($p['id'] ?? '') == $id) {
                        $p['title'] = trim($_POST['title'] ?? $p['title']);
                        $p['description'] = trim($_POST['description'] ?? ($p['description'] ?? ''));
                        $p['thematique'] = trim($_POST['thematique'] ?? ($p['thematique'] ?? ($p['source'] ?? '')));
                        break;
                    }
                }
                unset($p);
                $data['charte']['principles'] = $principles;
                file_put_contents(__DIR__ . '/../../data/site_content.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $result = ['success' => true, 'message' => 'Principe modifié'];
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                break; }
            case 'delete_principle': {
                $data = file_exists(__DIR__ . '/../../data/site_content.json') ? json_decode(file_get_contents(__DIR__ . '/../../data/site_content.json'), true) : [];
                $principles = $data['charte']['principles'] ?? [];
                $id = $_POST['id'] ?? '';
                if ($id === '') throw new Exception('ID manquant');
                $principles = array_values(array_filter($principles, function($p) use ($id) { return ($p['id'] ?? '') != $id; }));
                $data['charte']['principles'] = $principles;
                file_put_contents(__DIR__ . '/../../data/site_content.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $result = ['success' => true, 'message' => 'Principe supprimé'];
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                break; }

            // ===== RENDEZ-VOUS CRUD =====
            case 'add_event': {
                $data = file_exists(__DIR__ . '/../../data/site_content.json') ? json_decode(file_get_contents(__DIR__ . '/../../data/site_content.json'), true) : [];
                $events = $data['rendez_vous']['events'] ?? [];
                $id = $_POST['id'] ?? uniqid('ev_', true);
                $title = trim($_POST['title'] ?? '');
                if ($title === '') throw new Exception('Titre requis');
                $events[] = [
                    'id' => $id,
                    'title' => $title,
                    'description' => trim($_POST['description'] ?? ''),
                    'date' => trim($_POST['date'] ?? ''),
                    'location' => trim($_POST['location'] ?? '')
                ];
                $data['rendez_vous']['events'] = $events;
                file_put_contents(__DIR__ . '/../../data/site_content.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $result = ['success' => true, 'message' => 'Événement ajouté'];
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                break; }
            case 'edit_event': {
                $data = file_exists(__DIR__ . '/../../data/site_content.json') ? json_decode(file_get_contents(__DIR__ . '/../../data/site_content.json'), true) : [];
                $events = $data['rendez_vous']['events'] ?? [];
                $id = $_POST['id'] ?? '';
                if ($id === '') throw new Exception('ID manquant');
                foreach ($events as &$ev) {
                    if (($ev['id'] ?? '') == $id) {
                        $ev['title'] = trim($_POST['title'] ?? $ev['title']);
                        $ev['description'] = trim($_POST['description'] ?? ($ev['description'] ?? ''));
                        $ev['date'] = trim($_POST['date'] ?? ($ev['date'] ?? ''));
                        $ev['location'] = trim($_POST['location'] ?? ($ev['location'] ?? ''));
                        break;
                    }
                }
                unset($ev);
                $data['rendez_vous']['events'] = $events;
                file_put_contents(__DIR__ . '/../../data/site_content.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $result = ['success' => true, 'message' => 'Événement modifié'];
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                break; }
            case 'delete_event': {
                $data = file_exists(__DIR__ . '/../../data/site_content.json') ? json_decode(file_get_contents(__DIR__ . '/../../data/site_content.json'), true) : [];
                $events = $data['rendez_vous']['events'] ?? [];
                $id = $_POST['id'] ?? '';
                if ($id === '') throw new Exception('ID manquant');
                $events = array_values(array_filter($events, function($ev) use ($id) { return ($ev['id'] ?? '') != $id; }));
                $data['rendez_vous']['events'] = $events;
                file_put_contents(__DIR__ . '/../../data/site_content.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $result = ['success' => true, 'message' => 'Événement supprimé'];
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                break; }
            
            // ===== ÉQUIPE =====
            case 'add_member': {
                require_once __DIR__ . '/../includes/image_processor.php';
                $data = $loadSite();
                $members = $data['equipe']['members'] ?? [];
                $id = $_POST['id'] ?? uniqid('member_', true);
                $name = trim($_POST['name'] ?? '');
                $role = trim($_POST['role'] ?? '');
                $description = trim($_POST['description'] ?? '');
                if ($name === '' || $role === '') throw new Exception('Nom et rôle requis');
                $photo = null;
                // Chemin déjà uploadé (cropper)
                if (!empty($_POST['image_path'])) {
                    $photo = trim($_POST['image_path']);
                }
                if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $processor = new ImageProcessor();
                    $uploadsDir = realpath(__DIR__ . '/../../uploads');
                    if ($uploadsDir === false) { $uploadsDir = __DIR__ . '/../../uploads'; }
                    if (!is_dir($uploadsDir)) { @mkdir($uploadsDir, 0755, true); }
                    $r = $processor->processWithPreset($_FILES['image'], $uploadsDir, 'member', 'member');
                    if (!empty($r['success'])) { $photo = 'uploads/' . $r['filename']; }
                }
                // Normaliser le chemin image public
                $publicImage = $photo ? (str_starts_with($photo, 'uploads/') ? $photo : ('uploads/' . basename($photo))) : null;
                $members[] = [
                    'id' => $id,
                    'name' => $name,
                    'role' => $role,
                    'description' => $description,
                    // compatibilité: conserver les deux clés
                    'image' => $publicImage,
                    'photo' => $publicImage
                ];
                $data['equipe']['members'] = $members;
                $saveSite($data);
                $result = ['success' => true, 'message' => 'Membre ajouté'];
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                break; }
            case 'edit_member': {
                require_once __DIR__ . '/../includes/image_processor.php';
                $data = $loadSite();
                $members = $data['equipe']['members'] ?? [];
                $id = $_POST['id'] ?? '';
                if ($id === '') throw new Exception('ID manquant');
                foreach ($members as &$m) {
                    if (($m['id'] ?? '') == $id) {
                        $m['name'] = trim($_POST['name'] ?? $m['name']);
                        $m['role'] = trim($_POST['role'] ?? $m['role']);
                        $m['description'] = trim($_POST['description'] ?? ($m['description'] ?? ''));
                        // Chemin déjà uploadé (cropper)
                        if (!empty($_POST['image_path'])) {
                            $img = trim($_POST['image_path']);
                            $publicImage = str_starts_with($img, 'uploads/') ? $img : ('uploads/' . basename($img));
                            $m['image'] = $publicImage;
                            $m['photo'] = $publicImage;
                        }
                        if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                            $processor = new ImageProcessor();
                            $uploadsDir = realpath(__DIR__ . '/../../uploads');
                            if ($uploadsDir === false) { $uploadsDir = __DIR__ . '/../../uploads'; }
                            if (!is_dir($uploadsDir)) { @mkdir($uploadsDir, 0755, true); }
                            $r = $processor->processWithPreset($_FILES['image'], $uploadsDir, 'member', 'member');
                            if (!empty($r['success'])) {
                                $publicImage = 'uploads/' . $r['filename'];
                                $m['image'] = $publicImage;
                                $m['photo'] = $publicImage;
                            }
                        }
                        break;
                    }
                }
                unset($m);
                $data['equipe']['members'] = $members;
                $saveSite($data);
                $result = ['success' => true, 'message' => 'Membre modifié'];
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                break; }
            case 'delete_member': {
                $data = $loadSite();
                $members = $data['equipe']['members'] ?? [];
                $id = $_POST['id'] ?? '';
                if ($id === '') throw new Exception('ID manquant');
                $members = array_values(array_filter($members, function($m) use ($id) { return ($m['id'] ?? '') != $id; }));
                $data['equipe']['members'] = $members;
                $saveSite($data);
                $result = ['success' => true, 'message' => 'Membre supprimé'];
                if ($isAjax) { header('Content-Type: application/json'); echo json_encode($result); exit; }
                break; }
                
            case 'update_programme_section':
                $programmeSection = new ProgrammeSection($content);
                $result = $programmeSection->handleSubmission($_POST);
                break;
                
            case 'add_proposal':
            case 'edit_proposal':
            case 'delete_proposal':
            case 'approve_proposal':
            case 'reject_proposal':
            case 'restore_proposal':
            case 'set_citizen_status':
                $programmeSection = new ProgrammeSection($content);
                $result = $programmeSection->handleSubmission($_POST);
                break;
                
            default:
                throw new Exception('Action non reconnue: ' . $action);
        }
        
        if ($result['success']) {
            $_SESSION['admin_success'] = $result['message'];
        } else {
            throw new Exception($result['message'] ?? 'Erreur inconnue');
        }

        // Réponse AJAX générique
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }

    } catch (Exception $e) {
        $_SESSION['admin_error'] = $e->getMessage();
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    // Redirection pour éviter la resoumission (uniquement non-AJAX)
    if (!$isAjax) {
        $redirectSection = $_POST['redirect_section'] ?? 'dashboard';
        if ($redirectSection !== 'dashboard') {
            header('Location: ' . $_SERVER['PHP_SELF'] . '?section=' . $redirectSection);
        } else {
            header('Location: ' . $_SERVER['REQUEST_URI']);
        }
    }
    exit;
}

// Initialiser les sections
$sections = [
    new DashboardSection($content),
    new HeroSection($content),
    new ProgrammeSection($content, $programme_count),
    new CitationsSection($content),
    new EquipeSection($content),
    new RendezVousSection($content),
    new CharteSection($content),
    new ContactSection($content),
    new MediathequeSection($content),
    new GestionUtilisateursSection($content),
    new LogsSecuriteSection($content)
];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Osons Saint Paul</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS modulaire -->
    <link rel="stylesheet" href="../assets/css/admin-core.css">
    <link rel="stylesheet" href="../assets/css/admin-components.css">
    <link rel="stylesheet" href="../assets/css/admin-sections.css">
    
    <!-- JavaScript de navigation - DOIT être dans le head -->
    <script>
        // Fonction de navigation (nouveau système uniquement)
        function navigateToSection(sectionId) {if (window.AdminCore && window.AdminCore.navigateTo) {
                window.AdminCore.navigateTo(sectionId);
                return;
            }}
        
        // Fonctions pour la gestion des utilisateurs
        function editUser(userId) {
            // Charger les données de l'utilisateur et ouvrir le modal
            AdminModal.open('editUserModal', { id: userId });
        }
        
        function resetPassword(userId) {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser le mot de passe de cet utilisateur ?')) {
                AdminModal.open('resetPasswordModal', { id: userId });
            }
        }
        
        function deleteUser(userId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="${userId}">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function loadMoreLogs() {
            // Fonctionnalité de chargement de plus de logs
            AdminActions.showSuccess('Fonctionnalité de pagination à implémenter');
        }
        
        // ===== GESTION DES PROPOSITIONS =====
        
        // Ouvrir le modal de proposition
        function openProposalModal(mode = 'create', proposalData = null) {
            const modal = document.getElementById('proposalModal');
            const title = document.getElementById('proposalModalTitle');
            const action = document.getElementById('proposal-action');
            const saveBtn = document.getElementById('proposal-save-btn');
            const rejectBtn = document.getElementById('proposal-reject-btn');
        const citizenFields = document.getElementById('citizen-fields');
        const rejectionReason = document.getElementById('rejection-reason');
            
            // Réinitialiser le formulaire
            document.getElementById('proposal-form').reset();
            
            switch(mode) {
                case 'create':
                    title.innerHTML = '<i class="fas fa-plus"></i> Ajouter une proposition';
                    action.value = 'add_proposal';
                    saveBtn.textContent = 'Ajouter';
                    saveBtn.style.display = 'inline-block';
                    rejectBtn.style.display = 'none';
                    citizenFields.style.display = 'none';
                    rejectionReason.style.display = 'none';
                    break;
                    
                case 'edit':
                    title.innerHTML = '<i class="fas fa-edit"></i> Modifier la proposition';
                    action.value = 'edit_proposal';
                    saveBtn.textContent = 'Sauvegarder';
                    saveBtn.style.display = 'inline-block';
                    rejectBtn.style.display = 'none';
                    citizenFields.style.display = 'none';
                    rejectionReason.style.display = 'none';
                    
                    // Pré-remplir avec les données existantes
                    if (proposalData) {
                        document.getElementById('proposal-id').value = proposalData.id || '';
                        document.getElementById('proposal-title').value = proposalData.title || proposalData.data?.titre || '';
                        document.getElementById('proposal-description').value = proposalData.description || proposalData.data?.description || '';
                        document.getElementById('proposal-pillar').value = proposalData.pillar || 'proteger';
                        
                        // Construire les points clés avec catégories et bénéficiaires pour les propositions citoyennes
                        let items = proposalData.items || [];
                        if (proposalData.citizen_proposal && proposalData.data) {
                            items = proposalData.data.items || [];
                            if (proposalData.data.categories) {
                                items.unshift('Catégories: ' + proposalData.data.categories);
                            }
                            if (proposalData.data.beneficiaries) {
                                items.unshift('Bénéficiaires: ' + proposalData.data.beneficiaries);
                            }
                        }
                        document.getElementById('proposal-items').value = items.join('\n');
                        document.getElementById('citizen-proposal').value = proposalData.citizen_proposal ? '1' : '0';
                    }
                    break;
                    
                case 'approve':
                    title.innerHTML = '<i class="fas fa-check"></i> Modifier & Approuver';
                    action.value = 'approve_proposal';
                    saveBtn.textContent = 'Approuver';
                    saveBtn.style.display = 'inline-block';
                    rejectBtn.style.display = 'inline-block';
                    citizenFields.style.display = 'block';
                    rejectionReason.style.display = 'none';
                    
                    // Pré-remplir avec les données existantes
                    if (proposalData) {
                        document.getElementById('proposal-id').value = proposalData.id;
                        document.getElementById('proposal-title').value = proposalData.data?.titre || '';
                        document.getElementById('proposal-description').value = proposalData.data?.description || '';
                        document.getElementById('proposal-pillar').value = proposalData.data?.pillar || 'proteger';
                        
                        // Construire les points clés avec catégories et bénéficiaires
                        let items = proposalData.data?.items || [];
                        if (proposalData.data?.categories) {
                            items.unshift('Catégories: ' + proposalData.data.categories);
                        }
                        if (proposalData.data?.beneficiaries) {
                            items.unshift('Bénéficiaires: ' + proposalData.data.beneficiaries);
                        }
                        document.getElementById('proposal-items').value = items.join('\n');
                        document.getElementById('citizen-nom').value = proposalData.data?.nom || '';
                        document.getElementById('citizen-proposal').value = '1';
                    }
                    break;
                    
                case 'reject':
                    title.innerHTML = '<i class="fas fa-times"></i> Rejeter la proposition';
                    action.value = 'reject_proposal';
                    saveBtn.textContent = 'Confirmer le rejet';
                    saveBtn.style.display = 'inline-block';
                    rejectBtn.style.display = 'none';
                    citizenFields.style.display = 'none';
                    rejectionReason.style.display = 'block';
                    
                    if (proposalData) {
                        document.getElementById('proposal-id').value = proposalData.id;
                        document.getElementById('citizen-proposal').value = '1';
                    }
                    break;
            }
            
            AdminModal.open('proposalModal');
        }
        
        // Sauvegarder une proposition
        function saveProposal() {
            const form = document.getElementById('proposal-form');
            const formData = new FormData(form);
            const action = formData.get('action');
            
            // Validation selon l'action
            if (action === 'reject_proposal') {
                const rejectionReason = formData.get('rejection_reason');
                if (!rejectionReason || rejectionReason.trim() === '') {
                    AdminActions.showError('La raison du rejet est obligatoire');
                    return;
                }
            } else {
                const title = formData.get('title');
                const description = formData.get('description');
                const items = formData.get('items');
                
                if (!title || !description || !items) {
                    AdminActions.showError('Veuillez remplir tous les champs obligatoires');
                    return;
                }
            }
            
            // Fermer le modal et soumettre le formulaire
            AdminModal.close('proposalModal');
            
            // Ajouter un paramètre pour rediriger vers la section programme
            const submitFormData = new FormData(form);
            submitFormData.append('redirect_section', 'programme');
            
            // Créer un nouveau formulaire pour la soumission
            const submitForm = document.createElement('form');
            submitForm.method = 'POST';
            submitForm.style.display = 'none';
            
            // Ajouter tous les champs
            for (let [key, value] of submitFormData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                submitForm.appendChild(input);
            }
            
            document.body.appendChild(submitForm);
            submitForm.submit();
        }

        // Toggle de la zone rejetée
        function toggleRejectedZone() {
            const content = document.getElementById('rejected-content');
            const header = document.querySelector('.rejected-zone .zone-header');
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                header.classList.add('active');
            } else {
                content.style.display = 'none';
                header.classList.remove('active');
            }
        }
        
        // Fonctions pour les actions des propositions
        function editProposal(id) {
            // Charger les données et ouvrir le modal en mode édition        }
        
        function deleteProposal(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette proposition ?')) {
                // Créer un formulaire pour la suppression
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_proposal">
                    <input type="hidden" name="proposal_id" value="${id}">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function restoreProposal(id) {
            if (confirm('Êtes-vous sûr de vouloir restaurer cette proposition ?')) {
                // Créer un formulaire pour la restauration
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="restore_proposal">
                    <input type="hidden" name="proposal_id" value="${id}">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function rejectProposal(id) {
            // Trouver la proposition dans les données et ouvrir le modal en mode rejet
            // Pour l'instant, on ouvre juste avec l'ID
            openProposalModal('reject', { id: id });
        }
        
        // Fonction pour changer le statut d'une proposition citoyenne
        function setCitizenProposalStatus(proposalId, status) {const statusText = status === 'pending' ? 'en attente' : 'rejetée';
            if (confirm(`Êtes-vous sûr de vouloir mettre cette proposition ${statusText} ?`)) {const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="set_citizen_status">
                    <input type="hidden" name="proposal_id" value="${proposalId}">
                    <input type="hidden" name="status" value="${status}">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="redirect_section" value="programme">
                `;
                document.body.appendChild(form);form.submit();
            } else {}
        }
        
        // Charger automatiquement la section appropriée au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {            const menuItems = document.querySelectorAll('.menu-item a');menuItems.forEach((item, index) => {
                console.log(`  ${index}: ${item.textContent.trim()} - onclick: ${item.getAttribute('onclick')}`);
            });
            
            // Attendre un peu pour que tous les scripts soient chargés
            setTimeout(() => {
                const urlParams = new URLSearchParams(window.location.search);
                const section = urlParams.get('section') || 'dashboard';navigateToSection(section);
            }, 100);
        });
    </script>
    
    <style>
        /* Styles spécifiques à cette page */
        .admin-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 200px;
            color: var(--gray-600);
        }
        
        .admin-loading i {
            margin-right: 0.5rem;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-container">
        <?php include __DIR__ . '/../includes/admin_sidebar.php'; ?>
        
        <!-- Contenu principal -->
        <main class="admin-main">
            <div class="admin-header">
                <div class="header-left">
                    <h1>Administration du Site</h1>
                    <p>Gérez le contenu de votre site web</p>
                </div>
            </div>
            
            <!-- Workspace principal -->
            <div id="adminWorkspace" class="admin-workspace">
                <!-- Le contenu sera chargé automatiquement via JavaScript -->
            </div>
        </main>
    </div>
    
    <!-- Contenu des sections (caché) -->
    <!-- Sections cachées - seront déplacées dans l'espace de travail -->
    <div id="hiddenSections" style="display: none;">
        <?php foreach ($sections as $section): ?>
            <?= $section->renderContent() ?>
        <?php endforeach; ?>
    </div>
    
    <!-- Modals -->
    <div id="heroModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-home"></i> Modifier la section Hero</h3>
                <button onclick="AdminModal.close('heroModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Modal de modification Hero - À implémenter</p>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="AdminModal.close('heroModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="AdminActions.saveHero()" class="btn btn-primary">Sauvegarder</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Unifié Proposition -->
    <div id="proposalModal" class="modal-overlay" style="display: none;">
        <div class="modal-container modal-large">
            <div class="modal-header">
                <h3 id="proposalModalTitle"><i class="fas fa-plus"></i> Ajouter une proposition</h3>
                <button onclick="AdminModal.close('proposalModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="proposal-form" method="POST" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="add_proposal" id="proposal-action">
                    <input type="hidden" name="proposal_id" id="proposal-id" value="">
                    <input type="hidden" name="citizen_proposal" id="citizen-proposal" value="0">
                    <input type="hidden" name="current_section" value="programme">
                    
                    <!-- RECTO - Face visible de la carte -->
                    <div class="form-section">
                        <h4><i class="fas fa-eye"></i> Recto - Face visible de la carte</h4>
                        <div class="form-group">
                            <label for="proposal-title">Titre de la proposition *</label>
                            <input type="text" id="proposal-title" name="title" required placeholder="Ex: Préserver nos espaces naturels">
                        </div>
                        
                        <div class="form-group">
                            <label for="proposal-description">Description *</label>
                            <textarea id="proposal-description" name="description" rows="3" required placeholder="Décrivez votre proposition en quelques phrases..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="proposal-pillar">Pilier *</label>
                            <select id="proposal-pillar" name="pillar" required>
                                <option value="proteger">🛡️ Protéger</option>
                                <option value="tisser">🤝 Tisser</option>
                                <option value="dessiner">🎨 Dessiner</option>
                                <option value="ouvrir">🌍 Ouvrir</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- VERSO - Points clés -->
                    <div class="form-section">
                        <h4><i class="fas fa-list"></i> Verso - Points clés</h4>
                        <div class="form-group">
                            <label for="proposal-items">Points clés (un par ligne) *</label>
                            <textarea id="proposal-items" name="items" rows="4" required placeholder="Point clé 1&#10;Point clé 2&#10;Point clé 3"></textarea>
                            <small class="form-help">Séparez chaque point clé par une nouvelle ligne</small>
                        </div>
                    </div>

                    <!-- Champs spécifiques aux propositions citoyennes -->
                    <div id="citizen-fields" class="form-section" style="display: none;">
                        <h4><i class="fas fa-user"></i> Informations citoyen</h4>
                        <div class="form-group">
                            <label for="citizen-nom">Nom du citoyen</label>
                            <input type="text" id="citizen-nom" name="citizen_nom" readonly>
                        </div>
                    </div>
                    
                    <!-- Champ de raison pour les rejets -->
                    <div id="rejection-reason" class="form-section" style="display: none;">
                        <h4><i class="fas fa-exclamation-triangle"></i> Raison du rejet</h4>
                        <div class="form-group">
                            <label for="rejection-reason-text">Expliquez pourquoi cette proposition est rejetée *</label>
                            <textarea id="rejection-reason-text" name="rejection_reason" rows="3" placeholder="Cette proposition ne correspond pas à nos priorités car..."></textarea>
                            <small class="form-help">Cette raison sera envoyée par email au citoyen</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="AdminModal.close('proposalModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" id="proposal-save-btn" onclick="saveProposal()" class="btn btn-primary">Ajouter</button>
                <button type="button" id="proposal-reject-btn" onclick="rejectProposal()" class="btn btn-danger" style="display: none;">Rejeter</button>
            </div>
        </div>
    </div>

    <!-- Modal Ajouter Membre -->
    <div id="addMemberModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Ajouter un Membre</h3>
                <button onclick="AdminModal.close('addMemberModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-member-form" method="POST" action="" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="add_member">
                    <input type="hidden" name="redirect_section" value="equipe">
                    
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
                        <input type="file" id="member_photo" name="image" accept="image/*" data-crop="member">
                        <small class="form-help">Formats acceptés : JPG, PNG, GIF, WebP (max 5MB)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="AdminModal.close('addMemberModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="AdminActions.submitForm('add-member-form')" class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Modifier Membre -->
    <div id="editMemberModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Modifier le Membre</h3>
                <button onclick="AdminModal.close('editMemberModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-member-form" method="POST" action="" enctype="multipart/form-data" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="edit_member">
                    <input type="hidden" id="edit-member-id" name="id" value="">
                    <input type="hidden" name="redirect_section" value="equipe">
                    
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
                        <input type="file" id="edit-member-image" name="image" accept="image/*" data-ccrop="member" data-crop="member">
                        <small class="form-help">Formats acceptés : JPG, PNG, WebP (max 2MB)</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="AdminModal.close('editMemberModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="AdminActions.submitForm('edit-member-form')" class="btn btn-primary">Sauvegarder</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajouter Événement -->
    <div id="addEventModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-plus"></i> Ajouter un Événement</h3>
                <button onclick="AdminModal.close('addEventModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-event-form" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="add_event">
                    
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
                <button type="button" onclick="AdminModal.close('addEventModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="AdminActions.submitForm('add-event-form')" class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Modifier Événement -->
    <div id="editEventModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-edit"></i> Modifier l'Événement</h3>
                <button onclick="AdminModal.close('editEventModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-event-form" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="edit_event">
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
                <button type="button" onclick="AdminModal.close('editEventModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="AdminActions.submitForm('edit-event-form')" class="btn btn-primary">Sauvegarder</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Ajouter Principe -->
    <div id="addPrincipleModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-handshake"></i> Ajouter un Principe</h3>
                <button onclick="AdminModal.close('addPrincipleModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="add-principle-form" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="add_principle">
                    
                    <div class="form-group">
                        <label for="add-principle-title">Titre *</label>
                        <input type="text" id="add-principle-title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="add-principle-description">Description</label>
                        <textarea id="add-principle-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="add-principle-thematique">Thématique</label>
                        <input type="text" id="add-principle-thematique" name="thematique">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="AdminModal.close('addPrincipleModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="AdminActions.submitForm('add-principle-form')" class="btn btn-primary">Ajouter</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Modifier Principe -->
    <div id="editPrincipleModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-handshake"></i> Modifier le Principe</h3>
                <button onclick="AdminModal.close('editPrincipleModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-principle-form" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="edit_principle">
                    <input type="hidden" id="edit-principle-id" name="id" value="">
                    
                    <div class="form-group">
                        <label for="edit-principle-title">Titre *</label>
                        <input type="text" id="edit-principle-title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-principle-description">Description</label>
                        <textarea id="edit-principle-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-principle-thematique">Thématique</label>
                        <input type="text" id="edit-principle-thematique" name="thematique">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="AdminModal.close('editPrincipleModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="AdminActions.submitForm('edit-principle-form')" class="btn btn-primary">Sauvegarder</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Créer Utilisateur -->
    <div id="createUserModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-user-plus"></i> Créer un utilisateur</h3>
                <button onclick="AdminModal.close('createUserModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="create-user-form" method="POST" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="create_user">
                    
                    <div class="form-group">
                        <label for="username">Nom d'utilisateur *</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Mot de passe *</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Rôle *</label>
                        <select id="role" name="role" required>
                            <option value="editeur">Éditeur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="AdminModal.close('createUserModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="AdminActions.submitForm('create-user-form')" class="btn btn-primary">Créer</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Modifier Utilisateur -->
    <div id="editUserModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Modifier l'utilisateur</h3>
                <button onclick="AdminModal.close('editUserModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="edit-user-form" method="POST" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" id="edit-user-id" name="user_id" value="">
                    
                    <div class="form-group">
                        <label for="edit-username">Nom d'utilisateur *</label>
                        <input type="text" id="edit-username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-email">Email *</label>
                        <input type="email" id="edit-email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-role">Rôle *</label>
                        <select id="edit-role" name="role" required>
                            <option value="editeur">Éditeur</option>
                            <option value="admin">Administrateur</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="AdminModal.close('editUserModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="AdminActions.submitForm('edit-user-form')" class="btn btn-primary">Sauvegarder</button>
            </div>
        </div>
    </div>
    
    <!-- Modal Réinitialiser Mot de Passe -->
    <div id="resetPasswordModal" class="modal-overlay" style="display: none;">
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="fas fa-key"></i> Réinitialiser le mot de passe</h3>
                <button onclick="AdminModal.close('resetPasswordModal')" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="reset-password-form" method="POST" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" id="reset-user-id" name="user_id" value="">
                    
                    <div class="form-group">
                        <label for="new-password">Nouveau mot de passe *</label>
                        <input type="password" id="new-password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm-password">Confirmer le mot de passe *</label>
                        <input type="password" id="confirm-password" name="confirm_password" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="AdminModal.close('resetPasswordModal')" class="btn btn-secondary">Annuler</button>
                <button type="button" onclick="AdminActions.submitForm('reset-password-form')" class="btn btn-primary">Réinitialiser</button>
            </div>
        </div>
    </div>
    
    <!-- Bouton de sauvegarde flottant -->
    <button class="floating-save-btn" id="floating-save-btn" onclick="AdminActions.saveAllChanges()">
        <i class="fas fa-save"></i>
        Sauvegarder tout
    </button>
    
    <!-- Indicateur d'auto-sauvegarde -->
    <div class="auto-save-indicator" id="auto-save-indicator">
        <i class="fas fa-sync-alt"></i>
        <span>Auto-sauvegarde...</span>
    </div>
    
    <!-- Messages de feedback -->
    <?php if (isset($_SESSION['admin_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                AdminActions.showSuccess('<?= addslashes($_SESSION['admin_success']) ?>');
            });
        </script>
        <?php unset($_SESSION['admin_success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['admin_error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                AdminActions.showError('<?= addslashes($_SESSION['admin_error']) ?>');
            });
        </script>
        <?php unset($_SESSION['admin_error']); ?>
    <?php endif; ?>
    
    <!-- JavaScript modulaire -->
    <script src="../assets/js/admin-core.js"></script>
    <script src="../assets/js/admin-modals.js"></script>
    <script src="../assets/js/admin-actions.js"></script>
    <script src="../assets/js/admin-tabs.js"></script>
    
    <!-- Cropper.js CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script src="../assets/js/admin-image-cropper.js"></script>
    
</body>
</html>
