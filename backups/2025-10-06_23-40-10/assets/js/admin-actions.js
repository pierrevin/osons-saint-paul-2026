/**
 * AdminActions - Gestionnaire des actions (CRUD, formulaires, etc.)
 */
class AdminActions {
    constructor() {
        this.forms = new Map();
        this.init();
    }
    
    init() {
        this.bindEvents();
        console.log('🚀 AdminActions initialisé');
    }
    
    bindEvents() {
        // Gestion de la soumission des formulaires
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('admin-form')) {
                e.preventDefault();
                this.handleFormSubmit(e.target);
            }
        });
        
        // Gestion des changements de formulaire
        document.addEventListener('change', (e) => {
            if (e.target.closest('.admin-form')) {
                this.markFormChanged(e.target.closest('.admin-form'));
            }
        });
        
        // Gestion des boutons d'action
        document.addEventListener('click', (e) => {
            if (e.target.closest('[data-action]')) {
                const action = e.target.closest('[data-action]').dataset.action;
                this.handleAction(action, e.target.closest('[data-action]'));
            }
        });
    }
    
    markFormChanged(form) {
        if (!form) return;
        
        const formId = form.id || 'unknown';
        this.forms.set(formId, { changed: true, timestamp: Date.now() });
        
        // Marquer qu'il y a des changements non sauvegardés
        this.hasUnsavedChanges = true;
        this.showFloatingSaveBtn();
        
        // Ajouter un indicateur visuel
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.classList.add('btn-changed');
            submitBtn.textContent = '💾 Sauvegarder les modifications';
        }
        
        console.log('📝 Formulaire modifié:', formId);
    }
    
    // Variables pour la sauvegarde flottante
    hasUnsavedChanges = false;
    autoSaveInterval = null;
    
    /**
     * Afficher le bouton de sauvegarde flottant
     */
    showFloatingSaveBtn() {
        const floatingBtn = document.getElementById('floating-save-btn');
        if (floatingBtn && this.hasUnsavedChanges) {
            floatingBtn.classList.add('visible');
            floatingBtn.innerHTML = '<i class="fas fa-save"></i> Sauvegarder tout';
            floatingBtn.classList.remove('saving', 'success', 'error');
        }
    }
    
    /**
     * Masquer le bouton de sauvegarde flottant
     */
    hideFloatingSaveBtn() {
        const floatingBtn = document.getElementById('floating-save-btn');
        if (floatingBtn) {
            floatingBtn.classList.remove('visible', 'saving', 'success', 'error');
            floatingBtn.innerHTML = '<i class="fas fa-save"></i> Sauvegarder tout';
        }
    }
    
    /**
     * Sauvegarder tous les changements
     */
    async saveAllChanges() {
        const floatingBtn = document.getElementById('floating-save-btn');
        if (!this.hasUnsavedChanges) {
            this.showToast('Aucune modification à sauvegarder', 'warning');
            return;
        }
        if (floatingBtn) {
            floatingBtn.classList.add('saving');
            floatingBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sauvegarde...';
        }
        try {
            const forms = Array.from(document.querySelectorAll('form.admin-form'));
            for (const form of forms) {
                const action = form.querySelector('input[name="action"]')?.value;
                if (!action) continue;
                await this.sendRequest(action, Object.fromEntries(new FormData(form).entries()), form);
            }
            this.hasUnsavedChanges = false;
            this.hideFloatingSaveBtn();
            this.showToast('Toutes les modifications ont été sauvegardées', 'success');
            if (floatingBtn) {
                floatingBtn.classList.remove('saving');
                floatingBtn.classList.add('success');
                setTimeout(() => { floatingBtn.classList.remove('success'); }, 2000);
            }
        } catch (e) {
            console.error(e);
            this.showToast(e.message || 'Erreur lors de la sauvegarde', 'error');
            if (floatingBtn) {
                floatingBtn.classList.remove('saving');
                floatingBtn.classList.add('error');
                setTimeout(() => { floatingBtn.classList.remove('error'); }, 2000);
            }
        }
    }
    
    /**
     * Collecter toutes les données des formulaires
     */
    collectAllFormsData() {
        const formsData = {};
        const forms = document.querySelectorAll('form[id$="-form"], form[id$="-form-element"]');
        
        forms.forEach(form => {
            const formId = form.id;
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            formsData[formId] = data;
        });
        
        return formsData;
    }
    
    async handleFormSubmit(form) {
        const formId = form.id || 'unknown';
        const action = form.querySelector('input[name="action"]')?.value;
        
        console.log('🔄 Soumission du formulaire:', formId, 'Action:', action);
        
        try {
            // Désactiver le bouton de soumission
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = '⏳ Sauvegarde...';
            }
            
            // Préparer les données
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // Envoyer la requête
            // Forcer la redirection vers la section courante si non précisé
            if (!form.querySelector('input[name="redirect_section"]')) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'redirect_section';
                hidden.value = (form.id || '').toString().replace('-form-element', '') || 'dashboard';
                form.appendChild(hidden);
            }
            const response = await this.sendRequest(action, data, form);
            
            if (response.success) {
                this.showSuccess(response.message || 'Données sauvegardées avec succès');
                this.forms.delete(formId);
                
                // Réinitialiser le bouton
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-changed');
                    submitBtn.textContent = 'Sauvegarder';
                }
                
                // Déclencher un événement de succès
                form.dispatchEvent(new CustomEvent('form-success', { detail: response }));
                // Fermer le dernier modal ouvert si existant
                if (window.adminModal && window.adminModal.closeLast) {
                    window.adminModal.closeLast();
                }
                // Forcer un rechargement pour refléter les données serveur
                const redirect = form.querySelector('input[name="redirect_section"]')?.value || 'dashboard';
                const baseUrl = window.location.pathname;
                window.location.href = `${baseUrl}?section=${encodeURIComponent(redirect)}`;
                
            } else {
                throw new Error(response.message || 'Erreur lors de la sauvegarde');
            }
            
        } catch (error) {
            console.error('❌ Erreur de soumission:', error);
            this.showError(error.message);
            
            // Réactiver le bouton
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = '🔄 Réessayer';
            }
        }
    }
    
    async sendRequest(action, data, form) {
        // Vrai appel AJAX
        const fd = new FormData(form);
        fd.set('ajax', '1');
        if (!fd.get('redirect_section')) fd.set('redirect_section', 'equipe');
        // Attention: input name="action" peut masquer form.action (HTMLInputElement) → utiliser l'attribut
        const formActionAttr = form && typeof form.getAttribute === 'function' ? form.getAttribute('action') : '';
        const url = formActionAttr && formActionAttr.trim() ? formActionAttr : window.location.href.split('#')[0];
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        });
        if (!response.ok) {
            const text = await response.text().catch(() => '');
            throw new Error(`Erreur HTTP ${response.status} sur ${url} ${text ? '- ' + text.substring(0,200) : ''}`);
        }
        return await response.json().catch(() => ({ success: true, message: 'OK (fallback)' }));
    }
    
    handleAction(action, element) {
        console.log('🔄 Action:', action, element);
        
        switch (action) {
            case 'delete-member':
                this.deleteMember(element.dataset.id);
                break;
            case 'delete-principle':
                this.deleteChartePrinciple(element.dataset.id);
                break;
            case 'delete-event':
                this.deleteEvent(element.dataset.id);
                break;
            case 'delete-proposal':
                this.deleteProposal(element.dataset.id);
                break;
            case 'edit-proposal':
                this.editProposal(element.dataset.id);
                break;
            case 'add-proposal':
                this.addProposal();
                break;
            default:
                console.warn('⚠️ Action inconnue:', action);
        }
    }
    
    deleteMember(id) {
        if (!id) return;
        if (!confirm('Confirmer la suppression de ce membre ?')) return;
        const url = window.location.href; // même endpoint
        const fd = new FormData();
        fd.append('action', 'delete_member');
        fd.append('id', id);
        fd.append('redirect_section', 'equipe');
        fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
            .then(r => r.json())
            .then(json => {
                if (!json.success) throw new Error(json.error || 'Échec suppression');
                const card = document.querySelector(`.member-card[data-id="${CSS.escape(id)}"]`);
                if (card) card.remove();
                this.showSuccess('Membre supprimé');
            })
            .catch(err => this.showError(err.message));
    }

    deleteProposal(id) {
        console.log('🗑️ Suppression de la proposition:', id);
        
        // Confirmer la suppression
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette proposition ?')) {
            return;
        }
        
        // Ici, vous feriez un appel AJAX pour supprimer
        // Pour l'instant, on simule
        setTimeout(() => {
            const card = document.querySelector(`[data-id="${id}"]`);
            if (card) {
                card.remove();
                this.showSuccess('Proposition supprimée avec succès');
            }
        }, 500);
    }
    
    editProposal(id) {
        console.log('✏️ Modification de la proposition:', id);
        
        // Charger les données de la proposition
        // et ouvrir le modal d'édition
        AdminModal.open('editProposalModal', { id: id });
    }
    
    addProposal() {
        console.log('➕ Ajout d\'une nouvelle proposition');
        AdminModal.open('addProposalModal');
    }

    // ===== CHARTE =====
    async addChartePrinciple() {
        const title = prompt('Titre du principe');
        if (!title) return;
        const fd = new FormData();
        fd.append('action', 'add_principle');
        fd.append('title', title);
        const url = window.location.href.split('#')[0];
        const res = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        const json = await res.json();
        if (json.success) { this.showSuccess(json.message); window.location.href = window.location.pathname + '?section=charte'; }
        else { this.showError(json.message || 'Erreur'); }
    }

    async editChartePrincipleFromCard(cardEl) {
        if (!cardEl) return;
        const id = cardEl.dataset.id;
        const newTitle = prompt('Nouveau titre', cardEl.dataset.title || '');
        if (!newTitle) return;
        const newDesc = prompt('Description', cardEl.dataset.description || '');
        const newThem = prompt('Thématique', cardEl.dataset.thematique || '');
        const fd = new FormData();
        fd.append('action', 'edit_principle');
        fd.append('id', id);
        fd.append('title', newTitle);
        fd.append('description', newDesc);
        fd.append('thematique', newThem);
        const url = window.location.href.split('#')[0];
        const res = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        const json = await res.json();
        if (json.success) { this.showSuccess(json.message); window.location.href = window.location.pathname + '?section=charte'; }
        else { this.showError(json.message || 'Erreur'); }
    }

    static editChartePrinciple(el) {
        if (!window.adminActions) window.adminActions = new AdminActions();
        const card = (typeof el === 'string') ? document.querySelector(`.principle-card[data-id="${CSS.escape(el)}"]`) : el;
        window.adminActions.editChartePrincipleFromCard(card);
    }

    async deleteChartePrinciple(id) {
        if (!id) return;
        if (!confirm('Supprimer ce principe ?')) return;
        const fd = new FormData();
        fd.append('action', 'delete_principle');
        fd.append('id', id);
        const url = window.location.href.split('#')[0];
        const res = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        const json = await res.json();
        if (json.success) { this.showSuccess(json.message); window.location.href = window.location.pathname + '?section=charte'; }
        else { this.showError(json.message || 'Erreur'); }
    }

    // ===== RENDEZ-VOUS =====
    openEditEvent(idOrEl) {
        let card;
        if (typeof idOrEl === 'string') {
            card = document.querySelector(`.event-card[data-id="${CSS.escape(idOrEl)}"]`);
        } else if (idOrEl && idOrEl.closest) {
            card = idOrEl.closest('.event-card');
        }
        if (!card) return;
        // Remplir le modal d'édition
        const titleEl = document.getElementById('edit-event-title');
        const descEl = document.getElementById('edit-event-description');
        const dateEl = document.getElementById('edit-event-date');
        const locEl = document.getElementById('edit-event-location');
        const idEl = document.getElementById('edit-event-id');
        if (titleEl) titleEl.value = card.dataset.title || '';
        if (descEl) descEl.value = card.dataset.description || '';
        if (dateEl) dateEl.value = card.dataset.date || '';
        if (locEl) locEl.value = card.dataset.location || '';
        if (idEl) idEl.value = card.dataset.id;
        // Ouvrir le modal
        if (window.AdminModal && window.AdminModal.open) {
            AdminModal.open('editEventModal');
        } else {
            document.getElementById('editEventModal')?.style && (document.getElementById('editEventModal').style.display = 'block');
        }
    }

    async deleteEvent(id) {
        if (!id) return;
        if (!confirm('Supprimer cet événement ?')) return;
        const fd = new FormData();
        fd.append('action', 'delete_event');
        fd.append('id', id);
        const url = window.location.href.split('#')[0];
        const res = await fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: fd });
        const json = await res.json();
        if (json.success) { this.showSuccess(json.message); window.location.href = window.location.pathname + '?section=rendez_vous'; }
        else { this.showError(json.message || 'Erreur'); }
    }
    
    showSuccess(message) {
        this.showToast(message, 'success');
    }
    
    showError(message) {
        this.showToast(message, 'error');
    }
    
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span class="toast-message">${message}</span>
                <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Animation d'apparition
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Suppression automatique
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
    
    // Méthodes statiques pour compatibilité
    static deleteProposal(id) {
        if (!window.adminActions) {
            window.adminActions = new AdminActions();
        }
        window.adminActions.deleteProposal(id);
    }
    
    static editProposal(id) {
        if (!window.adminActions) {
            window.adminActions = new AdminActions();
        }
        window.adminActions.editProposal(id);
    }
    
    static addProposal() {
        if (!window.adminActions) {
            window.adminActions = new AdminActions();
        }
        window.adminActions.addProposal();
    }
    
    static saveAllChanges() {
        if (!window.adminActions) {
            window.adminActions = new AdminActions();
        }
        window.adminActions.saveAllChanges();
    }
    
    static showSuccess(message) {
        if (!window.adminActions) {
            window.adminActions = new AdminActions();
        }
        window.adminActions.showSuccess(message);
    }
    
    static showError(message) {
        if (!window.adminActions) {
            window.adminActions = new AdminActions();
        }
        window.adminActions.showError(message);
    }
    
    static submitForm(formId) {
        if (!window.adminActions) {
            window.adminActions = new AdminActions();
        }
        const form = document.getElementById(formId);
        if (form) {
            window.adminActions.handleFormSubmit(form);
        }
    }
}

// Alias global pour compatibilité
window.AdminActions = AdminActions;

// Compat: fonction globale utilisée par l'ancien inline onchange
window.markFormChanged = function() {
    try {
        const el = document.activeElement;
        const form = el && el.closest && el.closest('form');
        if (form) {
            if (!window.adminActions) {
                window.adminActions = new AdminActions();
            }
            window.adminActions.markFormChanged(form);
        }
    } catch (e) {
        console.warn('markFormChanged non appliqué:', e);
    }
};

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    window.adminActions = new AdminActions();
});
