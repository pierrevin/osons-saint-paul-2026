// ===== FONCTIONS UTILITAIRES =====
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// ===== NAVIGATION SMOOTH SCROLL =====
document.addEventListener('DOMContentLoaded', function() {
    // Navigation smooth scroll
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = targetSection.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Boutons hero smooth scroll
    const heroButtons = document.querySelectorAll('.hero-buttons .btn');
    heroButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = targetSection.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
});

// ===== HEADER SCROLL EFFECT =====
const header = document.querySelector('.header');

function updateHeaderOnScroll() {
    const scrollY = window.scrollY;
    
    if (scrollY > 100) {
        header.style.background = 'rgba(250, 245, 238, 0.98)';
        header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
    } else {
        header.style.background = 'rgba(250, 245, 238, 0.95)';
        header.style.boxShadow = 'none';
    }
}

window.addEventListener('scroll', throttle(updateHeaderOnScroll, 10));

// ===== FILTRES DES PROPOSITIONS =====
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const propositionCards = document.querySelectorAll('.proposition-card');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Retirer la classe active de tous les boutons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            // Récupérer le filtre sélectionné
            const filter = this.getAttribute('data-filter');
            
            // Filtrer les cartes
            propositionCards.forEach(card => {
                const category = card.getAttribute('data-category');
                const isCitizenCard = card.querySelector('.card-badge.citoyenne') !== null;
                
                let shouldShow = false;
                
                if (filter === 'all') {
                    shouldShow = true;
                } else if (filter === 'citoyens') {
                    shouldShow = isCitizenCard;
                } else {
                    shouldShow = category === filter && !isCitizenCard;
                }
                
                if (shouldShow) {
                    card.style.display = 'block';
                    // Animation d'apparition
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }, 100);
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        card.style.display = 'none';
                    }, 300);
                }
            });
        });
    });
});

// ===== ANIMATIONS AU SCROLL =====
function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

function animateOnScroll() {
    const elementsToAnimate = document.querySelectorAll('.section-title, .event, .team-member, .proposition-card');
    
    elementsToAnimate.forEach(element => {
        if (isElementInViewport(element) && !element.classList.contains('animated')) {
            element.classList.add('animated');
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }
    });
}

// Initialiser les éléments cachés
document.addEventListener('DOMContentLoaded', function() {
    const elementsToAnimate = document.querySelectorAll('.section-title, .event, .team-member, .proposition-card');
    
    elementsToAnimate.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    });
});

window.addEventListener('scroll', throttle(animateOnScroll, 100));

// ===== CARTES POSTALES INTERACTIVES =====
document.addEventListener('DOMContentLoaded', function() {
    const propositionCards = document.querySelectorAll('.proposition-card');
    
    propositionCards.forEach(card => {
        // Gestion du hover sur desktop
        card.addEventListener('mouseenter', function() {
            if (window.innerWidth > 768) {
                this.classList.add('hovered');
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (window.innerWidth > 768) {
                this.classList.remove('hovered');
            }
        });
        
        // Gestion du tap sur mobile
        card.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                this.classList.toggle('hovered');
            }
        });
    });
});

// ===== NEWSLETTER =====
document.addEventListener('DOMContentLoaded', function() {
    const newsletterForm = document.querySelector('.newsletter');
    const emailInput = document.querySelector('.newsletter-input');
    const submitButton = document.querySelector('.newsletter-btn');
    
    if (newsletterForm && emailInput && submitButton) {
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const email = emailInput.value.trim();
            
            if (validateEmail(email)) {
                // Simulation d'envoi
                submitButton.textContent = 'Inscription...';
                submitButton.style.background = '#2F6E4F';
                
                setTimeout(() => {
                    submitButton.textContent = 'Inscrit !';
                    emailInput.value = '';
                    
                    setTimeout(() => {
                        submitButton.textContent = 'S\'inscrire';
                        submitButton.style.background = '#F2775A';
                    }, 2000);
                }, 1000);
                
                // Ici vous pouvez ajouter l'intégration avec votre service d'email
                console.log('Email à inscrire:', email);
            } else {
                // Animation d'erreur
                emailInput.style.border = '2px solid #F2775A';
                emailInput.placeholder = 'Veuillez entrer un email valide';
                
                setTimeout(() => {
                    emailInput.style.border = 'none';
                    emailInput.placeholder = 'Votre email';
                }, 3000);
            }
        });
    }
});

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// ===== LAZY LOADING DES IMAGES =====
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
});

// ===== GESTION DES COOKIES =====
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// ===== PERFORMANCE ET OPTIMISATIONS =====
// Préchargement des images critiques
function preloadCriticalImages() {
    const criticalImages = [
        'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=1920&h=1080&fit=crop'
    ];
    
    criticalImages.forEach(src => {
        const img = new Image();
        img.src = src;
    });
}

document.addEventListener('DOMContentLoaded', preloadCriticalImages);

// ===== GESTION DES ERREURS =====
window.addEventListener('error', function(e) {
    console.error('Erreur JavaScript:', e.error);
    // Ici vous pouvez ajouter un service de tracking d'erreurs
});

// ===== ACCESSIBILITÉ =====
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du focus pour l'accessibilité
    const focusableElements = document.querySelectorAll('a, button, input, textarea, select');
    
    focusableElements.forEach(element => {
        element.addEventListener('focus', function() {
            this.style.outline = '2px solid #F2775A';
            this.style.outlineOffset = '2px';
        });
        
        element.addEventListener('blur', function() {
            this.style.outline = 'none';
        });
    });
    
    // Gestion du clavier pour les cartes
    const propositionCards = document.querySelectorAll('.proposition-card');
    propositionCards.forEach(card => {
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        card.setAttribute('aria-label', 'Voir les détails de cette proposition');
        
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});

// ===== ANALYTICS ET TRACKING =====
function trackEvent(category, action, label) {
    // Ici vous pouvez intégrer Google Analytics ou un autre service de tracking
    console.log('Event tracked:', { category, action, label });
    
    // Exemple d'intégration Google Analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', action, {
            event_category: category,
            event_label: label
        });
    }
}

// Tracking des interactions importantes
document.addEventListener('DOMContentLoaded', function() {
    // Tracking des clics sur les filtres
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            trackEvent('Navigation', 'Filter Click', this.getAttribute('data-filter'));
        });
    });
    
    // Tracking des clics sur les boutons CTA
    const ctaButtons = document.querySelectorAll('.btn-primary, .btn-secondary, .btn-yellow');
    ctaButtons.forEach(button => {
        button.addEventListener('click', function() {
            trackEvent('CTA', 'Button Click', this.textContent.trim());
        });
    });
    
    // Tracking des inscriptions newsletter
    const newsletterBtn = document.querySelector('.newsletter-btn');
    if (newsletterBtn) {
        newsletterBtn.addEventListener('click', function() {
            trackEvent('Newsletter', 'Subscribe', 'Footer');
        });
    }
});

// ===== FONCTIONNALITÉS AVANCÉES =====
// Partage social
function shareOnSocial(platform, url, text) {
    const encodedUrl = encodeURIComponent(url || window.location.href);
    const encodedText = encodeURIComponent(text || document.title);
    
    const shareUrls = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
        twitter: `https://twitter.com/intent/tweet?url=${encodedUrl}&text=${encodedText}`,
        linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`,
        whatsapp: `https://wa.me/?text=${encodedText}%20${encodedUrl}`
    };
    
    if (shareUrls[platform]) {
        window.open(shareUrls[platform], '_blank', 'width=600,height=400');
        trackEvent('Social', 'Share', platform);
    }
}

// Impression de la page
function printPage() {
    window.print();
    trackEvent('Action', 'Print', 'Page');
}

// ===== TRANSITIONS PAR DÉGRADÉ AU SCROLL =====
function initializeGradientTransitions() {
    const sections = document.querySelectorAll('section');
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '-50px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    sections.forEach(section => {
        observer.observe(section);
    });
}

// ===== HEADER STICKY =====
function initializeHeaderSticky() {
    const header = document.getElementById('headerSticky');
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Configuration des sections
    const sections = [
        { id: 'hero', title: 'Accueil' },
        { id: 'programme', title: 'Programme' },
        { id: 'equipe', title: 'Équipe' },
        { id: 'rencontres', title: 'Rendez-vous' },
        { id: 'charte', title: 'Charte' },
        { id: 'idees', title: 'Idées' }
    ];
    
    let lastScrollY = window.scrollY;
    let ticking = false;
    
    // Fonction pour gérer l'affichage du header
    function updateHeader() {
        const scrollY = window.scrollY;
        
        // Afficher le header après avoir scrollé un peu
        if (scrollY > 100) {
            header.classList.add('visible');
        } else {
            header.classList.remove('visible');
        }
        
        lastScrollY = scrollY;
        ticking = false;
    }
    
    // Fonction pour mettre à jour le lien actif
    function updateActiveLink() {
        let current = '';
        
        sections.forEach(section => {
            const element = document.getElementById(section.id);
            if (element) {
                const rect = element.getBoundingClientRect();
                if (rect.top <= 100 && rect.bottom >= 100) {
                    current = section.id;
                }
            }
        });
        
        // Mettre à jour les liens
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    }
    
    // Event listener pour le scroll
    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(() => {
                updateHeader();
                updateActiveLink();
            });
            ticking = true;
        }
    }
    
    window.addEventListener('scroll', onScroll);
    
    // Navigation smooth scroll
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const headerHeight = header.offsetHeight;
                const targetPosition = targetElement.offsetTop - headerHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Initialiser l'état
    updateHeader();
    updateActiveLink();
}

// ===== MENU MOBILE =====
function initializeMobileMenu() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const headerNav = document.querySelector('.header-nav');
    
    if (mobileMenuToggle && headerNav) {
        mobileMenuToggle.addEventListener('click', function() {
            headerNav.classList.toggle('mobile-active');
            mobileMenuToggle.classList.toggle('active');
        });
        
        // Fermer le menu en cliquant sur un lien
        const navLinks = headerNav.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                headerNav.classList.remove('mobile-active');
                mobileMenuToggle.classList.remove('active');
            });
        });
    }
}

// ===== NEWSLETTER =====
function initializeNewsletter() {
    const newsletterForm = document.getElementById('newsletterForm');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('newsletterEmail').value;
            const submitBtn = newsletterForm.querySelector('.btn');
            const originalText = submitBtn.textContent;
            
            // Animation de chargement
            submitBtn.textContent = 'Inscription...';
            submitBtn.disabled = true;
            
            // Simulation d'envoi (à remplacer par un vrai appel API)
            setTimeout(() => {
                // Message de succès
                submitBtn.textContent = 'Inscrit !';
                submitBtn.style.background = 'var(--deep-green)';
                
                // Réinitialiser après 3 secondes
                setTimeout(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    submitBtn.style.background = '';
                    newsletterForm.reset();
                }, 3000);
                
                // Ici vous pourriez ajouter un appel à votre API
                console.log('Newsletter subscription:', email);
            }, 1500);
        });
    }
}

// ===== INITIALISATION FINALE =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('Site Osons Saint-Paul 2026 chargé avec succès !');
    
    // Ajouter une classe au body pour indiquer que JS est activé
    document.body.classList.add('js-enabled');
    
    // Initialiser les transitions par dégradé
    initializeGradientTransitions();
    
    // Initialiser le header sticky
    initializeHeaderSticky();
    
    // Initialiser le menu mobile
    initializeMobileMenu();
    
    // Initialiser la newsletter
    initializeNewsletter();
    
    // Animation d'entrée du hero
    setTimeout(() => {
        document.querySelector('.hero-content').style.opacity = '1';
    }, 100);
});

// ===== GESTION DES RÉSIZES =====
window.addEventListener('resize', debounce(function() {
    // Réinitialiser les cartes sur mobile/desktop
    const propositionCards = document.querySelectorAll('.proposition-card');
    propositionCards.forEach(card => {
        if (window.innerWidth > 768) {
            card.classList.remove('hovered');
        }
    });
}, 250));
