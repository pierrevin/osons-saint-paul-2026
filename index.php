<?php
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
</head>
<body>
    <!-- Header sticky -->
    <header class="header-sticky" id="headerSticky">
        <div class="header-content">
            <a href="#hero" class="header-logo">
                <img src="uploads/Osons1.png" alt="Logo Osons Saint-Paul" class="logo-img">
            </a>
            <nav class="header-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="#programme" class="nav-link">Programme</a>
                    </li>
                    <li class="nav-item">
                        <a href="#equipe" class="nav-link">Équipe</a>
                    </li>
                    <li class="nav-item">
                        <a href="#rendez-vous" class="nav-link">Rendez-vous</a>
                    </li>
                    <li class="nav-item">
                        <a href="#charte" class="nav-link">Charte</a>
                    </li>
                    <li class="nav-item">
                        <a href="#idees" class="nav-link">Idées</a>
                    </li>
                    <li class="nav-item">
                        <a href="#mediatheque" class="nav-link">Médiathèque</a>
                    </li>
                    <li class="nav-item nav-item-cta">
                        <a href="/proposez" class="nav-link nav-link-btn">💡 Faire une proposition</a>
                    </li>
                </ul>
            </nav>
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Section Hero - La Première Carte Postale -->
    <section class="hero" id="hero">
        <div class="hero-background">
            <picture>
                <source media="(max-width: 768px)" srcset="<?= htmlspecialchars($content['hero']['background_image_mobile'] ?? ($content['hero']['background_image'] ?? 'Images/hero_test.png')) ?>">
                <img src="<?= htmlspecialchars($content['hero']['background_image'] ?? 'Images/hero_test.png') ?>" alt="Hero" style="width:0;height:0;opacity:0;position:absolute;" onload="this.parentElement.parentElement.style.backgroundImage='url(' + this.src + ')'">
            </picture>
            <div class="hero-overlay"></div>
        </div>
        <div class="hero-content">
            <div class="container">
                <!-- Logo intégré dans le hero -->
                <div class="hero-logo">
                    <img src="uploads/Osons1.png" alt="Logo Osons Saint-Paul" class="logo-img">
                </div>
                
                <h1 class="hero-title"><?= htmlspecialchars($content['hero']['title'] ?? 'Construisons ensemble le village vivant et partagé') ?></h1>
                <div class="hero-buttons">
                    <a href="#programme" class="btn btn-primary"><?= htmlspecialchars($content['hero']['button_primary'] ?? 'Découvrir le Programme') ?></a>
                    <a href="/proposez" class="btn btn-secondary"><?= htmlspecialchars($content['hero']['button_secondary'] ?? 'Faire une Proposition') ?></a>
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
                <p class="text-description">Ce programme évolutif s'enrichit au fil de vos propositions citoyennes et de nos actions validées collectivement.</p>
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
                    <i class="fas fa-hand-holding-heart"></i>
                    <span>Citoyens</span>
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
                                <div class="card-badge citoyenne"><i class="fas fa-lightbulb"></i></div>
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
                <img src="<?= htmlspecialchars($content['hero']['background_image'] ?? 'Images/hero_test.png') ?>" alt="Image hero mobile" loading="lazy">
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
            <div class="cta-box">
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
                <p>
                    <?php if (!empty($content['charte']['intro_text'])): ?>
                        <?= htmlspecialchars($content['charte']['intro_text']) ?>
                    <?php endif; ?>
                    <?php if (!empty($content['charte']['intro_highlight'])): ?>
                        <strong><?= htmlspecialchars($content['charte']['intro_highlight']) ?></strong>
                    <?php endif; ?>
                </p>
            </div>
            
            <div class="charte-principles">
                <?php $i=1; foreach (($content['charte']['principles'] ?? []) as $principle): ?>
                <div class="principle">
                    <div class="principle-number"><?= sprintf('%02d', $i++) ?></div>
                    <div class="principle-content">
                        <h3><?= htmlspecialchars($principle['title'] ?? '') ?></h3>
                        <p><?= htmlspecialchars($principle['description'] ?? '') ?></p>
                        <?php if (!empty($principle['thematique'])): ?>
                        <span class="principle-source"><?= htmlspecialchars($principle['thematique']) ?></span>
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
            <h2 class="section-title">Vos idées comptent</h2>
            <h3 class="section-subtitle">Osez proposer</h3>
            
            <div class="idees-content">
                <div class="idees-text">
                    <p class="idees-description">Partagez vos idées, vos préoccupations, vos suggestions. Votre voix compte dans notre projet municipal.</p>
                    
                    <!-- Formulaire de contact -->
                    <div class="contact-form">
                        <form>
                            <div class="form-group">
                                <label for="nom">Nom et Prénom *</label>
                                <input type="text" id="nom" name="nom" required placeholder="Votre nom complet">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required placeholder="votre@email.fr">
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
            <h2 class="section-title">Médiathèque</h2>
            <h3 class="section-subtitle">Osons partager</h3>
            
            <div class="mediatheque-content">
                <div class="mediatheque-description">
                    <p>Retrouvez tous nos documents, photos, vidéos et actualités dans notre espace de partage.</p>
                </div>
                
                <div class="mediatheque-actions">
                    <a href="https://drive.google.com/drive/folders/your-folder-id" target="_blank" class="btn btn-primary mediatheque-btn">
                        <i class="fas fa-folder-open"></i>
                        Accéder à la médiathèque
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
                        <img src="uploads/Osons1.png" alt="Logo Osons Saint-Paul" class="footer-logo-img">
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
                // Fonction pour gérer le scroll
                function handleScroll() {
                    if (window.scrollY > 100) {
                        header.classList.add('header-scrolled');
                    } else {
                        header.classList.remove('header-scrolled');
                    }
                }
                
                // Vérifier immédiatement au chargement
                handleScroll();
                
                // Écouter le scroll
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
</body>
</html>
