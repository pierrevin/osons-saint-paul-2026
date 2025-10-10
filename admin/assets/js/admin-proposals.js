/**
 * Gestion des propositions - Modals et actions
 */

// Fonction globale pour ouvrir un modal de proposition
function openProposalModal(action, proposalData = null) {
    console.log('openProposalModal appelé:', action, proposalData);
    
    // Utiliser le modal unifié
    const modal = document.getElementById('proposalModal');
    if (!modal) {
        console.error('Modal proposalModal introuvable');
        return;
    }
    
    switch(action) {
        case 'create':
            prepareModalForCreate();
            AdminModal.open('proposalModal');
            break;
            
        case 'edit':
            if (proposalData) {
                prepareModalForEdit(proposalData);
                AdminModal.open('proposalModal');
            }
            break;
            
        case 'approve':
            if (proposalData) {
                prepareModalForApprove(proposalData);
                AdminModal.open('proposalModal');
            }
            break;
    }
}

// Fonction globale pour rejeter une proposition
function rejectProposal(proposalId) {
    const reason = prompt('Raison du rejet :');
    if (!reason || reason.trim() === '') {
        alert('La raison du rejet est obligatoire.');
        return;
    }
    
    if (confirm('Êtes-vous sûr de vouloir rejeter cette proposition ?')) {
        submitProposalAction('reject_proposal', {
            proposal_id: proposalId,
            rejection_reason: reason
        });
    }
}

// Fonction globale pour restaurer une proposition
function restoreProposal(proposalId) {
    if (confirm('Restaurer cette proposition en attente de validation ?')) {
        submitProposalAction('restore_proposal', {
            proposal_id: proposalId
        });
    }
}

// Fonction globale pour supprimer une proposition
function deleteProposal(proposalId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer définitivement cette proposition ?')) {
        submitProposalAction('delete_proposal', {
            proposal_id: proposalId
        });
    }
}

// Fonction globale pour changer le statut d'une proposition citoyenne
function setCitizenProposalStatus(proposalId, status) {
    let message = '';
    let reason = '';
    
    switch(status) {
        case 'pending':
            message = 'Remettre cette proposition en attente ?';
            break;
        case 'approved':
            message = 'Approuver cette proposition ?';
            break;
        case 'rejected':
            reason = prompt('Raison du rejet :');
            if (!reason || reason.trim() === '') {
                alert('La raison du rejet est obligatoire.');
                return;
            }
            message = 'Rejeter cette proposition ?';
            break;
    }
    
    if (confirm(message)) {
        const data = {
            proposal_id: proposalId,
            status: status
        };
        
        if (reason) {
            data.rejection_reason = reason;
        }
        
        submitProposalAction('set_citizen_proposal_status', data);
    }
}

// Préparer le modal pour la création
function prepareModalForCreate() {
    const modal = document.getElementById('proposalModal');
    if (!modal) return;
    
    // Titre du modal
    const modalTitle = modal.querySelector('#proposalModalTitle');
    if (modalTitle) modalTitle.innerHTML = '<i class="fas fa-plus"></i> Ajouter une proposition';
    
    // Réinitialiser le formulaire
    const form = modal.querySelector('#proposal-form');
    if (form) form.reset();
    
    // Action du formulaire
    const actionInput = modal.querySelector('#proposal-action');
    if (actionInput) actionInput.value = 'add_proposal';
    
    // ID vide pour création
    const idInput = modal.querySelector('#proposal-id');
    if (idInput) idInput.value = '';
    
    // Pas de proposition citoyenne
    const citizenInput = modal.querySelector('#citizen-proposal');
    if (citizenInput) citizenInput.value = '0';
    
    // Décocher le badge citoyen
    const citizenBadgeCheckbox = modal.querySelector('#display-citizen-badge');
    if (citizenBadgeCheckbox) citizenBadgeCheckbox.checked = false;
    
    // Masquer les champs citoyens
    const citizenFields = modal.querySelector('#citizen-fields');
    if (citizenFields) citizenFields.style.display = 'none';
    
    // Masquer la raison de rejet
    const rejectionReason = modal.querySelector('#rejection-reason');
    if (rejectionReason) rejectionReason.style.display = 'none';
    
    // Boutons
    const saveBtn = modal.querySelector('#proposal-save-btn');
    if (saveBtn) {
        saveBtn.textContent = 'Ajouter';
        saveBtn.className = 'btn btn-primary';
        saveBtn.style.display = 'inline-block';
    }
    
    const rejectBtn = modal.querySelector('#proposal-reject-btn');
    if (rejectBtn) rejectBtn.style.display = 'none';
}

// Préparer le modal pour l'édition
function prepareModalForEdit(proposalData) {
    const modal = document.getElementById('proposalModal');
    if (!modal) return;
    
    // Titre du modal
    const modalTitle = modal.querySelector('#proposalModalTitle');
    if (modalTitle) modalTitle.innerHTML = '<i class="fas fa-edit"></i> Modifier la proposition';
    
    // Action du formulaire
    const actionInput = modal.querySelector('#proposal-action');
    if (actionInput) actionInput.value = 'edit_proposal';
    
    // ID de la proposition
    const idInput = modal.querySelector('#proposal-id');
    if (idInput) idInput.value = proposalData.id || '';
    
    // Proposition citoyenne ?
    const isCitizen = proposalData.citizen_proposal || (proposalData.data && proposalData.data.titre);
    const citizenInput = modal.querySelector('#citizen-proposal');
    if (citizenInput) citizenInput.value = isCitizen ? '1' : '0';
    
    // Titre
    const titleInput = modal.querySelector('#proposal-title');
    if (titleInput) titleInput.value = proposalData.title || proposalData.data?.titre || '';
    
    // Description
    const descInput = modal.querySelector('#proposal-description');
    if (descInput) descInput.value = proposalData.description || proposalData.data?.description || '';
    
    // Pilier
    const pillarSelect = modal.querySelector('#proposal-pillar');
    if (pillarSelect) pillarSelect.value = proposalData.pillar || 'tisser';
    
    // Items
    const itemsTextarea = modal.querySelector('#proposal-items');
    if (itemsTextarea) {
        const items = proposalData.items || proposalData.data?.items || [];
        itemsTextarea.value = items.join('\n');
    }
    
    // Badge citoyen
    const citizenBadgeCheckbox = modal.querySelector('#display-citizen-badge');
    if (citizenBadgeCheckbox) {
        citizenBadgeCheckbox.checked = proposalData.display_citizen_badge == '1' || 
                                       proposalData.citizen_proposal === true;
    }
    
    // Masquer les champs citoyens
    const citizenFields = modal.querySelector('#citizen-fields');
    if (citizenFields) citizenFields.style.display = 'none';
    
    // Masquer la raison de rejet
    const rejectionReason = modal.querySelector('#rejection-reason');
    if (rejectionReason) rejectionReason.style.display = 'none';
    
    // Boutons
    const saveBtn = modal.querySelector('#proposal-save-btn');
    if (saveBtn) {
        saveBtn.textContent = 'Enregistrer';
        saveBtn.className = 'btn btn-primary';
        saveBtn.style.display = 'inline-block';
    }
    
    const rejectBtn = modal.querySelector('#proposal-reject-btn');
    if (rejectBtn) rejectBtn.style.display = 'none';
}

// Préparer le modal pour l'approbation
function prepareModalForApprove(proposalData) {
    const modal = document.getElementById('proposalModal');
    if (!modal) return;
    
    // Titre du modal
    const modalTitle = modal.querySelector('#proposalModalTitle');
    if (modalTitle) modalTitle.innerHTML = '<i class="fas fa-check"></i> Modifier & Approuver';
    
    // Action du formulaire
    const actionInput = modal.querySelector('#proposal-action');
    if (actionInput) actionInput.value = 'approve_proposal';
    
    // ID de la proposition
    const idInput = modal.querySelector('#proposal-id');
    if (idInput) idInput.value = proposalData.id || '';
    
    // C'est une proposition citoyenne
    const citizenInput = modal.querySelector('#citizen-proposal');
    if (citizenInput) citizenInput.value = '1';
    
    // Titre
    const titleInput = modal.querySelector('#proposal-title');
    if (titleInput) titleInput.value = proposalData.data?.titre || proposalData.title || '';
    
    // Description
    const descInput = modal.querySelector('#proposal-description');
    if (descInput) descInput.value = proposalData.data?.description || proposalData.description || '';
    
    // Pilier - mapper depuis la catégorie
    const pillarSelect = modal.querySelector('#proposal-pillar');
    if (pillarSelect) {
        const category = proposalData.data?.categorie || proposalData.pillar || '';
        const pillarMapping = {
            'Urbanisme & Logement': 'dessiner',
            'Environnement & Nature': 'proteger',
            'Mobilité & Transport': 'dessiner',
            'Vie sociale & Solidarité': 'tisser',
            'Éducation & Jeunesse': 'ouvrir',
            'Santé & Bien-être': 'proteger',
            'Culture & Sport': 'tisser',
            'Économie & Commerce': 'ouvrir',
            'Services publics': 'proteger',
            'Autre': 'tisser'
        };
        const pillar = pillarMapping[category] || 'tisser';
        pillarSelect.value = pillar;
    }
    
    // Items
    const itemsTextarea = modal.querySelector('#proposal-items');
    if (itemsTextarea) {
        const items = proposalData.data?.items || proposalData.items || [];
        const categories = proposalData.data?.categories || '';
        const beneficiaries = proposalData.data?.beneficiaries || '';
        
        let itemsText = items.join('\n');
        if (categories) itemsText += '\nCatégories: ' + categories;
        if (beneficiaries) itemsText += '\nBénéficiaires: ' + beneficiaries;
        
        itemsTextarea.value = itemsText;
    }
    
    // Badge citoyen automatiquement coché pour les approbations
    const citizenBadgeCheckbox = modal.querySelector('#display-citizen-badge');
    if (citizenBadgeCheckbox) {
        citizenBadgeCheckbox.checked = true;
    }
    
    // Afficher les champs citoyens
    const citizenFields = modal.querySelector('#citizen-fields');
    if (citizenFields) {
        citizenFields.style.display = 'block';
        const nomInput = modal.querySelector('#citizen-nom');
        if (nomInput) nomInput.value = proposalData.data?.nom || 'Anonyme';
    }
    
    // Masquer la raison de rejet
    const rejectionReason = modal.querySelector('#rejection-reason');
    if (rejectionReason) rejectionReason.style.display = 'none';
    
    // Boutons
    const saveBtn = modal.querySelector('#proposal-save-btn');
    if (saveBtn) {
        saveBtn.textContent = 'Approuver';
        saveBtn.className = 'btn btn-success';
        saveBtn.style.display = 'inline-block';
    }
    
    const rejectBtn = modal.querySelector('#proposal-reject-btn');
    if (rejectBtn) rejectBtn.style.display = 'none';
}

// Ces fonctions ne sont plus nécessaires car le modal unifié utilise un textarea pour les items

// Soumettre une action de proposition
async function submitProposalAction(action, data) {
    try {
        const formData = new FormData();
        formData.append('action', action);
        
        for (const key in data) {
            formData.append(key, data[key]);
        }
        
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload(); // Recharger silencieusement pour afficher les changements
        } else {
            throw new Error(result.message || 'Erreur lors de l\'action');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur: ' + error.message);
    }
}

// Fonction appelée par le bouton du modal unifié
function saveProposal() {
    const form = document.querySelector('#proposal-form');
    if (!form) {
        console.error('Formulaire proposal-form introuvable');
        return;
    }
    
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const action = data.action || 'add_proposal';
    
    // Soumettre l'action
    submitProposalAction(action, data);
}

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', () => {
    console.log('admin-proposals.js chargé');
});

