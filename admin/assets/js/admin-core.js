/**
 * AdminCore - Système de navigation et gestion d'état principal
 */
class AdminCore {
    constructor() {
        this.currentSection = null;
        this.sections = new Map();
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initMobileMenu();
        this.restoreLastSection();
        // Test de débogage
        setTimeout(() => {
            const firstMenuItem = document.querySelector('.menu-item a');
            if (firstMenuItem) {
            } else {
            }
        }, 1000);
    }
    
    initMobileMenu() {
        // Créer le bouton hamburger mobile
        const mobileToggle = document.createElement('button');
        mobileToggle.className = 'mobile-menu-toggle';
        mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
        mobileToggle.setAttribute('aria-label', 'Ouvrir le menu');
        document.body.appendChild(mobileToggle);

        // Gestion du clic sur le bouton hamburger
        mobileToggle.addEventListener('click', () => {
            const sidebar = document.querySelector('.admin-sidebar');
            const isOpen = sidebar.classList.contains('mobile-open');
            
            if (isOpen) {
                sidebar.classList.remove('mobile-open');
                mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
                mobileToggle.setAttribute('aria-label', 'Ouvrir le menu');
            } else {
                sidebar.classList.add('mobile-open');
                mobileToggle.innerHTML = '<i class="fas fa-times"></i>';
                mobileToggle.setAttribute('aria-label', 'Fermer le menu');
            }
        });

        // Fermer le menu en cliquant sur un lien
        document.addEventListener('click', (e) => {
            if (e.target.closest('.admin-sidebar a')) {
                const sidebar = document.querySelector('.admin-sidebar');
                const mobileToggle = document.querySelector('.mobile-menu-toggle');
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-open');
                    mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    mobileToggle.setAttribute('aria-label', 'Ouvrir le menu');
                }
            }
        });

        // Fermer le menu en cliquant à l'extérieur
        document.addEventListener('click', (e) => {
            const sidebar = document.querySelector('.admin-sidebar');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            
            if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('mobile-open');
                    mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
                    mobileToggle.setAttribute('aria-label', 'Ouvrir le menu');
                }
            }
        });

        // Gestion du redimensionnement de la fenêtre
        window.addEventListener('resize', () => {
            const sidebar = document.querySelector('.admin-sidebar');
            const mobileToggle = document.querySelector('.mobile-menu-toggle');
            
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
                mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
                mobileToggle.setAttribute('aria-label', 'Ouvrir le menu');
            }
        });
    }

    bindEvents() {
        // Gestion du hash dans l'URL
        window.addEventListener('hashchange', () => this.handleHashChange());
        
        // Gestion des clics sur les liens du menu (seulement ceux avec onclick)
        document.addEventListener('click', (e) => {
            if (e.target.closest('.menu-item a')) {
                const link = e.target.closest('a');
                const sectionId = this.extractSectionFromLink(link);
                
                // Seulement intercepter les liens avec des fonctions de navigation
                if (sectionId) {
                    e.preventDefault();
                    this.navigateTo(sectionId);
                }
                // Les autres liens (sans onclick) fonctionnent normalement
            }
        });
    }
    
    extractSectionFromLink(link) {
        const onclick = link.getAttribute('onclick');
        if (onclick) {
            // Chercher navigateToSection ou AdminRouter.navigateTo
            const match = onclick.match(/(?:navigateToSection|AdminRouter\.navigateTo)\('([^']+)'\)/);
            return match ? match[1] : null;
        }
        return null;
    }
    
    handleHashChange() {
        const hash = window.location.hash.replace('#', '');
        if (hash && hash !== this.currentSection) {
            this.navigateTo(hash);
        }
    }
    
    navigateTo(sectionId) {
        if (this.currentSection === sectionId) {
            return;
        }try {
            // Récupérer le contenu de la section
            const contentEl = document.getElementById(`${sectionId}-content`);
            if (!contentEl) {return;
            }
            
            // Mettre à jour le workspace
            const workspace = document.getElementById('adminWorkspace');
            if (!workspace) {return;
            }
            
            // Injecter le contenu
            workspace.innerHTML = contentEl.innerHTML;
            
            // Marquer comme visible
            workspace.style.display = 'block';
            
            // Focus sur le premier élément interactif
            const firstInput = workspace.querySelector('input, textarea, select, button');
            if (firstInput) {
                firstInput.focus();
            }
            
            // Mettre à jour l'URL
            history.replaceState(null, '', `#${sectionId}`);
            
            // Sauvegarder la section actuelle
            this.currentSection = sectionId;
            try {
                localStorage.setItem('adminLastSection', sectionId);
            } catch (e) {}
            
            // Mettre à jour l'état du menu
            this.updateMenuState(sectionId);
            
            // Scroll vers le workspace
            workspace.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Émettre un événement pour signaler que la section est chargée
            const evt = new CustomEvent('section-loaded', { detail: { sectionId } });
            document.dispatchEvent(evt);
        } catch (error) {}
    }
    
    updateMenuState(activeSectionId) {
        // Retirer la classe active de tous les éléments du menu
        document.querySelectorAll('.menu-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Ajouter la classe active à l'élément correspondant
        const activeLink = document.querySelector(`[onclick*="'${activeSectionId}'"]`);
        if (activeLink) {
            activeLink.closest('.menu-item').classList.add('active');
        }
    }
    
    restoreLastSection() {
        try {
            const lastSection = localStorage.getItem('adminLastSection');
            if (lastSection) {
                // Délai pour s'assurer que le DOM est prêt
                setTimeout(() => {
                    this.navigateTo(lastSection);
                }, 50);
            }
        } catch (e) {}
    }
    
    // Méthode publique pour navigation depuis l'extérieur
    static navigateTo(sectionId) {
        if (!window.adminCore) {
            window.adminCore = new AdminCore();
        }
        window.adminCore.navigateTo(sectionId);
    }
}

// Alias global pour compatibilité
window.AdminRouter = {
    navigateTo: AdminCore.navigateTo
};

// Fonction globale pour compatibilité avec l'ancien système
window.navigateToSection = function(sectionId) {if (window.adminCore) {
        window.adminCore.navigateTo(sectionId);
    } else {setTimeout(() => {
            if (window.adminCore) {
                window.adminCore.navigateTo(sectionId);
            } else {}
        }, 100);
    }
};

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    window.adminCore = new AdminCore();
});
