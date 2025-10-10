/**
 * Système de gestion du consentement des cookies RGPD
 */
class CookieConsent {
    constructor() {
        this.cookieName = 'cookie-consent';
        this.consentDuration = 365; // jours
        this.init();
    }
    
    init() {
        // Vérifier si l'utilisateur a déjà donné son consentement
        if (!this.hasConsent()) {
            this.showConsentBanner();
        } else {
            this.applyConsent();
        }
    }
    
    hasConsent() {
        return localStorage.getItem(this.cookieName) !== null;
    }
    
    getConsent() {
        const consent = localStorage.getItem(this.cookieName);
        return consent ? JSON.parse(consent) : null;
    }
    
    showConsentBanner() {
        // Créer le banner de consentement
        const banner = document.createElement('div');
        banner.id = 'cookie-consent-banner';
        banner.innerHTML = `
            <div class="cookie-banner-content">
                <div class="cookie-banner-text">
                    <h4>🍪 Gestion des cookies</h4>
                    <p>Nous utilisons des cookies pour améliorer votre expérience sur notre site. Vous pouvez choisir quels cookies accepter ou refuser en cliquant sur "Personnaliser".</p>
                </div>
                
                <div class="cookie-banner-preferences" id="cookie-preferences" style="display: none;">
                    <div class="preference-item">
                        <input type="checkbox" id="pref-analytics" checked>
                        <label for="pref-analytics">
                            <strong>Cookies d'analyse</strong>
                            <small>Statistiques de visite anonymisées (Google Analytics)</small>
                        </label>
                    </div>
                    <div class="preference-item">
                        <input type="checkbox" id="pref-security" checked disabled>
                        <label for="pref-security">
                            <strong>Cookies de sécurité</strong>
                            <small>Obligatoires pour le fonctionnement du site</small>
                        </label>
                    </div>
                    <div class="preference-item">
                        <input type="checkbox" id="pref-preferences" checked>
                        <label for="pref-preferences">
                            <strong>Cookies de préférences</strong>
                            <small>Mémorisent vos choix pour vos prochaines visites</small>
                        </label>
                    </div>
                </div>
                
                <div class="cookie-banner-actions">
                    <button class="btn btn-outline-secondary btn-sm" onclick="cookieConsent.togglePreferences()">
                        <span id="pref-toggle-text">Personnaliser</span>
                    </button>
                    <button class="btn btn-success btn-sm" id="save-preferences-btn" onclick="cookieConsent.saveCustomPreferences()" style="display: none;">
                        Sauvegarder
                    </button>
                    <button class="btn btn-secondary btn-sm" onclick="cookieConsent.rejectAll()">
                        Refuser
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="cookieConsent.acceptAll()">
                        Accepter tout
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(banner);
        
        // Ajouter les styles CSS
        this.addBannerStyles();
    }
    
    addBannerStyles() {
        const style = document.createElement('style');
        style.textContent = `
            #cookie-consent-banner {
                position: fixed;
                bottom: 20px;
                left: 20px;
                max-width: 400px;
                background: var(--white, #ffffff);
                border: 1px solid var(--gray-300, #dee2e6);
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                z-index: 1000;
                padding: 1rem;
                animation: slideUp 0.3s ease-out;
            }
            
            @keyframes slideUp {
                from { transform: translateY(100%); }
                to { transform: translateY(0); }
            }
            
            .cookie-banner-content {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            
            .cookie-banner-text h4 {
                margin: 0 0 0.5rem 0;
                color: var(--dark-blue, #004a6d);
                font-size: 0.95rem;
            }
            
            .cookie-banner-text p {
                margin: 0;
                color: var(--gray-700, #495057);
                font-size: 0.85rem;
                line-height: 1.3;
            }
            
            .cookie-banner-actions {
                display: flex;
                gap: 0.4rem;
                flex-wrap: wrap;
            }
            
            .cookie-banner-actions .btn {
                white-space: nowrap;
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
            
            .btn-success {
                background: #28a745;
                color: white;
                border: 1px solid #28a745;
            }
            
            .btn-success:hover {
                background: #218838;
                border-color: #1e7e34;
            }
            
            .cookie-banner-preferences {
                margin: 0.5rem 0;
                padding: 0.75rem;
                background: rgba(0,0,0,0.05);
                border-radius: 8px;
            }
            
            .preference-item {
                display: flex;
                align-items: flex-start;
                gap: 0.4rem;
                margin-bottom: 0.5rem;
            }
            
            .preference-item:last-child {
                margin-bottom: 0;
            }
            
            .preference-item input[type="checkbox"] {
                margin-top: 0.2rem;
                flex-shrink: 0;
            }
            
            .preference-item label {
                flex: 1;
                cursor: pointer;
                color: #333;
            }
            
            .preference-item label strong {
                display: block;
                color: #004a6d;
                font-weight: 600;
                margin-bottom: 0.1rem;
                font-size: 0.85rem;
            }
            
            .preference-item label small {
                display: block;
                color: #666;
                font-size: 0.75rem;
                line-height: 1.2;
            }
            
            .preference-item input[type="checkbox"]:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
            
            
            @media (max-width: 768px) {
                #cookie-consent-banner {
                    bottom: 10px;
                    left: 10px;
                    right: 10px;
                    max-width: none;
                }
                
                .cookie-banner-actions {
                    justify-content: center;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    acceptAll() {
        const consent = {
            necessary: true,
            analytics: true,
            preferences: true,
            timestamp: new Date().toISOString()
        };
        
        this.saveConsent(consent);
        this.hideBanner();
        this.applyConsent();
    }
    
    rejectAll() {
        const consent = {
            necessary: true, // Toujours nécessaire
            analytics: false,
            preferences: false,
            timestamp: new Date().toISOString()
        };
        
        this.saveConsent(consent);
        this.hideBanner();
        this.applyConsent();
    }
    
    saveCustomPreferences() {
        const analytics = document.getElementById('pref-analytics').checked;
        const security = document.getElementById('pref-security').checked; // Toujours true (disabled)
        const preferences = document.getElementById('pref-preferences').checked;
        
        const consent = {
            necessary: true, // Toujours nécessaire
            analytics: analytics,
            preferences: preferences,
            timestamp: new Date().toISOString()
        };
        
        this.saveConsent(consent);
        this.hideBanner();
        this.applyConsent();
    }
    
    togglePreferences() {
        const preferences = document.getElementById('cookie-preferences');
        const toggleText = document.getElementById('pref-toggle-text');
        const saveBtn = document.getElementById('save-preferences-btn');
        
        if (preferences.style.display === 'none') {
            preferences.style.display = 'block';
            saveBtn.style.display = 'inline-block';
            toggleText.textContent = 'Masquer';
        } else {
            preferences.style.display = 'none';
            saveBtn.style.display = 'none';
            toggleText.textContent = 'Personnaliser';
        }
    }
    
    
    showPreferences() {
        // Rediriger vers la page de gestion des cookies (pour les utilisateurs avancés)
        window.location.href = '/gestion-cookies.php';
    }
    
    saveConsent(consent) {
        localStorage.setItem(this.cookieName, JSON.stringify(consent));
    }
    
    hideBanner() {
        const banner = document.getElementById('cookie-consent-banner');
        if (banner) {
            banner.style.animation = 'slideDown 0.3s ease-out';
            setTimeout(() => {
                banner.remove();
            }, 300);
        }
    }
    
    applyConsent() {
        const consent = this.getConsent();
        if (!consent) return;
        
        // Appliquer les préférences Google Analytics
        if (consent.analytics) {
            // Activer Google Analytics
            window['ga-disable-G-B544VTFXWF'] = false;
        } else {
            // Désactiver Google Analytics
            window['ga-disable-G-B544VTFXWF'] = true;
        }
        
        // Appliquer les préférences de cookies
        if (!consent.preferences) {
            // Supprimer les cookies de préférences existants
            localStorage.removeItem('cookie-consent');
            // Note: Les cookies de sécurité restent actifs car nécessaires
        }
    }
    
    // Méthode pour réinitialiser le consentement (pour les tests)
    reset() {
        localStorage.removeItem(this.cookieName);
        location.reload();
    }
}

// Initialiser le système de consentement
const cookieConsent = new CookieConsent();

// Ajouter l'animation slideDown
const slideDownStyle = document.createElement('style');
slideDownStyle.textContent = `
    @keyframes slideDown {
        from { transform: translateY(0); }
        to { transform: translateY(100%); }
    }
`;
document.head.appendChild(slideDownStyle);
