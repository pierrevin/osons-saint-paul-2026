<?php
// MAINTENANCE MODE - Décommentez la ligne suivante pour activer
session_start();

// Vérifier si l'utilisateur est connecté à l'admin
$user_connected = isset($_SESSION['user_id']) || isset($_SESSION['admin_logged_in']) || isset($_SESSION['authenticated']);

// Si l'utilisateur n'est pas connecté, afficher la maintenance
if (!$user_connected) {
    include __DIR__ . '/maintenance.php'; exit;
}

// Site public dynamique basé sur index.html
require_once __DIR__ . '/admin/config.php';

// Charger les données du site
$content = get_json_data('site_content.json');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= htmlspecialchars($content['hero']['title'] ?? 'Osons Saint-Paul 2026 - Construisons ensemble le village vivant et partagé') ?></title>
    <meta name="description" content="Liste citoyenne Osons Saint-Paul pour les élections municipales 2026. Un projet participatif pour construire ensemble le village de demain.">
    
    <!-- Polices Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Caveat:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS principal -->
    <link rel="stylesheet" href="styles.css">
    
    <!-- Google reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=6LeOrNorAAAAAGfkiHS2IqTbd5QbQHvinxR_4oek"></script>
</head>
<body>
    <!-- Header sticky -->
    <header class="header-sticky" id="headerSticky" role="banner">
        <div class="header-content">
            <?php
            $logoPng = 'uploads/Osons1.png';
            $logoWebp = 'uploads/Osons1.webp';
            $logoSrc = file_exists(__DIR__ . '/' . $logoWebp) ? $logoWebp : $logoPng;
            ?>
            <a href="#programme" class="header-logo" aria-label="Découvrir le programme">
                <img src="<?= htmlspecialchars($logoSrc) ?>" alt="Logo Osons Saint-Paul" class="logo-img">
            </a>
            <button id="mobileMenuToggle" class="mobile-menu-toggle" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mainNav">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="header-nav" id="mainNav" role="navigation" aria-label="Navigation principale">
                <ul class="nav-list">
                    <li><a href="#programme" class="nav-link">Programme</a></li>
                    <li><a href="#equipe" class="nav-link">Équipe</a></li>
                    <li><a href="#rendez-vous" class="nav-link">Rendez-vous</a></li>
                    <li><a href="#newsletter-cta" class="nav-link">Infolettre</a></li>
                    <li><a href="#charte" class="nav-link">Charte</a></li>
                    <li><a href="#idees" class="nav-link">Contact</a></li>
                    <li><a href="/proposez" class="nav-link nav-link-btn">Faire une proposition</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Section Hero - La Première Carte Postale -->
    <section class="hero" id="hero">
        <div class="hero-background">
            <picture>
                <?php
                $bg = $content['hero']['background_image'] ?? 'Images/hero_test.png';
                $bgMobile = $content['hero']['background_image_mobile'] ?? $bg;
                // Forcer un chemin absolu local si c'est un upload
                if (!preg_match('#^https?://#', $bg) && strpos($bg, '/') !== 0) { $bg = '/' . ltrim($bg, '/'); }
                if (!preg_match('#^https?://#', $bgMobile) && strpos($bgMobile, '/') !== 0) { $bgMobile = '/' . ltrim($bgMobile, '/'); }
                ?>
                <source media="(max-width: 768px)" srcset="<?= htmlspecialchars($bgMobile) ?>">
                <img src="<?= htmlspecialchars($bg) ?>" alt="Équipe Osons Saint-Paul - image hero" style="width:0;height:0;opacity:0;position:absolute;" onload="this.parentElement.parentElement.style.backgroundImage='url(' + this.src + ')'">
            </picture>
            <div class="hero-overlay"></div>
        </div>
        <!-- Logo scroll-down en tête du hero -->
        <div class="section-divider-logo" role="button" aria-label="Voir la suite" tabindex="0">
            <img src="/Ofeuille.png" alt="Osons Saint-Paul">
        </div>

        <div class="hero-content">
            <div class="hero-panel">
                <h1 class="hero-title"><?= htmlspecialchars($content['hero']['title'] ?? 'Construisons ensemble le village vivant et partagé') ?></h1>
                <div class="hero-buttons"></div>
                <div class="hero-buttons">
                    <a href="#programme" class="btn btn-primary"><?= htmlspecialchars($content['hero']['button_primary'] ?? 'Découvrir le programme') ?></a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Programme - Le présentoir à cartes postales -->
    <section class="programme" id="programme">
        <!-- Éléments flottants dynamiques -->
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        <div class="floating-element"></div>
        
        <!-- Bulles surprises -->
        <div class="surprise-bubble" style="top: 30%; left: 80%;"></div>
        <div class="surprise-bubble" style="bottom: 40%; left: 5%;"></div>
        
        <div class="container">
            <h2 class="section-title"><?= htmlspecialchars($content['programme']['h2'] ?? 'Notre Programme') ?></h2>
            <h3 class="section-subtitle"><?= htmlspecialchars($content['programme']['h3'] ?? 'Osons intégrer vos idées') ?></h3>
            
            <!-- Appel à l'action - Proposition -->
            <div class="cta-box cta-box--compact">
                <a href="/proposez" class="btn btn-primary">💡 Faire une proposition</a>
                <p class="text-description">
                    <strong>Ce programme évolutif</strong> s'enrichit au fil de vos <strong>propositions citoyennes</strong> et de nos actions validées collectivement.
                    Les cartes marquées du picto 
                    <span class="card-badge citoyenne badge-inline" title="Proposition citoyenne">
                        <i class="fas fa-lightbulb" aria-hidden="true"></i>
                    </span>
                    sont des <strong>propositions citoyennes</strong> (idées d'habitants) intégrées au programme après validation par l'équipe.
                </p>
            </div>
            
            <!-- Filtres -->
            <div class="filters">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-th"></i>
                    <span>Tout</span>
                </button>
                <button class="filter-btn" data-filter="proteger">
                    <i class="fas fa-leaf"></i>
                    <span>Protéger</span>
                </button>
                <button class="filter-btn" data-filter="tisser">
                    <i class="fas fa-users"></i>
                    <span>Tisser</span>
                </button>
                <button class="filter-btn" data-filter="dessiner">
                    <i class="fas fa-drafting-compass"></i>
                    <span>Dessiner</span>
                </button>
                <button class="filter-btn" data-filter="ouvrir">
                    <i class="fas fa-door-open"></i>
                    <span>Ouvrir</span>
                </button>
                <button class="filter-btn" data-filter="citoyens">
                    <i class="fas fa-lightbulb"></i>
                    <span>Propositions citoyennes</span>
                </button>
            </div>

            <!-- Grille de propositions -->
            <div class="propositions-grid">
                <?php foreach ($content['programme']['proposals'] ?? [] as $proposal): ?>
                <div class="proposition-card" data-category="<?= htmlspecialchars($proposal['pillar'] ?? 'proteger') ?>">
                    <div class="card-inner">
                        <div class="card-front <?= htmlspecialchars($proposal['pillar'] ?? 'proteger') ?>" style="background-color: <?= htmlspecialchars($proposal['color'] ?? '#65ae99') ?>">
                            <div class="card-image">
                                <?php if ($proposal['citizen_proposal'] ?? false): ?>
                                <div class="card-badge citoyenne" title="💡 Proposition citoyenne - Idée proposée par un habitant">
                                    <i class="fas fa-lightbulb"></i>
                                    <span class="badge-text">Citoyenne</span>
                                </div>
                                <?php endif; ?>
                                <div class="card-category <?= htmlspecialchars($proposal['pillar'] ?? 'proteger') ?>">
                                    <?php
                                    $pillar_names = [
                                        'proteger' => 'Osons protéger',
                                        'tisser' => 'Osons tisser des liens', 
                                        'dessiner' => 'Osons dessiner',
                                        'ouvrir' => 'Osons ouvrir'
                                    ];
                                    echo htmlspecialchars($pillar_names[$proposal['pillar'] ?? 'proteger'] ?? 'Osons protéger');
                                    ?>
                                </div>
                                <div class="card-title-overlay">
                                    <h3><?= htmlspecialchars($proposal['title']) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="card-back">
                            <div class="card-content">
                                <h3><?= htmlspecialchars($proposal['title']) ?></h3>
                                <p><?= htmlspecialchars($proposal['description']) ?></p>
                                <?php if (!empty($proposal['items'])): ?>
                                <ul>
                                    <?php foreach ($proposal['items'] as $item): ?>
                                    <li><?= htmlspecialchars($item) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
    </section>

    <!-- Citation de transition : Programme → Équipe -->
    <div class="transition-quote programme-equipe" style="background-image: url('<?= htmlspecialchars($content['citations']['citation1']['background_image'] ?? 'Images/hero_test.png') ?>');">
        <div class="transition-quote-content">
            <blockquote class="transition-quote-text">
                <span class="quote-text"><?= htmlspecialchars($content['citations']['citation1']['text'] ?? 'Considérer l\'être humain et la préservation de la nature comme composante centrale de l\'action publique') ?></span>
                <span class="quote-author"><?= htmlspecialchars($content['citations']['citation1']['author'] ?? 'Notre vision') ?></span>
            </blockquote>
        </div>
    </div>

    <!-- Section Notre Équipe -->
    <section class="equipe" id="equipe">
        <!-- Éléments flottants -->
        <div class="floating-element" style="animation-delay: 3s;"></div>
        <div class="floating-element" style="animation-delay: 8s;"></div>
        
        <!-- Bulles surprises -->
        <div class="surprise-bubble" style="top: 40%; right: 5%;"></div>
        <div class="surprise-bubble" style="bottom: 30%; left: 10%;"></div>
        
        <div class="container">
            <h2 class="section-title"><?= htmlspecialchars($content['equipe']['h2'] ?? 'Notre Équipe') ?></h2>
            <h3 class="section-subtitle"><?= htmlspecialchars($content['equipe']['h3'] ?? 'Osez nous aborder') ?></h3>
            
            <!-- Image Hero Mobile (visible uniquement sur mobile) -->
            <div class="hero-mobile-image">
                <img src="<?= htmlspecialchars($content['hero']['background_image'] ?? 'Images/hero_test.png') ?>" alt="Équipe Osons Saint-Paul - Photo de groupe" loading="lazy">
            </div>
            
            <div class="team-grid">
<?php foreach ($content['equipe']['members'] ?? [] as $member): ?>
                <div class="team-member">
                    <div class="member-photo">
                        <?php 
                        $image_url = $member['image'] ?? $member['photo'] ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face&auto=format&q=80';
                        $alt_text = htmlspecialchars($member['name'] ?? 'Membre de l\'équipe');
                        ?>
                        <img src="<?= htmlspecialchars($image_url) ?>" alt="<?= $alt_text ?>" loading="lazy" width="300" height="300">
                        <div class="member-overlay">
                            <h3 class="member-name"><?= htmlspecialchars($member['name'] ?? 'Nom non défini') ?></h3>
                            <p class="member-role"><?= htmlspecialchars($member['role'] ?? 'Rôle non défini') ?></p>
                            <p class="member-description"><?= htmlspecialchars($member['description'] ?? 'Description non définie') ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Appel à l'action -->
            <div class="cta-box">
                <h3 class="cta-box__title">Rencontrons-nous !</h3>
                <p class="text-description">Vous avez des questions ? Des idées à partager ? N'hésitez pas à nous contacter directement.</p>
                <div class="cta-box__buttons">
                        <a href="#idees" class="btn btn-primary">Nous contacter</a>
                        <a href="#rendez-vous" class="btn btn-secondary">Voir nos rendez-vous</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Citation de transition : Équipe → Rencontres -->
    <div class="transition-quote equipe-rencontres" style="background-image: url('<?= htmlspecialchars($content['citations']['citation2']['background_image'] ?? 'Images/hero_test.png') ?>');">
        <div class="transition-quote-content">
            <blockquote class="transition-quote-text">
                <span class="quote-text"><?= htmlspecialchars($content['citations']['citation2']['text'] ?? 'Favoriser, au sein du conseil municipal, le débat d\'idées dans le respect des points de vue') ?></span>
                <span class="quote-author"><?= htmlspecialchars($content['citations']['citation2']['author'] ?? 'Notre méthode de travail') ?></span>
            </blockquote>
        </div>
    </div>

    <!-- Section Construisons Ensemble - L'agenda des rencontres -->
    <section class="rencontres" id="rendez-vous">
        <div class="container">
            <h2 class="section-title"><?= htmlspecialchars($content['rendez_vous']['h2'] ?? 'Nos rendez-vous') ?></h2>
            <h3 class="section-subtitle"><?= htmlspecialchars($content['rendez_vous']['h3'] ?? 'Osons échanger') ?></h3>
            
            <div class="events-timeline">
                <?php
                $events = $content['rendez_vous']['events'] ?? [];
                // Normaliser dates: supporter ISO (2025-10-01T10:30) ou anciens champs jour/mois/heure
                $now = time();
                $normalized = [];
                foreach ($events as $ev) {
                    $ts = false;
                    if (!empty($ev['date']) && preg_match('/\d{4}-\d{2}-\d{2}/', (string)$ev['date'])) {
                        $ts = strtotime($ev['date']);
                    } elseif (!empty($ev['date']) && !empty($ev['month'])) {
                        // Ex: date=15, month=OCT, time=18h30
                        $day = (int)$ev['date'];
                        $monthStr = strtoupper((string)$ev['month']);
                        $monthMap = [
                            'JAN'=>1,'FEB'=>2,'FEV'=>2,'MAR'=>3,'APR'=>4,'AVR'=>4,'MAY'=>5,'MAI'=>5,'JUN'=>6,'JUI'=>7,'JUL'=>7,'AUG'=>8,'AOU'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DEC'=>12,'DEC.'=>12
                        ];
                        $monthNum = $monthMap[$monthStr] ?? null;
                        $year = (int)date('Y');
                        if ($monthNum) {
                            $timeStr = '00:00';
                            if (!empty($ev['time']) && preg_match('/(\d{1,2})h(\d{2})?/', (string)$ev['time'], $m)) {
                                $timeStr = sprintf('%02d:%02d', (int)$m[1], isset($m[2]) ? (int)$m[2] : 0);
                            }
                            $ts = strtotime(sprintf('%04d-%02d-%02d %s', $year, $monthNum, max(1,$day), $timeStr));
                        }
                    }
                    if ($ts !== false && $ts >= $now) {
                        $ev['_ts'] = $ts;
                        $normalized[] = $ev;
                    }
                }
                // Trier du plus proche au plus lointain
                usort($normalized, function($a,$b){ return ($a['_ts'] <=> $b['_ts']); });
                if (!empty($normalized)):
                    $first = array_slice($normalized, 0, 3);
                    $more = array_slice($normalized, 3);
                    foreach ($first as $event):
                        $ts = $event['_ts'];
                        $day = date('d', $ts);
                        $month = strtoupper(date('M', $ts));
                ?>
                <div class="event">
                    <div class="event-date">
                        <span class="day"><?= htmlspecialchars($day) ?></span>
                        <span class="month"><?= htmlspecialchars($month) ?></span>
                    </div>
                    <div class="event-content">
                        <h3 class="event-title"><?= htmlspecialchars($event['title'] ?? '') ?></h3>
                        <p class="event-description"><?= htmlspecialchars($event['description'] ?? '') ?></p>
                        <div class="event-details">
                            <span class="event-time"><i class="fas fa-clock"></i> <?= htmlspecialchars(date('d/m H\hi', $ts)) ?></span>
                            <?php if (!empty($event['location'])): ?>
                            <span class="event-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></span>
                            <?php endif; ?>
                            <button class="btn-add-calendar" onclick="addToCalendar(<?= htmlspecialchars(json_encode([
                                'title' => $event['title'] ?? '',
                                'description' => $event['description'] ?? '',
                                'location' => $event['location'] ?? '',
                                'start' => date('Y-m-d H:i:s', $ts),
                                'end' => date('Y-m-d H:i:s', $ts + 7200)
                            ], JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)) ?>)">
                                <i class="fas fa-calendar-plus"></i> Ajouter à mon agenda
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (!empty($more)): ?>
                <div id="more-events" style="display:none;">
                    <?php foreach ($more as $event): $ts = $event['_ts']; $day = date('d',$ts); $month = strtoupper(date('M',$ts)); ?>
                <div class="event">
                    <div class="event-date">
                            <span class="day"><?= htmlspecialchars($day) ?></span>
                            <span class="month"><?= htmlspecialchars($month) ?></span>
                    </div>
                    <div class="event-content">
                            <h3 class="event-title"><?= htmlspecialchars($event['title'] ?? '') ?></h3>
                            <p class="event-description"><?= htmlspecialchars($event['description'] ?? '') ?></p>
                        <div class="event-details">
                                <span class="event-time"><i class="fas fa-clock"></i> <?= htmlspecialchars(date('d/m H\hi', $ts)) ?></span>
                                <?php if (!empty($event['location'])): ?>
                                <span class="event-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></span>
                                <?php endif; ?>
                            </div>
                        <div class="event-actions">
                            <button class="btn-add-calendar" onclick="addToCalendar(<?= htmlspecialchars(json_encode([
                                'title' => $event['title'] ?? '',
                                'description' => $event['description'] ?? '',
                                'location' => $event['location'] ?? '',
                                'start' => date('Y-m-d H:i:s', $ts),
                                'end' => date('Y-m-d H:i:s', $ts + 7200)
                            ], JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS)) ?>)">
                                <i class="fas fa-calendar-plus"></i> Ajouter à mon agenda
                            </button>
                        </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="see-more-container" style="text-align:center; margin-top: 1rem;">
                    <button id="toggleMoreEvents" class="btn btn-secondary" type="button" onclick="(function(){var e=document.getElementById('more-events'); var b=document.getElementById('toggleMoreEvents'); if(e.style.display==='none'){e.style.display='block'; b.textContent='Voir moins';} else {e.style.display='none'; b.textContent='Voir plus';}})()">Voir plus</button>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <p>Aucun événement à venir.</p>
                <?php endif; ?>
            </div>
            
            <!-- Appel à l'action - Newsletter -->
            <div class="cta-box" id="newsletter-cta">
                <h3 class="cta-box__title">Restez informé !</h3>
                <p class="text-description">Recevez nos actualités et invitations aux événements directement dans votre boîte mail.</p>
                    <!-- Newsletter Form -->
                    <form class="newsletter-form" id="newsletter-section">
                        <div class="form-group">
                            <input type="text" name="PRENOM" placeholder="Votre prénom" required>
                            <input type="email" name="EMAIL" placeholder="Votre adresse email" required>
                        <input type="text" name="email_address_check" value="" class="hidden" tabindex="-1" autocomplete="off">
                            <button type="submit" class="btn btn-primary">S'inscrire</button>
                        </div>
                    <p class="text-note">Nous respectons votre vie privée. Désabonnement possible à tout moment.</p>
                    </form>
                <div id="newsletter-success-section" class="hidden text-center mt-1" style="color: var(--primary-color); font-weight: 500;">✓ Merci ! Un email de confirmation vient de vous être envoyé. Pensez à vérifier vos spams.</div>
                <div id="newsletter-error-section" class="hidden text-center mt-1" style="color: #dc3545;"></div>
            </div>
        </div>
        
    </section>

    <!-- Citation de transition : Rencontres → Charte -->
    <div class="transition-quote rencontres-charte" style="background-image: url('<?= htmlspecialchars($content['citations']['citation3']['background_image'] ?? 'Images/hero_test.png') ?>');">
        <div class="transition-quote-content">
            <blockquote class="transition-quote-text">
                <span class="quote-text"><?= htmlspecialchars($content['citations']['citation3']['text'] ?? 'Porter une politique en faveur des plus fragiles par des actions favorisant l\'inclusion et l\'autonomie') ?></span>
                <span class="quote-author"><?= htmlspecialchars($content['citations']['citation3']['author'] ?? 'Notre engagement social') ?></span>
            </blockquote>
        </div>
    </div>

    <!-- Section Notre Charte (dynamique) -->
    <section class="charte" id="charte">
        <div class="container">
            <h2 class="section-title"><?= htmlspecialchars($content['charte']['h2'] ?? 'Notre charte') ?></h2>
            <h3 class="section-subtitle"><?= htmlspecialchars($content['charte']['h3'] ?? 'Nos engagements') ?></h3>
            
            <div class="charte-intro">
                <?php if (!empty($content['charte']['description'])): ?>
                    <div class="charte-description"><?= $content['charte']['description'] ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Grille moderne des principes -->
            <div class="charte-principles-modern">
                <?php $i=1; foreach (($content['charte']['principles'] ?? []) as $principle): ?>
                <div class="principle-modern <?= $i == 1 ? 'principle-special' : '' ?>">
                    <?php if ($i == 1): ?>
                    <div class="principle-badge-modern">Charte de l'élu</div>
                    <?php endif; ?>
                    <div class="principle-number-modern"><?= sprintf('%02d', $i++) ?></div>
                    <div class="principle-content-modern">
                        <h4><?= htmlspecialchars($principle['title'] ?? '') ?></h4>
                        <p><?= htmlspecialchars($principle['description'] ?? '') ?></p>
                        <?php if (!empty($principle['thematique'])): ?>
                        <span class="principle-theme"><?= htmlspecialchars($principle['thematique']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Citation de transition : Charte → Vos idées -->
    <div class="transition-quote charte-idees" style="background-image: url('<?= htmlspecialchars($content['citations']['citation4']['background_image'] ?? 'Images/hero_test.png') ?>');">
        <div class="transition-quote-content">
            <blockquote class="transition-quote-text">
                <span class="quote-text"><?= htmlspecialchars($content['citations']['citation4']['text'] ?? 'S\'engager à être cohérent.es entre nos intentions et nos actes') ?></span>
                <span class="quote-author"><?= htmlspecialchars($content['citations']['citation4']['author'] ?? 'Notre cohérence') ?></span>
            </blockquote>
        </div>
    </div>

    <!-- Section Vos idées comptent -->
    <section class="idees" id="idees">
        <div class="container">
            <h2 class="section-title"><?= htmlspecialchars($content['idees']['title'] ?? 'Vos idées comptent') ?></h2>
            <h3 class="section-subtitle"><?= htmlspecialchars($content['idees']['subtitle'] ?? 'Osez proposer') ?></h3>
            
            <div class="idees-content">
                <div class="idees-text">
                    <p class="idees-description"><?= htmlspecialchars($content['idees']['description'] ?? 'Partagez vos idées, vos préoccupations, vos suggestions. Votre voix compte dans notre projet municipal.') ?></p>
                    
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <strong>✅ Message envoyé !</strong><br>
                            <?= htmlspecialchars($_SESSION['success']) ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <strong>❌ Erreur :</strong><br>
                            <?= htmlspecialchars($_SESSION['error']) ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <!-- Formulaire de contact -->
                    <div class="contact-form">
                        <form method="POST" action="forms/contact.php">
                            <div class="form-group">
                                <label for="nom">Nom et Prénom *</label>
                                <input type="text" id="nom" name="nom" required placeholder="Votre nom complet">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required placeholder="votre@email.fr">
                            </div>
                            
                            <div class="form-group">
                                <label for="objet">Objet *</label>
                                <input type="text" id="objet" name="objet" required placeholder="Sujet de votre message">
                            </div>
                            
                            <div class="form-group">
                                <label for="message">Votre message *</label>
                                <textarea id="message" name="message" rows="6" required placeholder="Partagez vos idées, vos préoccupations, vos suggestions..."></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Envoyer mon message</button>
                                <a href="/proposez" class="btn btn-secondary" style="margin-left: 12px;">💡 Faire une proposition</a>
                            </div>
                        </form>
                    </div>
                    
                    
                </div>
                
                <!-- Éléments décoratifs subtils -->
                <div class="idees-decorations">
                    <div class="floating-element" style="top: 20%; right: 10%; animation-delay: 2s;"></div>
                    <div class="floating-element" style="bottom: 30%; left: 5%; animation-delay: 5s;"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Médiathèque -->
    <section class="mediatheque" id="mediatheque">
        <div class="container">
            <h2 class="section-title"><?= htmlspecialchars($content['mediatheque']['title'] ?? 'Médiathèque') ?></h2>
            <h3 class="section-subtitle"><?= htmlspecialchars($content['mediatheque']['subtitle'] ?? 'Osons partager') ?></h3>
            
            <div class="mediatheque-content">
                <div class="mediatheque-description">
                    <p><?= htmlspecialchars($content['mediatheque']['description'] ?? 'Retrouvez tous nos documents, photos, vidéos et actualités dans notre espace de partage.') ?></p>
                </div>
                
                <div class="mediatheque-actions">
                    <a href="<?= htmlspecialchars($content['mediatheque']['drive_url'] ?? 'https://drive.google.com/drive/folders/your-folder-id') ?>" target="_blank" class="btn btn-primary mediatheque-btn">
                        <i class="fas fa-folder-open"></i>
                        <?= htmlspecialchars($content['mediatheque']['button_text'] ?? 'Accéder à la médiathèque') ?>
                    </a>
                    <p class="mediatheque-note">Documents, photos et vidéos disponibles en ligne</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <div class="footer-logo">
                        <?php
                        $footerLogoSrc = file_exists(__DIR__ . '/' . $logoWebp) ? $logoWebp : $logoPng;
                        ?>
                        <img src="<?= htmlspecialchars($footerLogoSrc) ?>" alt="Logo Osons Saint-Paul" class="footer-logo-img">
                        <span class="footer-logo-text">Osons Saint-Paul</span>
                    </div>
                    <p class="footer-description">Une liste citoyenne qui place l'humain au cœur de ses préoccupations. Ensemble, construisons le Saint-Paul de demain.</p>
                </div>

                <div class="footer-column">
                    <h3 class="footer-title">Restons en contact</h3>
                    <!-- Newsletter Form -->
                    <form class="newsletter" id="newsletter-footer">
                        <input class="newsletter-input" type="text" name="PRENOM" placeholder="Votre prénom" required />
                        <input class="newsletter-input" type="email" name="EMAIL" placeholder="Votre email" required />
                        <input type="text" name="email_address_check" value="" style="display:none" tabindex="-1" autocomplete="off">
                        <button type="submit" class="newsletter-btn">S'inscrire</button>
                    </form>
                    <div id="newsletter-success-footer" style="display:none; color: var(--primary-color); margin-top: 0.5rem; font-size: 0.9rem;">✓ Merci ! Vérifiez votre boîte mail pour confirmer votre inscription.</div>
                    <div id="newsletter-error-footer" style="display:none; color: #dc3545; margin-top: 0.5rem; font-size: 0.9rem;"></div>
                    
                    <div class="social-links" style="margin-top: 1.5rem;">
                        <a href="https://facebook.com" target="_blank" class="social-link"><i class="fab fa-facebook"></i></a>
                        <a href="https://instagram.com" target="_blank" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <div class="footer-column">
                    <h3 class="footer-title">Navigation</h3>
                    <div class="footer-links">
                        <a href="#programme" class="footer-link">Programme</a>
                        <a href="#equipe" class="footer-link">Équipe</a>
                        <a href="#rendez-vous" class="footer-link">Rendez-vous</a>
                        <a href="#charte" class="footer-link">Charte</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-legal">
                    <a href="#" class="legal-link">Mentions légales</a>
                    <a href="#" class="legal-link">Gestion des cookies</a>
                </div>
                <p class="footer-copyright">&copy; 2025 Osons Saint-Paul. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript chargé de manière asynchrone -->
    <script>
        // Script critique inline - Header sticky
        (function() {
            const header = document.getElementById('headerSticky');
            if (header) {
                function handleScroll() {
                    if (window.scrollY > 100) {
                        header.classList.add('visible');
                    } else {
                        header.classList.remove('visible');
                    }
                }
                handleScroll();
                window.addEventListener('scroll', handleScroll, { passive: true });
            }
        })();
    </script>
    
    <!-- Script principal -->
    <script src="script.js"></script>
    
    <!-- Newsletter Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handler générique pour les formulaires newsletter
            function handleNewsletterForm(formId, successId, errorId) {
                const form = document.getElementById(formId);
                if (!form) return;
                
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.textContent;
                    const successDiv = document.getElementById(successId);
                    const errorDiv = document.getElementById(errorId);
                    
                    // Reset messages
                    successDiv.style.display = 'none';
                    errorDiv.style.display = 'none';
                    
                    submitBtn.textContent = 'Envoi...';
                    submitBtn.disabled = true;
                    
                    fetch('forms/subscribe-newsletter.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.reset();
                            successDiv.style.display = 'block';
                            setTimeout(() => {
                                successDiv.style.display = 'none';
                            }, 10000);
                        } else {
                            errorDiv.textContent = '✗ ' + (data.error || 'Erreur lors de l\'inscription');
                            errorDiv.style.display = 'block';
                        }
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        errorDiv.textContent = '✗ Erreur de connexion. Réessayez.';
                        errorDiv.style.display = 'block';
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    });
                });
            }
            
            // Initialiser les deux formulaires
            handleNewsletterForm('newsletter-footer', 'newsletter-success-footer', 'newsletter-error-footer');
            handleNewsletterForm('newsletter-section', 'newsletter-success-section', 'newsletter-error-section');
        });
    </script>

    <!-- Correctif filtres programme (délégation robuste) -->
    <script>
        (function(){
            document.addEventListener('click', function(e){
                var btn = e.target.closest && e.target.closest('.filter-btn');
                if (!btn) return;
                e.preventDefault();
                var filter = btn.getAttribute('data-filter') || 'all';
                // Activer visuel
                document.querySelectorAll('.filter-btn').forEach(function(b){ b.classList.remove('active'); });
                btn.classList.add('active');
                // Appliquer filtre
                document.querySelectorAll('.proposition-card').forEach(function(card){
                    var category = card.getAttribute('data-category');
                    var isCitizen = !!card.querySelector('.card-badge.citoyenne');
                    var show = (filter === 'all') || (filter === 'citoyens' ? isCitizen : (category === filter && !isCitizen));
                    if (show) {
                        card.style.display = '';
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    } else {
                        card.style.opacity = '0';
                        card.style.transform = 'scale(0.92)';
                        setTimeout(function(){ card.style.display = 'none'; }, 120);
                    }
                });
            });
        })();
    </script>

    <!-- Gestion reCAPTCHA v3 pour le formulaire de contact -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.querySelector('form[action="forms/contact.php"]');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const form = this;
                    
                    grecaptcha.ready(function() {
                        grecaptcha.execute('6LeOrNorAAAAAGfkiHS2IqTbd5QbQHvinxR_4oek', {action: 'submit_contact'}).then(function(token) {
                            // Ajouter le token au formulaire
                            let recaptchaInput = form.querySelector('input[name="recaptcha_token"]');
                            if (!recaptchaInput) {
                                recaptchaInput = document.createElement('input');
                                recaptchaInput.type = 'hidden';
                                recaptchaInput.name = 'recaptcha_token';
                                form.appendChild(recaptchaInput);
                            }
                            recaptchaInput.value = token;
                            
                            // Soumettre le formulaire
                            form.submit();
                        });
                    });
                });
            }
        });
    </script>
</body>
</html>
<!-- Test webhook Fri Oct 10 13:10:56 CEST 2025 -->
<!-- Test webhook 2 - Fri Oct 10 13:15:45 CEST 2025 -->
