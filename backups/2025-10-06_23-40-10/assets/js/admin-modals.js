/**
 * AdminModal - Gestionnaire des modals
 */
class AdminModal {
    constructor() {
        this.openModals = new Set();
        this.init();
    }
    
    init() {
        this.bindEvents();
        console.log('🚀 AdminModal initialisé');
    }
    
    bindEvents() {
        // Fermeture par clic sur l'overlay
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.close(e.target.id);
            }
        });
        
        // Fermeture par échap
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeLast();
            }
        });

        // Empêcher la propagation d'actions internes des modals au système d'actions génériques
        document.addEventListener('click', (e) => {
            const inModal = e.target.closest && e.target.closest('.modal-container');
            if (inModal && e.target.matches('[data-action]')) {
                const action = e.target.getAttribute('data-action');
                // Laisser passer les actions de recadrage pour le module Cropper
                if (action !== 'confirm-crop' && action !== 'cancel-crop') {
                    e.stopPropagation();
                }
            }
        }, true);
    }
    
    open(modalId, data = null) {
        console.log('🔄 Ouverture du modal:', modalId, data);
        
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error('❌ Modal introuvable:', modalId);
            return;
        }
        
        // Préparer les données si nécessaire
        if (data) {
            this.prepareModalData(modal, data);
        }
        
        // Afficher le modal avec animation
        modal.style.display = 'flex';
        modal.style.opacity = '0';
        modal.style.transition = 'opacity 0.3s ease';
        
        // Forcer le reflow
        modal.offsetHeight;
        
        // Animation d'apparition
        modal.style.opacity = '1';
        
        // Ajouter à la liste des modals ouverts
        this.openModals.add(modalId);
        
        // Focus sur le premier élément interactif
        setTimeout(() => {
            const firstInput = modal.querySelector('input, textarea, select, button');
            if (firstInput) {
                firstInput.focus();
            }
        }, 100);
        
        console.log('✅ Modal ouvert:', modalId);
    }
    
    close(modalId) {
        console.log('🔄 Fermeture du modal:', modalId);
        
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error('❌ Modal introuvable:', modalId);
            return;
        }
        
        // Animation de disparition
        modal.style.opacity = '0';
        
        setTimeout(() => {
            modal.style.display = 'none';
            modal.style.transition = '';
        }, 300);
        
        // Retirer de la liste des modals ouverts
        this.openModals.delete(modalId);
        
        console.log('✅ Modal fermé:', modalId);
    }
    
    closeLast() {
        if (this.openModals.size > 0) {
            const lastModal = Array.from(this.openModals).pop();
            this.close(lastModal);
        }
    }
    
    closeAll() {
        console.log('🔄 Fermeture de tous les modals');
        
        this.openModals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                modal.style.opacity = '1';
                modal.style.transition = '';
            }
        });
        
        this.openModals.clear();
        
        console.log('✅ Tous les modals fermés');
    }
    
    prepareModalData(modal, data) {
        // Préparer les données spécifiques selon le type de modal
        if (data && typeof data === 'object') {
            const inputs = modal.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                const fieldName = input.name;
                const key = fieldName || input.id;
                if (key && data[key] !== undefined) {
                    input.value = data[key];
                }
            });
        }
        
        // Déclencher les événements de préparation spécifiques
        const event = new CustomEvent('modal-prepare', {
            detail: { modal, data }
        });
        modal.dispatchEvent(event);
    }
    
    // Méthodes statiques pour compatibilité
    static open(modalId, data = null) {
        if (!window.adminModal) {
            window.adminModal = new AdminModal();
        }
        window.adminModal.open(modalId, data);
    }
    
    static close(modalId) {
        if (!window.adminModal) {
            window.adminModal = new AdminModal();
        }
        window.adminModal.close(modalId);
    }
    
    static closeAll() {
        if (!window.adminModal) {
            window.adminModal = new AdminModal();
        }
        window.adminModal.closeAll();
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    window.adminModal = new AdminModal();
});
