<?php
// Chargement du contenu dynamique
$content = [];
if (file_exists('data/site_content.json')) {
    $content = json_decode(file_get_contents('data/site_content.json'), true);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription confirmée - Osons Saint-Paul 2026</title>
    <meta name="description" content="Merci pour votre inscription à notre newsletter. Vous recevrez nos prochaines actualités et rendez-vous.">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="Ofeuille.png" type="image/png">
</head>
<body>
    <!-- Header minimal -->
    <header class="header" id="headerSticky">
        <div class="container header-container">
            <div class="logo">
                <img src="Ofeuille.png" alt="Logo Osons Saint-Paul" class="logo-img">
                <span class="logo-text">Osons Saint-Paul</span>
            </div>
        </div>
    </header>

    <!-- Section confirmation -->
    <section class="confirmation-page" style="min-height: 80vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(47, 110, 79, 0.05), rgba(236, 101, 79, 0.05)); padding: 4rem 2rem;">
        <div class="container" style="max-width: 700px; text-align: center;">
            <!-- Icône succès -->
            <div style="width: 100px; height: 100px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 2rem; box-shadow: 0 10px 30px rgba(47, 110, 79, 0.3); animation: scaleIn 0.5s ease;">
                <i class="fas fa-check" style="font-size: 3rem; color: white;"></i>
            </div>

            <!-- Message principal -->
            <h1 style="font-family: var(--font-script); font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem; transform: rotate(-1deg);">
                Merci pour votre inscription !
            </h1>
            
            <p style="font-size: 1.2rem; color: var(--text-color); margin-bottom: 2rem; line-height: 1.6;">
                Votre inscription à notre newsletter est <strong>confirmée</strong>. 🎉<br>
                Vous recevrez nos prochaines actualités, invitations aux rendez-vous et propositions pour Saint-Paul.
            </p>

            <!-- Séparateur -->
            <div style="width: 80px; height: 3px; background: var(--secondary-color); margin: 2rem auto; border-radius: 3px;"></div>

            <!-- Réseaux sociaux -->
            <div style="margin: 2rem 0;">
                <h3 style="font-size: 1.3rem; color: var(--text-color); margin-bottom: 1rem;">
                    Suivez-nous sur les réseaux sociaux
                </h3>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <a href="#" class="social-link" style="width: 50px; height: 50px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; transition: all 0.3s ease; text-decoration: none;">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="#" class="social-link" style="width: 50px; height: 50px; background: var(--secondary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; transition: all 0.3s ease; text-decoration: none;">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 3rem;">
                <a href="/" class="btn btn-primary" style="text-decoration: none;">
                    <i class="fas fa-home"></i> Retour à l'accueil
                </a>
                <a href="/#programme" class="btn btn-secondary" style="text-decoration: none;">
                    <i class="fas fa-book-open"></i> Découvrir le programme
                </a>
                <a href="/proposez" class="btn btn-secondary" style="text-decoration: none;">
                    <i class="fas fa-lightbulb"></i> Proposer une idée
                </a>
            </div>

            <!-- Note -->
            <p style="margin-top: 3rem; font-size: 0.9rem; color: var(--text-color); opacity: 0.7;">
                Vous ne recevez pas nos emails ? Pensez à vérifier vos <strong>spams</strong>.<br>
                Pour toute question : <a href="mailto:bonjour@osons-saint-paul.fr" style="color: var(--secondary-color);">bonjour@osons-saint-paul.fr</a>
            </p>
        </div>
    </section>

    <!-- Animation CSS -->
    <style>
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .social-link:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
    </style>
</body>
</html>

