<?php
// Chargement du contenu du site
$content = json_decode(file_get_contents('../data/site_content.json'), true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Osons Saint-Paul 2026</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;600;700&family=Lato:wght@300;400;600;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 3rem;
            text-align: center;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .confirmation-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .confirmation-title {
            font-family: var(--font-script);
            font-size: 2.5rem;
            color: var(--deep-green);
            margin-bottom: 1rem;
        }

        .confirmation-message {
            font-size: 1.2rem;
            color: var(--dark-blue);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .proposition-id {
            background: var(--cream);
            padding: 1rem;
            border-radius: 10px;
            margin: 2rem 0;
            font-family: monospace;
            font-weight: bold;
            color: var(--coral);
        }

        .next-steps {
            background: rgba(47, 110, 79, 0.1);
            padding: 2rem;
            border-radius: 15px;
            margin: 2rem 0;
            text-align: left;
        }

        .next-steps h3 {
            font-family: var(--font-script);
            font-size: 1.4rem;
            color: var(--deep-green);
            margin-bottom: 1rem;
        }

        .next-steps ul {
            list-style: none;
            padding: 0;
        }

        .next-steps li {
            padding: 0.5rem 0;
            position: relative;
            padding-left: 2rem;
        }

        .next-steps li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--coral);
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--coral);
            color: white;
        }

        .btn-primary:hover {
            background: var(--deep-green);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: transparent;
            color: var(--coral);
            border: 2px solid var(--coral);
        }

        .btn-secondary:hover {
            background: var(--coral);
            color: white;
        }

        @media (max-width: 768px) {
            .confirmation-container {
                margin: 2rem 1rem;
                padding: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-container">
            <div class="confirmation-icon">🎉</div>
            
            <h1 class="confirmation-title">Proposition envoyée !</h1>
            
            <p class="confirmation-message">
                Merci pour votre engagement citoyen ! Votre proposition a été transmise à notre équipe et sera étudiée dans les plus brefs délais.
            </p>

            <?php if (isset($_GET['id'])): ?>
            <div class="proposition-id">
                ID de suivi : <?= htmlspecialchars($_GET['id']) ?>
            </div>
            <?php endif; ?>

            <div class="next-steps">
                <h3>📋 Prochaines étapes</h3>
                <ul>
                    <li>Votre proposition sera examinée par notre équipe</li>
                    <li>Vous recevrez un email de confirmation</li>
                    <li>Nous vous tiendrons informé de l'avancement</li>
                    <li>Si retenue, votre proposition sera intégrée au programme</li>
                </ul>
            </div>

            <div class="action-buttons">
                <a href="../index.php" class="btn btn-primary">
                    🏠 Retour au site
                </a>
                <a href="proposition-citoyenne.php" class="btn btn-secondary">
                    💡 Autre proposition
                </a>
            </div>
        </div>
    </div>
</body>
</html>
