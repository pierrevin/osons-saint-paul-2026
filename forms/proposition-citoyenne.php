<?php
// Configuration de base
session_start();

// Fonction pour générer un token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Chargement du contenu du site
$data_path = defined('ROOT_PATH') ? ROOT_PATH . '/data/site_content.json' : '../data/site_content.json';
$content = file_exists($data_path) ? json_decode(file_get_contents($data_path), true) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposition Citoyenne - Osons Saint-Paul 2026</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&family=Lato:wght@300;400;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Styles spécifiques au formulaire */
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .form-header h1 {
            font-family: var(--font-script);
            font-size: 2.5rem;
            color: var(--coral);
            margin-bottom: 1rem;
        }

        .form-header p {
            font-size: 1.1rem;
            color: var(--dark-blue);
            line-height: 1.6;
        }

        .form-section {
            margin-bottom: 2.5rem;
            padding: 1.5rem;
            background: var(--cream);
            border-radius: 15px;
            border-left: 4px solid var(--coral);
        }

        .form-section h3 {
            font-family: var(--font-script);
            font-size: 1.4rem;
            color: var(--deep-green);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark-blue);
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--coral);
            box-shadow: 0 0 0 3px rgba(236, 101, 79, 0.1);
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .checkbox-item {
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
        }

        .checkbox-item input[type="checkbox"] {
            width: auto;
            margin-top: 0.2rem;
        }

        .checkbox-item label {
            margin-bottom: 0;
            font-weight: normal;
            cursor: pointer;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .category-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .category-item:hover {
            border-color: var(--coral);
            background: rgba(236, 101, 79, 0.05);
        }

        .category-item input[type="checkbox"] {
            width: auto;
        }

        .category-item input[type="checkbox"]:checked + label {
            font-weight: 600;
            color: var(--coral);
        }

        .category-item:has(input[type="checkbox"]:checked) {
            border-color: var(--coral);
            background: rgba(236, 101, 79, 0.1);
        }

        .submit-section {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--cream);
        }

        .btn-submit {
            background: var(--coral);
            color: white;
            padding: 1rem 3rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(236, 101, 79, 0.3);
        }

        .btn-submit:hover {
            background: var(--deep-green);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(236, 101, 79, 0.4);
        }

        .required {
            color: var(--coral);
        }

        .char-counter {
            text-align: right;
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }

        .char-counter.warning {
            color: var(--coral);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--coral);
            text-decoration: none;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .back-link:hover {
            color: var(--deep-green);
        }

        @media (max-width: 768px) {
            .form-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .categories-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="back-link">
            ← Retour au site
        </a>

        <div class="form-container">
            <div class="form-header">
                <h1>💡 Votre Proposition Citoyenne</h1>
                <p>Partagez vos idées pour améliorer Saint-Paul ! Vos propositions seront étudiées par l'équipe et pourront être intégrées à notre programme.</p>
            </div>

            <form action="process-form.php" method="POST" id="propositionForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                <!-- Section 1: Informations personnelles -->
                <div class="form-section">
                    <h3>👤 Informations personnelles (optionnelles)</h3>
                    
                    <div class="form-group">
                        <label for="nom">Nom et prénom</label>
                        <input type="text" id="nom" name="nom" placeholder="Votre nom (optionnel)">
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required placeholder="votre@email.com">
                        <small>Pour vous tenir informé de l'avancement de votre proposition</small>
                    </div>

                    <div class="form-group">
                        <label for="commune">Commune de résidence</label>
                        <input type="text" id="commune" name="commune" placeholder="Saint-Paul (optionnel)">
                    </div>

                    <div class="form-group">
                        <label for="telephone">Numéro de téléphone</label>
                        <input type="tel" id="telephone" name="telephone" placeholder="06 12 34 56 78 (optionnel)">
                    </div>
                </div>

                <!-- Section 2: Proposition principale -->
                <div class="form-section">
                    <h3>💡 Votre proposition</h3>
                    
                    <div class="form-group">
                        <label for="titre">Titre de votre proposition <span class="required">*</span></label>
                        <input type="text" id="titre" name="titre" required maxlength="100" placeholder="Ex: Créer un jardin partagé au centre-ville">
                    </div>

                    <div class="form-group">
                        <label for="description">Description détaillée <span class="required">*</span></label>
                        <textarea id="description" name="description" required maxlength="500" placeholder="Décrivez votre proposition en détail..."></textarea>
                        <div class="char-counter" id="descCounter">0/500 caractères</div>
                    </div>

                    <div class="form-group">
                        <label>Catégorie(s) concernée(s) <span class="required">*</span></label>
                        <div class="categories-grid">
                            <div class="category-item">
                                <input type="checkbox" id="cat_urbanisme" name="categories[]" value="Urbanisme & Logement">
                                <label for="cat_urbanisme">🏠 Urbanisme & Logement</label>
                            </div>
                            <div class="category-item">
                                <input type="checkbox" id="cat_environnement" name="categories[]" value="Environnement & Nature">
                                <label for="cat_environnement">🌱 Environnement & Nature</label>
                            </div>
                            <div class="category-item">
                                <input type="checkbox" id="cat_mobilite" name="categories[]" value="Mobilité & Transport">
                                <label for="cat_mobilite">🚗 Mobilité & Transport</label>
                            </div>
                            <div class="category-item">
                                <input type="checkbox" id="cat_social" name="categories[]" value="Vie sociale & Solidarité">
                                <label for="cat_social">👥 Vie sociale & Solidarité</label>
                            </div>
                            <div class="category-item">
                                <input type="checkbox" id="cat_education" name="categories[]" value="Éducation & Jeunesse">
                                <label for="cat_education">🎓 Éducation & Jeunesse</label>
                            </div>
                            <div class="category-item">
                                <input type="checkbox" id="cat_sante" name="categories[]" value="Santé & Bien-être">
                                <label for="cat_sante">🏥 Santé & Bien-être</label>
                            </div>
                            <div class="category-item">
                                <input type="checkbox" id="cat_culture" name="categories[]" value="Culture & Sport">
                                <label for="cat_culture">🎭 Culture & Sport</label>
                            </div>
                            <div class="category-item">
                                <input type="checkbox" id="cat_economie" name="categories[]" value="Économie & Commerce">
                                <label for="cat_economie">💼 Économie & Commerce</label>
                            </div>
                            <div class="category-item">
                                <input type="checkbox" id="cat_services" name="categories[]" value="Services publics">
                                <label for="cat_services">🔧 Services publics</label>
                            </div>
                            <div class="category-item">
                                <input type="checkbox" id="cat_autre" name="categories[]" value="Autre">
                                <label for="cat_autre">📝 Autre</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Impact & faisabilité -->
                <div class="form-section">
                    <h3>🎯 Impact & faisabilité</h3>
                    
                    <div class="form-group">
                        <label for="beneficiaires">Qui bénéficierait de cette proposition ? <span class="required">*</span></label>
                        <textarea id="beneficiaires" name="beneficiaires" required placeholder="Ex: Tous les habitants du centre-ville, les familles avec enfants..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="cout">Estimation du coût</label>
                        <select id="cout" name="cout">
                            <option value="">Sélectionner...</option>
                            <option value="Faible (< 10k€)">💰 Faible (< 10k€)</option>
                            <option value="Modéré (10k€ - 50k€)">💰💰 Modéré (10k€ - 50k€)</option>
                            <option value="Élevé (> 50k€)">💰💰💰 Élevé (> 50k€)</option>
                            <option value="Difficile à estimer">🤷 Difficile à estimer</option>
                        </select>
                    </div>
                </div>

                <!-- Section 4: Engagement citoyen -->
                <div class="form-section">
                    <h3>🤝 Engagement citoyen</h3>
                    
                    <div class="form-group">
                        <label>Seriez-vous prêt à participer à la mise en œuvre ?</label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="radio" id="engagement_oui" name="engagement" value="oui">
                                <label for="engagement_oui">Oui, je souhaite m'impliquer</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="radio" id="engagement_non" name="engagement" value="non">
                                <label for="engagement_non">Non, je préfère juste proposer</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="engagement_details">Si oui, comment souhaitez-vous participer ?</label>
                        <textarea id="engagement_details" name="engagement_details" placeholder="Ex: Participer aux réunions, apporter mes compétences techniques, aider à l'organisation..."></textarea>
                    </div>
                </div>

                <!-- Section 5: Validation -->
                <div class="form-section">
                    <h3>✅ Validation</h3>
                    
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="acceptation_publication" name="acceptation_publication" required>
                            <label for="acceptation_publication">J'accepte que ma proposition soit publiée sur le site et étudiée par l'équipe <span class="required">*</span></label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="acceptation_rgpd" name="acceptation_rgpd" required>
                            <label for="acceptation_rgpd">J'accepte le traitement de mes données personnelles conformément à la politique de confidentialité <span class="required">*</span></label>
                        </div>
                    </div>
                </div>

                <div class="submit-section">
                    <button type="submit" class="btn-submit">
                        🚀 Envoyer ma proposition
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Compteur de caractères
        const descriptionTextarea = document.getElementById('description');
        const descCounter = document.getElementById('descCounter');
        
        descriptionTextarea.addEventListener('input', function() {
            const length = this.value.length;
            descCounter.textContent = `${length}/500 caractères`;
            
            if (length > 450) {
                descCounter.classList.add('warning');
            } else {
                descCounter.classList.remove('warning');
            }
        });

        // Validation du formulaire
        document.getElementById('propositionForm').addEventListener('submit', function(e) {
            const categories = document.querySelectorAll('input[name="categories[]"]:checked');
            const acceptationPublication = document.getElementById('acceptation_publication').checked;
            const acceptationRgpd = document.getElementById('acceptation_rgpd').checked;
            
            if (categories.length === 0) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins une catégorie.');
                return;
            }
            
            if (!acceptationPublication || !acceptationRgpd) {
                e.preventDefault();
                alert('Veuillez accepter les conditions pour pouvoir envoyer votre proposition.');
                return;
            }
        });

        // Animation des catégories
        document.querySelectorAll('.category-item').forEach(item => {
            item.addEventListener('click', function() {
                const checkbox = this.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
            });
        });
    </script>
</body>
</html>
