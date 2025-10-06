/**
 * AdminTabs - Gestionnaire des onglets
 */
class AdminTabs {
    constructor() {
        this.activeTab = null;
        this.tabGroups = new Map();
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeTabs();
        console.log('🚀 AdminTabs initialisé');
    }
    
    bindEvents() {
        // Gestion des clics sur les boutons d'onglets
        document.addEventListener('click', (e) => {
            if (e.target.closest('.tab-button')) {
                e.preventDefault();
                const button = e.target.closest('.tab-button');
                const tabName = this.extractTabName(button);
                if (tabName) {
                    this.switchTo(tabName);
                }
            }
        });
    }
    
    extractTabName(button) {
        const onclick = button.getAttribute('onclick');
        if (onclick) {
            const match = onclick.match(/AdminTabs\.switchTo\('([^']+)'\)/);
            return match ? match[1] : null;
        }
        return null;
    }
    
    initializeTabs() {
        // Trouver tous les groupes d'onglets
        const tabGroups = document.querySelectorAll('.tab-buttons');
        
        tabGroups.forEach(group => {
            const groupId = group.closest('.proposals-tabs')?.id || 'default';
            const buttons = group.querySelectorAll('.tab-button');
            const contents = group.parentElement.querySelectorAll('.tab-content');
            
            this.tabGroups.set(groupId, {
                buttons: Array.from(buttons),
                contents: Array.from(contents)
            });
            
            // Activer le premier onglet actif par défaut
            const activeButton = group.querySelector('.tab-button.active');
            if (activeButton) {
                const tabName = this.extractTabName(activeButton);
                if (tabName) {
                    this.activeTab = tabName;
                }
            }
        });
    }
    
    switchTo(tabName) {
        console.log('🔄 Changement d\'onglet vers:', tabName);
        
        try {
            // Trouver le groupe d'onglets qui contient cet onglet
            const tabGroup = this.findTabGroup(tabName);
            if (!tabGroup) {
                console.warn('⚠️ Groupe d\'onglets introuvable pour:', tabName);
                return;
            }
            
            // Désactiver tous les onglets du groupe
            tabGroup.buttons.forEach(btn => btn.classList.remove('active'));
            tabGroup.contents.forEach(content => content.classList.remove('active'));
            
            // Activer l'onglet sélectionné
            const activeButton = tabGroup.buttons.find(btn => 
                btn.getAttribute('onclick')?.includes(`'${tabName}'`)
            );
            
            const activeContent = document.getElementById(tabName);
            
            if (activeButton) {
                activeButton.classList.add('active');
            }
            
            if (activeContent) {
                activeContent.classList.add('active');
            }
            
            // Mettre à jour l'état actif
            this.activeTab = tabName;
            
            // Déclencher l'événement de changement d'onglet
            this.onTabChange(tabName);
            
            console.log('✅ Onglet activé:', tabName);
            
        } catch (error) {
            console.error('❌ Erreur de changement d\'onglet:', error);
        }
    }
    
    findTabGroup(tabName) {
        for (const [groupId, tabGroup] of this.tabGroups) {
            const hasButton = tabGroup.buttons.some(btn => 
                btn.getAttribute('onclick')?.includes(`'${tabName}'`)
            );
            if (hasButton) {
                return tabGroup;
            }
        }
        return null;
    }
    
    onTabChange(tabName) {
        // Actions spécifiques selon l'onglet
        switch (tabName) {
            case 'citizen-proposals':
                this.loadCitizenProposals();
                break;
            case 'programme-proposals':
                this.refreshProgrammeProposals();
                break;
        }
        
        // Déclencher l'événement personnalisé
        const event = new CustomEvent('tab-change', {
            detail: { tabName }
        });
        document.dispatchEvent(event);
    }
    
    loadCitizenProposals() {
        console.log('🔄 Chargement des propositions citoyennes...');
        
        const container = document.getElementById('citizen-proposals-container');
        if (!container) {
            return;
        }
        
        // Simuler le chargement (remplacer par un vrai appel AJAX)
        container.innerHTML = '<p>Chargement des propositions citoyennes...</p>';
        
        // Ici, vous feriez un appel AJAX pour récupérer les données
        setTimeout(() => {
            container.innerHTML = `
                <div class="citizen-proposals-list">
                    <p>Aucune proposition citoyenne en attente.</p>
                    <button class="btn btn-primary" onclick="AdminModal.open('addProposalModal')">
                        Ajouter une proposition
                    </button>
                </div>
            `;
        }, 500);
    }
    
    refreshProgrammeProposals() {
        console.log('🔄 Actualisation des propositions du programme...');
        // Logique d'actualisation si nécessaire
    }
    
    // Méthode statique pour compatibilité
    static switchTo(tabName) {
        if (!window.adminTabs) {
            window.adminTabs = new AdminTabs();
        }
        window.adminTabs.switchTo(tabName);
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    window.adminTabs = new AdminTabs();
});
