<?php
// Configuration de base
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politique de Confidentialité - Osons Saint-Paul 2026</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&family=Lato:wght@300;400;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LeOrNorAAAAAGfkiHS2IqTbd5QbQHvinxR_4oek"></script>
    <style>
        .privacy-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .privacy-header {
            text-align: center;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid var(--cream);
        }

        .privacy-header h1 {
            font-family: var(--font-script);
            font-size: 2.5rem;
            color: var(--coral);
            margin-bottom: 1rem;
        }

        .privacy-header p {
            color: var(--dark-blue);
            font-size: 1.1rem;
        }

        .privacy-section {
            margin-bottom: 2.5rem;
        }

        .privacy-section h2 {
            font-family: var(--font-script);
            font-size: 1.8rem;
            color: var(--deep-green);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .privacy-section h3 {
            font-size: 1.3rem;
            color: var(--dark-blue);
            margin: 1.5rem 0 0.8rem 0;
        }

        .privacy-section p, .privacy-section li {
            line-height: 1.8;
            color: #333;
            margin-bottom: 1rem;
        }

        .privacy-section ul {
            margin-left: 2rem;
        }

        .highlight-box {
            background: var(--cream);
            padding: 1.5rem;
            border-radius: 15px;
            border-left: 4px solid var(--coral);
            margin: 1.5rem 0;
        }

        .contact-info {
            background: var(--cream);
            padding: 2rem;
            border-radius: 15px;
            margin-top: 2rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: var(--coral);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .btn-back:hover {
            background: var(--deep-green);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .privacy-container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="proposition-citoyenne.php" class="btn-back">
            ← Retour au formulaire
        </a>

        <div class="privacy-container">
            <div class="privacy-header">
                <h1>🔒 Politique de Confidentialité</h1>
                <p>Dernière mise à jour : <?= date('d/m/Y') ?></p>
            </div>

            <div class="privacy-section">
                <h2>📋 1. Introduction</h2>
                <p>
                    La protection de vos données personnelles est une priorité pour l'équipe Osons Saint-Paul 2026. 
                    Cette politique de confidentialité vous informe sur la manière dont nous collectons, utilisons et protégeons 
                    vos données personnelles lors de l'utilisation de notre formulaire de proposition citoyenne.
                </p>
            </div>

            <div class="privacy-section">
                <h2>👤 2. Responsable du traitement</h2>
                <div class="highlight-box">
                    <p><strong>Responsable :</strong> Pierre Vincenot</p>
                    <p><strong>Adresse :</strong> Saint-Paul-Sur-Save, 31530, France</p>
                    <p><strong>Contact :</strong> <a href="#contact-form" class="privacy-link">Formulaire de contact sécurisé</a></p>
                </div>
            </div>

            <div class="privacy-section">
                <h2>📊 3. Données collectées</h2>
                <p>Nous collectons les données suivantes lorsque vous soumettez une proposition :</p>
                
                <h3>Données obligatoires :</h3>
                <ul>
                    <li>Adresse email</li>
                    <li>Titre de la proposition</li>
                    <li>Description de la proposition</li>
                    <li>Catégorie(s) concernée(s)</li>
                    <li>Bénéficiaires de la proposition</li>
                    <li>Consentements (publication et RGPD)</li>
                </ul>

                <h3>Données facultatives :</h3>
                <ul>
                    <li>Nom et prénom</li>
                    <li>Commune de résidence</li>
                    <li>Numéro de téléphone</li>
                    <li>Informations sur votre engagement citoyen</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>🎯 4. Finalités du traitement</h2>
                <p>Vos données sont collectées pour les finalités suivantes :</p>
                <ul>
                    <li><strong>Gestion des propositions citoyennes :</strong> Étudier, valider et intégrer vos propositions au programme électoral</li>
                    <li><strong>Communication :</strong> Vous tenir informé de l'avancement de votre proposition</li>
                    <li><strong>Publication :</strong> Publier votre proposition sur notre site (avec votre consentement)</li>
                    <li><strong>Statistiques :</strong> Analyser les catégories de propositions les plus demandées</li>
                    <li><strong>Sécurité :</strong> Prévenir le spam et les abus grâce à Google reCAPTCHA</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>⏱️ 5. Durée de conservation</h2>
                <p>Vos données sont conservées pendant :</p>
                <ul>
                    <li><strong>Propositions validées :</strong> Durée du mandat électoral + 1 an (archive)</li>
                    <li><strong>Propositions en attente :</strong> 6 mois maximum</li>
                    <li><strong>Propositions rejetées :</strong> 3 mois puis suppression</li>
                    <li><strong>Logs de sécurité :</strong> 12 mois maximum</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>🔐 6. Sécurité des données</h2>
                <p>Nous mettons en œuvre les mesures de sécurité suivantes :</p>
                <ul>
                    <li>Hébergement sécurisé avec protocole HTTPS</li>
                    <li>Protection CSRF contre les attaques</li>
                    <li>Validation reCAPTCHA v3 contre les robots</li>
                    <li>Limitation du taux de soumission par email</li>
                    <li>Sauvegardes régulières des données</li>
                    <li>Accès restreint aux données (administrateurs uniquement)</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>👥 7. Destinataires des données</h2>
                <p>Vos données peuvent être transmises aux destinataires suivants :</p>
                <ul>
                    <li><strong>Équipe Osons Saint-Paul 2026 :</strong> Pour étudier votre proposition</li>
                    <li><strong>Membres de l'équipe de campagne :</strong> Pour validation et intégration au programme</li>
                    <li><strong>Public (proposition validée) :</strong> Votre proposition peut être publiée sur le site (sans vos coordonnées personnelles)</li>
                </ul>
            </div>

            <div class="privacy-section">
                <h2>🌐 8. Services tiers</h2>
                <div class="highlight-box">
                    <p><strong>Google reCAPTCHA :</strong> Ce site utilise Google reCAPTCHA v3 pour se protéger contre le spam et les abus. 
                    L'utilisation de reCAPTCHA est soumise aux 
                    <a href="https://policies.google.com/privacy" target="_blank" style="color: var(--coral); text-decoration: underline;">Règles de confidentialité</a> 
                    et aux 
                    <a href="https://policies.google.com/terms" target="_blank" style="color: var(--coral); text-decoration: underline;">Conditions d'utilisation</a> 
                    de Google.</p>
                </div>
            </div>

            <div class="privacy-section">
                <h2>🖥️ 9. Hébergement des données</h2>
                <p>Vos données sont hébergées en France, garantissant une conformité totale avec le RGPD :</p>
                <div class="highlight-box">
                    <p><strong>Hébergeur :</strong> OVH</p>
                    <p><strong>Localisation :</strong> Datacenters en France (conformité RGPD)</p>
                    <p><strong>Certifications :</strong> ISO 27001, HDS (Hébergement de Données de Santé)</p>
                    <p><strong>Sécurité :</strong> Infrastructure redondante, sauvegardes quotidiennes, protection DDoS</p>
                </div>
            </div>

            <div class="privacy-section">
                <h2>✅ 10. Vos droits</h2>
                <p>Conformément au RGPD, vous disposez des droits suivants :</p>
                <ul>
                    <li><strong>Droit d'accès :</strong> Obtenir une copie de vos données personnelles</li>
                    <li><strong>Droit de rectification :</strong> Corriger des données inexactes</li>
                    <li><strong>Droit à l'effacement :</strong> Demander la suppression de vos données</li>
                    <li><strong>Droit à la portabilité :</strong> Recevoir vos données dans un format structuré</li>
                    <li><strong>Droit d'opposition :</strong> S'opposer au traitement de vos données</li>
                    <li><strong>Droit de limitation :</strong> Demander la limitation du traitement</li>
                    <li><strong>Droit de retrait du consentement :</strong> Retirer votre consentement à tout moment</li>
                </ul>

                <div class="highlight-box">
                    <p><strong>💡 Pour exercer vos droits :</strong><br>
                    Utilisez notre <a href="#contact-form" class="privacy-link">formulaire de contact sécurisé</a> en incluant :</p>
                    <ul style="margin-top: 1rem;">
                        <li>Votre nom et prénom</li>
                        <li>Votre adresse email utilisée lors de la soumission</li>
                        <li>L'objet de votre demande</li>
                        <li>Une copie d'une pièce d'identité (pour vérification)</li>
                    </ul>
                    <p style="margin-top: 1rem;"><strong>Délai de réponse :</strong> Maximum 1 mois</p>
                </div>
            </div>

            <div class="privacy-section">
                <h2>📮 11. Droit de réclamation</h2>
                <p>
                    Si vous estimez que vos droits ne sont pas respectés, vous pouvez introduire une réclamation auprès de la 
                    <strong>CNIL (Commission Nationale de l'Informatique et des Libertés)</strong> :
                </p>
                <div class="highlight-box">
                    <p><strong>CNIL</strong><br>
                    3 Place de Fontenoy<br>
                    TSA 80715<br>
                    75334 PARIS CEDEX 07</p>
                    <p>Site web : <a href="https://www.cnil.fr" target="_blank" style="color: var(--coral); text-decoration: underline;">www.cnil.fr</a></p>
                </div>
            </div>

            <div class="privacy-section">
                <h2>🔄 12. Modifications de la politique</h2>
                <p>
                    Nous nous réservons le droit de modifier cette politique de confidentialité à tout moment. 
                    Toute modification sera publiée sur cette page avec une nouvelle date de mise à jour.
                </p>
            </div>

            <div class="contact-info" id="contact-form">
                <h2 style="color: var(--dark-blue);">📧 13. Contact</h2>
                <p style="color: #333;">Pour toute question concernant cette politique de confidentialité ou le traitement de vos données, utilisez notre formulaire de contact sécurisé ci-dessous :</p>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div style="background: #10b981; color: white; padding: 1rem; border-radius: 10px; margin: 1rem 0;">
                        ✅ <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div style="background: #ef4444; color: white; padding: 1rem; border-radius: 10px; margin: 1rem 0;">
                        ❌ <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                
                <form action="contact-gdpr.php" method="POST" id="contactForm" style="margin-top: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="contact-nom" style="display: block; font-weight: 600; color: var(--dark-blue); margin-bottom: 0.5rem;">Nom *</label>
                        <input type="text" id="contact-nom" name="nom" required style="width: 100%; padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 10px;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="contact-email" style="display: block; font-weight: 600; color: var(--dark-blue); margin-bottom: 0.5rem;">Email *</label>
                        <input type="email" id="contact-email" name="email" required style="width: 100%; padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 10px;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="contact-sujet" style="display: block; font-weight: 600; color: var(--dark-blue); margin-bottom: 0.5rem;">Objet *</label>
                        <select id="contact-sujet" name="sujet" required style="width: 100%; padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 10px;">
                            <option value="">Sélectionnez...</option>
                            <option value="Droit d'accès">Exercer mon droit d'accès</option>
                            <option value="Droit de rectification">Exercer mon droit de rectification</option>
                            <option value="Droit à l'effacement">Exercer mon droit à l'effacement</option>
                            <option value="Droit à la portabilité">Exercer mon droit à la portabilité</option>
                            <option value="Droit d'opposition">Exercer mon droit d'opposition</option>
                            <option value="Question RGPD">Question sur le traitement de mes données</option>
                            <option value="Autre">Autre question</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="contact-message" style="display: block; font-weight: 600; color: var(--dark-blue); margin-bottom: 0.5rem;">Message *</label>
                        <textarea id="contact-message" name="message" rows="5" required style="width: 100%; padding: 0.8rem; border: 2px solid #e0e0e0; border-radius: 10px; resize: vertical;"></textarea>
                    </div>
                    
                    <button type="submit" style="background: var(--coral); color: white; padding: 1rem 2rem; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; transition: all 0.3s ease;">
                        📤 Envoyer
                    </button>
                </form>
                
                <p style="margin-top: 1.5rem; color: #666;"><strong style="color: var(--dark-blue);">Délai de réponse :</strong> Maximum 1 mois (conformément au RGPD)</p>
            </div>
        </div>
    </div>
    
    <script>
        // Validation du formulaire de contact avec reCAPTCHA v3
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Envoi en cours...';
            
            // Exécuter reCAPTCHA v3
            grecaptcha.ready(() => {
                grecaptcha.execute('6LeOrNorAAAAAGfkiHS2IqTbd5QbQHvinxR_4oek', {action: 'contact_form'}).then((token) => {
                    // Ajouter le token au formulaire
                    const recaptchaInput = document.createElement('input');
                    recaptchaInput.type = 'hidden';
                    recaptchaInput.name = 'recaptcha_token';
                    recaptchaInput.value = token;
                    document.getElementById('contactForm').appendChild(recaptchaInput);
                    
                    // Soumettre le formulaire
                    document.getElementById('contactForm').submit();
                }).catch((error) => {
                    console.error('Erreur reCAPTCHA:', error);
                    alert('Erreur de vérification. Veuillez réessayer.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            });
        });
    </script>
</body>
</html>

