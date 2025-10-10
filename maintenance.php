<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site en maintenance - Osons Saint-Paul</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #004a6d 0%, #0e7fad 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            color: white;
        }
        .maintenance-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }
        .maintenance-logo {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 20px;
            margin: 0 auto 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .maintenance-logo img {
            width: 80px;
            height: auto;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        .subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .message {
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }
        .admin-link {
            display: inline-block;
            background: #ec654f;
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        .admin-link:hover {
            background: #d55a47;
            transform: translateY(-2px);
        }
        .info {
            font-size: 0.9rem;
            opacity: 0.8;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-logo">
            <img src="Ofeuille.png" alt="Logo Osons Saint-Paul" onerror="this.style.display='none'">
        </div>
        
        <h1>Site en maintenance</h1>
        <p class="subtitle">Osons Saint-Paul 2026</p>
        
        <div class="message">
            <p>Notre site est temporairement en maintenance pour améliorer votre expérience.</p>
            <p>Nous travaillons activement pour vous offrir un site encore plus performant.</p>
        </div>
        
        <a href="/admin/login.php" class="admin-link">
            Accès Administration
        </a>
        
        <div class="info">
            <p>Pour toute question urgente : <strong>bonjour@osons-saint-paul.fr</strong></p>
            <p>Merci de votre patience !</p>
        </div>
    </div>
</body>
</html>
