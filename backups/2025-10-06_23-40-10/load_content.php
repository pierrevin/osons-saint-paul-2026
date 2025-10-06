<?php
// Chargement des données du site depuis le JSON
function load_site_content() {
    $json_file = __DIR__ . '/site_content.json';
    
    if (file_exists($json_file)) {
        $content = json_decode(file_get_contents($json_file), true);
        return $content ?: [];
    }
    
    return [];
}

// Fonction pour obtenir les données d'une section spécifique
function get_section_data($section_name) {
    $content = load_site_content();
    return $content[$section_name] ?? [];
}

// Fonction pour obtenir les membres de l'équipe
function get_team_members() {
    $equipe_data = get_section_data('equipe');
    return $equipe_data['members'] ?? [];
}

// Fonction pour obtenir les propositions du programme
function get_programme_propositions() {
    $programme_data = get_section_data('programme');
    return $programme_data['propositions'] ?? [];
}

// Fonction pour obtenir les événements
function get_events() {
    $events_data = get_section_data('rendez_vous');
    return $events_data['events'] ?? [];
}

// Fonction pour obtenir les principes de la charte
function get_charte_principles() {
    $charte_data = get_section_data('charte');
    return $charte_data['principles'] ?? [];
}
?>
