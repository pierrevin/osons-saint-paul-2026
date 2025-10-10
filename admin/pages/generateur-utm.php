<?php
session_start();
require_once __DIR__ . '/../config.php';

// Vérifier l'authentification
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Charger le contenu du site pour les sections
$data_path = DATA_PATH . '/site_content.json';
$content = file_exists($data_path) ? json_decode(file_get_contents($data_path), true) : [];

// Charger les sections si elles ne sont pas déjà chargées
if (!isset($sections)) {
    $sections = [];
    
    // Charger les sections si les classes sont disponibles
    if (class_exists('DashboardSection')) {
        $sections[] = new DashboardSection($content);
    }
    if (class_exists('HeroSection')) {
        $sections[] = new HeroSection($content);
    }
    if (class_exists('ProgrammeSection')) {
        $programme_count = count($content['programme']['proposals'] ?? []);
        $sections[] = new ProgrammeSection($content, $programme_count);
    }
    if (class_exists('CitationsSection')) {
        $sections[] = new CitationsSection($content);
    }
    if (class_exists('EquipeSection')) {
        $sections[] = new EquipeSection($content);
    }
    if (class_exists('RendezVousSection')) {
        $sections[] = new RendezVousSection($content);
    }
    if (class_exists('CharteSection')) {
        $sections[] = new CharteSection($content);
    }
    if (class_exists('ContactSection')) {
        $sections[] = new ContactSection($content);
    }
    if (class_exists('MediathequeSection')) {
        $sections[] = new MediathequeSection($content);
    }
    if (class_exists('GestionUtilisateursSection')) {
        $sections[] = new GestionUtilisateursSection($content);
    }
    if (class_exists('LogsSecuriteSection')) {
        $sections[] = new LogsSecuriteSection($content);
    }
}

// Pages disponibles
$pages = [
    '/' => 'Page d\'accueil',
    '/#programme' => 'Section Programme',
    '/#equipe' => 'Section Équipe',
    '/#rendez-vous' => 'Section Rendez-vous',
    '/#charte' => 'Section Charte',
    '/#idees' => 'Section Contact',
    '/forms/proposition-citoyenne.php' => 'Formulaire Proposition',
    '/mentions-legales.php' => 'Mentions légales',
    '/forms/politique-confidentialite.php' => 'Politique de confidentialité',
    '/gestion-cookies.php' => 'Gestion des cookies',
];

// Sources prédéfinies
$sources = [
    'qrcode' => 'QR Code',
    'email' => 'Email / Newsletter',
    'facebook' => 'Facebook',
    'instagram' => 'Instagram',
    'linkedin' => 'LinkedIn',
    'twitter' => 'Twitter (X)',
    'direct' => 'Lien direct',
    'autre' => 'Autre (personnalisé)',
];

// Médiums prédéfinis
$mediums = [
    'print' => 'Support imprimé (cartes, affiches, flyers)',
    'social' => 'Réseaux sociaux',
    'email' => 'Email',
    'referral' => 'Site référent',
    'autre' => 'Autre (personnalisé)',
];

// Campagnes suggérées
$campagnes_suggerees = [
    'cartes_postales_2026' => 'Cartes postales',
    'affiches_publiques_2026' => 'Affiches publiques',
    'flyers_programme_2026' => 'Flyers programme',
    'badges_equipe_2026' => 'Badges équipe',
    'newsletter_janvier_2026' => 'Newsletter janvier',
    'facebook_ads_2026' => 'Publicités Facebook',
    'instagram_stories_2026' => 'Stories Instagram',
];

$base_url = 'https://osons-saint-paul.fr';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur d'URLs UTM | Admin Osons Saint-Paul</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .utm-generator {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .utm-form {
            display: grid;
            gap: 1.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-group label {
            font-weight: 600;
            color: #004a6d;
            font-size: 0.95rem;
        }
        
        .form-group label .required {
            color: #ec654f;
        }
        
        .form-group label .help-text {
            display: block;
            font-weight: 400;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        
        .form-group select,
        .form-group input {
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #ec654f;
        }
        
        .url-preview {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #65ae99;
            margin-top: 2rem;
        }
        
        .url-preview h3 {
            margin: 0 0 1rem 0;
            color: #004a6d;
            font-size: 1.1rem;
        }
        
        .url-display {
            background: white;
            padding: 1rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            word-break: break-all;
            color: #0066cc;
            border: 1px solid #dee2e6;
        }
        
        .url-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background: #ec654f;
            color: white;
        }
        
        .btn-primary:hover {
            background: #d94d36;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #65ae99;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #529582;
        }
        
        .btn-outline {
            background: white;
            color: #004a6d;
            border: 2px solid #004a6d;
        }
        
        .btn-outline:hover {
            background: #004a6d;
            color: white;
        }
        
        .qr-code-section {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #fff3cd;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }
        
        .qr-code-section h3 {
            margin: 0 0 1rem 0;
            color: #856404;
        }
        
        #qrcode {
            margin-top: 1rem;
            display: flex;
            justify-content: center;
        }
        
        .info-box {
            background: #e7f3ff;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #0066cc;
        }
        
        .info-box p {
            margin: 0;
            color: #004085;
        }
        
        .examples {
            margin-top: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .examples h3 {
            margin: 0 0 1rem 0;
            color: #004a6d;
        }
        
        .example-item {
            padding: 0.75rem;
            background: white;
            border-radius: 6px;
            margin-bottom: 0.75rem;
            border-left: 3px solid #65ae99;
        }
        
        .example-item strong {
            color: #004a6d;
        }
        
        .example-item code {
            display: block;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #f1f3f5;
            border-radius: 4px;
            font-size: 0.85rem;
            color: #0066cc;
            word-break: break-all;
        }
        
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #28a745;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: none;
            align-items: center;
            gap: 0.75rem;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .toast.show {
            display: flex;
        }
    </style>
</head>
<body class="admin-body">
    
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <main class="admin-main">
        <div class="admin-header">
            <h1><i class="fas fa-link"></i> Générateur d'URLs UTM</h1>
            <p class="admin-subtitle">Créez des URLs trackées pour mesurer vos campagnes</p>
        </div>
        
        <div class="utm-generator">
            <div class="info-box">
                <p><strong>💡 Qu'est-ce qu'une URL UTM ?</strong><br>
                Les paramètres UTM permettent de tracker précisément d'où viennent vos visiteurs dans Google Analytics. 
                Idéal pour mesurer l'efficacité de vos QR codes, affiches, posts sur les réseaux sociaux, etc.</p>
            </div>
            
            <form class="utm-form" id="utmForm">
                <div class="form-group">
                    <label for="page">
                        Page de destination <span class="required">*</span>
                        <span class="help-text">Vers quelle page diriger les visiteurs ?</span>
                    </label>
                    <select id="page" name="page" required>
                        <?php foreach ($pages as $url => $nom): ?>
                        <option value="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($nom) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="source">
                        Source (utm_source) <span class="required">*</span>
                        <span class="help-text">D'où vient le trafic ? Ex: qrcode, facebook, email</span>
                    </label>
                    <select id="source" name="source" required>
                        <option value="">-- Choisir une source --</option>
                        <?php foreach ($sources as $value => $nom): ?>
                        <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($nom) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="source_custom" name="source_custom" placeholder="Source personnalisée" style="display:none;">
                </div>
                
                <div class="form-group">
                    <label for="medium">
                        Médium (utm_medium) <span class="required">*</span>
                        <span class="help-text">Quel type de support ? Ex: print, social, email</span>
                    </label>
                    <select id="medium" name="medium" required>
                        <option value="">-- Choisir un médium --</option>
                        <?php foreach ($mediums as $value => $nom): ?>
                        <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($nom) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="medium_custom" name="medium_custom" placeholder="Médium personnalisé" style="display:none;">
                </div>
                
                <div class="form-group">
                    <label for="campaign">
                        Campagne (utm_campaign) <span class="required">*</span>
                        <span class="help-text">Nom de votre campagne. Ex: cartes_postales_2026</span>
                    </label>
                    <select id="campaign" name="campaign" required>
                        <option value="">-- Choisir une campagne --</option>
                        <?php foreach ($campagnes_suggerees as $value => $nom): ?>
                        <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($nom) ?></option>
                        <?php endforeach; ?>
                        <option value="autre">Autre (personnalisé)</option>
                    </select>
                    <input type="text" id="campaign_custom" name="campaign_custom" placeholder="Nom de campagne personnalisé (ex: marche_noel_2026)" style="display:none;">
                </div>
                
                <div class="form-group">
                    <label for="content">
                        Contenu (utm_content) <span style="color: #6c757d;">(optionnel)</span>
                        <span class="help-text">Pour différencier des variantes. Ex: version_a, qrcode_recto</span>
                    </label>
                    <input type="text" id="content" name="content" placeholder="Ex: version_a, bouton_bleu, qrcode_recto">
                </div>
                
                <div class="form-group">
                    <label for="term">
                        Terme (utm_term) <span style="color: #6c757d;">(optionnel)</span>
                        <span class="help-text">Pour les campagnes payantes avec mots-clés</span>
                    </label>
                    <input type="text" id="term" name="term" placeholder="Ex: municipales, liste citoyenne">
                </div>
                
                <button type="button" class="btn btn-primary" onclick="generateURL()">
                    <i class="fas fa-magic"></i>
                    Générer l'URL
                </button>
            </form>
            
            <div class="url-preview" id="urlPreview" style="display:none;">
                <h3><i class="fas fa-link"></i> URL générée</h3>
                <div class="url-display" id="generatedURL"></div>
                <div class="url-actions">
                    <button class="btn btn-secondary" onclick="copyURL()">
                        <i class="fas fa-copy"></i>
                        Copier l'URL
                    </button>
                    <button class="btn btn-outline" onclick="openURL()">
                        <i class="fas fa-external-link-alt"></i>
                        Ouvrir dans un nouvel onglet
                    </button>
                    <button class="btn btn-outline" onclick="generateQRCode()">
                        <i class="fas fa-qrcode"></i>
                        Générer le QR Code
                    </button>
                </div>
            </div>
            
            <div class="qr-code-section" id="qrcodeSection" style="display:none;">
                <h3><i class="fas fa-qrcode"></i> QR Code généré</h3>
                <p>Faites un clic droit → "Enregistrer l'image sous..." pour télécharger le QR code.</p>
                <div id="qrcode"></div>
                <div style="margin-top: 1rem;">
                    <button class="btn btn-secondary" onclick="downloadQRCode()">
                        <i class="fas fa-download"></i>
                        Télécharger le QR Code
                    </button>
                </div>
            </div>
            
            <div class="examples">
                <h3><i class="fas fa-lightbulb"></i> Exemples d'utilisation</h3>
                
                <div class="example-item">
                    <strong>🎫 Cartes postales distribuées</strong>
                    <code>https://osons-saint-paul.fr/forms/proposition-citoyenne.php?utm_source=qrcode&utm_medium=print&utm_campaign=cartes_postales_2026</code>
                </div>
                
                <div class="example-item">
                    <strong>📰 Affiche publique place du village</strong>
                    <code>https://osons-saint-paul.fr/?utm_source=qrcode&utm_medium=print&utm_campaign=affiches_publiques_2026&utm_content=place_village</code>
                </div>
                
                <div class="example-item">
                    <strong>📧 Newsletter de janvier</strong>
                    <code>https://osons-saint-paul.fr/#programme?utm_source=email&utm_medium=email&utm_campaign=newsletter_janvier_2026</code>
                </div>
                
                <div class="example-item">
                    <strong>📱 Post Facebook sur le programme</strong>
                    <code>https://osons-saint-paul.fr/#programme?utm_source=facebook&utm_medium=social&utm_campaign=facebook_programme_2026</code>
                </div>
            </div>
        </div>
    </main>
    
    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMessage">URL copiée dans le presse-papier !</span>
    </div>
    
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <script>
        let generatedURLValue = '';
        let qrCodeInstance = null;
        
        // Gérer les champs personnalisés
        document.getElementById('source').addEventListener('change', function() {
            const customField = document.getElementById('source_custom');
            customField.style.display = this.value === 'autre' ? 'block' : 'none';
            if (this.value !== 'autre') customField.value = '';
        });
        
        document.getElementById('medium').addEventListener('change', function() {
            const customField = document.getElementById('medium_custom');
            customField.style.display = this.value === 'autre' ? 'block' : 'none';
            if (this.value !== 'autre') customField.value = '';
        });
        
        document.getElementById('campaign').addEventListener('change', function() {
            const customField = document.getElementById('campaign_custom');
            customField.style.display = this.value === 'autre' ? 'block' : 'none';
            if (this.value !== 'autre') customField.value = '';
        });
        
        function generateURL() {
            const page = document.getElementById('page').value;
            let source = document.getElementById('source').value;
            let medium = document.getElementById('medium').value;
            let campaign = document.getElementById('campaign').value;
            const content = document.getElementById('content').value;
            const term = document.getElementById('term').value;
            
            // Récupérer les valeurs personnalisées si nécessaire
            if (source === 'autre') {
                source = document.getElementById('source_custom').value;
            }
            if (medium === 'autre') {
                medium = document.getElementById('medium_custom').value;
            }
            if (campaign === 'autre') {
                campaign = document.getElementById('campaign_custom').value;
            }
            
            // Validation
            if (!page || !source || !medium || !campaign) {
                showToast('Veuillez remplir tous les champs obligatoires', 'error');
                return;
            }
            
            // Construction de l'URL
            const baseURL = '<?= $base_url ?>' + page;
            const params = new URLSearchParams();
            params.append('utm_source', source);
            params.append('utm_medium', medium);
            params.append('utm_campaign', campaign);
            if (content) params.append('utm_content', content);
            if (term) params.append('utm_term', term);
            
            generatedURLValue = baseURL + '?' + params.toString();
            
            // Afficher l'URL
            document.getElementById('generatedURL').textContent = generatedURLValue;
            document.getElementById('urlPreview').style.display = 'block';
            
            // Réinitialiser le QR code
            document.getElementById('qrcodeSection').style.display = 'none';
            
            // Scroll vers le résultat
            document.getElementById('urlPreview').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        function copyURL() {
            navigator.clipboard.writeText(generatedURLValue).then(function() {
                showToast('URL copiée dans le presse-papier !', 'success');
            }, function() {
                showToast('Erreur lors de la copie', 'error');
            });
        }
        
        function openURL() {
            window.open(generatedURLValue, '_blank');
        }
        
        function generateQRCode() {
            const qrcodeDiv = document.getElementById('qrcode');
            qrcodeDiv.innerHTML = ''; // Clear previous QR code
            
            qrCodeInstance = new QRCode(qrcodeDiv, {
                text: generatedURLValue,
                width: 256,
                height: 256,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
            
            document.getElementById('qrcodeSection').style.display = 'block';
            document.getElementById('qrcodeSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        function downloadQRCode() {
            const canvas = document.querySelector('#qrcode canvas');
            if (canvas) {
                const url = canvas.toDataURL('image/png');
                const link = document.createElement('a');
                link.download = 'qrcode-osons-saint-paul.png';
                link.href = url;
                link.click();
                showToast('QR Code téléchargé !', 'success');
            }
        }
        
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            toastMessage.textContent = message;
            
            if (type === 'error') {
                toast.style.background = '#dc3545';
            } else {
                toast.style.background = '#28a745';
            }
            
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>

