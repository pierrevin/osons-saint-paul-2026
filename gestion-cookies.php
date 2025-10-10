<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des cookies | Osons Saint-Paul 2026</title>
    <meta name="description" content="Gérez vos préférences de cookies sur le site Osons Saint-Paul 2026. Information sur l'utilisation des cookies et protection de vos données.">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="https://osons-saint-paul.fr/gestion-cookies.php">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://osons-saint-paul.fr/gestion-cookies.php">
    <meta property="og:title" content="Gestion des cookies | Osons Saint-Paul 2026">
    <meta property="og:description" content="Gérez vos préférences de cookies sur le site Osons Saint-Paul 2026.">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="Osons Saint-Paul 2026">
    
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-B544VTFXWF"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-B544VTFXWF');
    </script>
</head>
<body>
    <div class="legal-page">
        <div class="legal-container">
            <header class="legal-header">
                <h1>Gestion des cookies</h1>
                <p>Informations sur l'utilisation des cookies sur notre site</p>
            </header>

            <main class="legal-content">
                <section class="legal-section">
                    <h2>Qu'est-ce qu'un cookie ?</h2>
                    <p>Un cookie est un petit fichier texte déposé sur votre ordinateur, tablette ou smartphone lors de la visite d'un site web. Il permet au site de mémoriser vos actions et préférences pendant une période donnée, afin que vous n'ayez pas à les ressaisir à chaque fois que vous revenez sur le site ou naviguez d'une page à l'autre.</p>
                </section>

                <section class="legal-section">
                    <h2>Cookies utilisés sur ce site</h2>
                    
                    <h3>Cookies techniques (exemptés de consentement)</h3>
                    <div class="cookie-category">
                        <h4>Cookies de session PHP</h4>
                        <p><strong>Nom :</strong> PHPSESSID</p>
                        <p><strong>Finalité :</strong> Maintien de la session utilisateur, sécurité des formulaires</p>
                        <p><strong>Durée :</strong> Session (supprimé à la fermeture du navigateur)</p>
                        <p><strong>Obligatoire :</strong> Oui</p>
                    </div>

                    <div class="cookie-category">
                        <h4>Tokens CSRF</h4>
                        <p><strong>Finalité :</strong> Protection contre les attaques CSRF (Cross-Site Request Forgery)</p>
                        <p><strong>Durée :</strong> Session</p>
                        <p><strong>Obligatoire :</strong> Oui</p>
                    </div>

                    <div class="cookie-category">
                        <h4>Préférences d'administration</h4>
                        <p><strong>Finalité :</strong> Mémorisation des préférences de l'interface d'administration</p>
                        <p><strong>Durée :</strong> 30 jours</p>
                        <p><strong>Obligatoire :</strong> Non (pour les administrateurs uniquement)</p>
                    </div>

                    <h3>Cookies nécessitant votre consentement</h3>
                    <div class="cookie-category">
                        <h4>Google Analytics</h4>
                        <p><strong>Noms :</strong> _ga, _gid, _gat</p>
                        <p><strong>Finalité :</strong> Analyse du trafic et du comportement des visiteurs</p>
                        <p><strong>Durée :</strong> 2 ans (_ga), 24 heures (_gid), 1 minute (_gat)</p>
                        <p><strong>Obligatoire :</strong> Non</p>
                        <p><strong>Données collectées :</strong> Pages visitées, durée de visite, source de trafic, type d'appareil</p>
                    </div>

                    <div class="cookie-category">
                        <h4>Google reCAPTCHA</h4>
                        <p><strong>Finalité :</strong> Protection contre le spam et les robots</p>
                        <p><strong>Durée :</strong> Variable selon l'utilisation</p>
                        <p><strong>Obligatoire :</strong> Non (uniquement pour les formulaires)</p>
                    </div>
                </section>

                <section class="legal-section">
                    <h2>Gestion de vos préférences</h2>
                    <p>Vous pouvez à tout moment modifier vos préférences concernant les cookies :</p>
                    
                    <div class="cookie-controls">
                        <h3>Paramètres actuels</h3>
                        <div class="cookie-setting">
                            <label>
                                <input type="checkbox" id="analytics-cookies" checked>
                                <span>Cookies d'analyse (Google Analytics)</span>
                            </label>
                            <p class="cookie-description">Permet d'analyser l'utilisation du site pour l'améliorer</p>
                        </div>
                        
                        <div class="cookie-setting">
                            <label>
                                <input type="checkbox" id="security-cookies" checked disabled>
                                <span>Cookies de sécurité (reCAPTCHA)</span>
                            </label>
                            <p class="cookie-description">Nécessaires pour la sécurité des formulaires</p>
                        </div>
                        
                        <button class="btn btn-primary" onclick="saveCookiePreferences()">Sauvegarder mes préférences</button>
                    </div>
                </section>

                <section class="legal-section">
                    <h2>Comment supprimer les cookies</h2>
                    <p>Vous pouvez supprimer les cookies déjà stockés sur votre appareil :</p>
                    
                    <h3>Dans votre navigateur :</h3>
                    <ul>
                        <li><strong>Chrome :</strong> Paramètres > Confidentialité et sécurité > Cookies et autres données de site</li>
                        <li><strong>Firefox :</strong> Options > Vie privée et sécurité > Cookies et données de sites</li>
                        <li><strong>Safari :</strong> Préférences > Confidentialité > Gérer les données de sites web</li>
                        <li><strong>Edge :</strong> Paramètres > Cookies et autorisations de site</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <h2>Conséquences du refus des cookies</h2>
                    <p>Si vous refusez les cookies non essentiels :</p>
                    <ul>
                        <li>Le site fonctionnera normalement</li>
                        <li>Vous ne bénéficierez pas de l'analyse d'audience</li>
                        <li>Certaines fonctionnalités de sécurité peuvent être limitées</li>
                    </ul>
                </section>

                <section class="legal-section">
                    <h2>Contact</h2>
                    <p>Pour toute question concernant l'utilisation des cookies, vous pouvez nous contacter :</p>
                    <p><strong>Email :</strong> <a href="mailto:bonjour@osons-saint-paul.fr">bonjour@osons-saint-paul.fr</a></p>
                </section>
            </main>

            <footer class="legal-footer">
                <div class="footer-links">
                    <a href="/politique-confidentialite.php">Politique de confidentialité</a>
                    <a href="/mentions-legales.php">Mentions légales</a>
                    <a href="/gestion-cookies.php">Gestion des cookies</a>
                </div>
                <div class="footer-info">
                    © 2025 Osons Saint-Paul | Pierre Vincenot
                </div>
            </footer>
        </div>
    </div>

    <script>
        // Gestion des préférences de cookies
        function saveCookiePreferences() {
            const analytics = document.getElementById('analytics-cookies').checked;
            
            // Sauvegarder les préférences
            localStorage.setItem('cookie-preferences', JSON.stringify({
                analytics: analytics,
                timestamp: new Date().toISOString()
            }));
            
            // Appliquer les préférences
            if (!analytics) {
                // Désactiver Google Analytics
                window['ga-disable-G-B544VTFXWF'] = true;
            } else {
                // Réactiver Google Analytics
                window['ga-disable-G-B544VTFXWF'] = false;
            }
            
            alert('Vos préférences ont été sauvegardées.');
        }
        
        // Charger les préférences au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const preferences = localStorage.getItem('cookie-preferences');
            if (preferences) {
                const prefs = JSON.parse(preferences);
                document.getElementById('analytics-cookies').checked = prefs.analytics;
            }
        });
    </script>
</body>
</html>
