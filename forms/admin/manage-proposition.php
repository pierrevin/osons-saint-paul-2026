<?php
session_start();

// Vérification de l'authentification
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: propositions-admin.php');
    exit;
}

// Configuration
$DATA_FILE = '../../data/propositions.json';
$PROPOSITIONS_DATA_FILE = '../../data/site_content.json';

// Fonctions utilitaires
function loadPropositions() {
    global $DATA_FILE;
    if (file_exists($DATA_FILE)) {
        return json_decode(file_get_contents($DATA_FILE), true);
    }
    return ['propositions' => []];
}

function loadSiteContent() {
    global $PROPOSITIONS_DATA_FILE;
    if (file_exists($PROPOSITIONS_DATA_FILE)) {
        return json_decode(file_get_contents($PROPOSITIONS_DATA_FILE), true);
    }
    return [];
}

function savePropositions($data) {
    global $DATA_FILE;
    $data['last_updated'] = date('c');
    return file_put_contents($DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function saveSiteContent($data) {
    global $PROPOSITIONS_DATA_FILE;
    return file_put_contents($PROPOSITIONS_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Récupération de la proposition
$proposition_id = $_GET['id'] ?? '';
$propositions_data = loadPropositions();
$proposition = null;

foreach ($propositions_data['propositions'] as $prop) {
    if ($prop['id'] === $proposition_id) {
        $proposition = $prop;
        break;
    }
}

if (!$proposition) {
    header('Location: propositions-admin.php');
    exit;
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_to_programme':
            // Ajouter la proposition au programme principal
            $site_content = loadSiteContent();
            
            // Créer une nouvelle proposition pour le programme
            $new_proposal = [
                'id' => uniqid('prog_', true),
                'title' => $proposition['data']['titre'],
                'description' => $proposition['data']['description'],
                'category' => $proposition['data']['categories'][0], // Prendre la première catégorie
                'beneficiaires' => $proposition['data']['beneficiaires'],
                'cout' => $proposition['data']['cout'],
                'engagement' => $proposition['data']['engagement_details'],
                'source' => 'citoyenne',
                'citizen_proposal_id' => $proposition['id'],
                'date_added' => date('Y-m-d H:i:s')
            ];
            
            // Ajouter au programme
            if (!isset($site_content['programme']['proposals'])) {
                $site_content['programme']['proposals'] = [];
            }
            $site_content['programme']['proposals'][] = $new_proposal;
            
            // Marquer la proposition comme approuvée et intégrée
            foreach ($propositions_data['propositions'] as &$prop) {
                if ($prop['id'] === $proposition_id) {
                    $prop['status'] = 'approved';
                    $prop['integrated'] = true;
                    $prop['integrated_at'] = date('Y-m-d H:i:s');
                    $prop['programme_id'] = $new_proposal['id'];
                    break;
                }
            }
            
            saveSiteContent($site_content);
            savePropositions($propositions_data);
            
            $success_message = "Proposition ajoutée au programme avec succès !";
            break;
            
        case 'update_status':
            $status = $_POST['status'] ?? '';
            foreach ($propositions_data['propositions'] as &$prop) {
                if ($prop['id'] === $proposition_id) {
                    $prop['status'] = $status;
                    $prop['updated_at'] = date('Y-m-d H:i:s');
                    break;
                }
            }
            savePropositions($propositions_data);
            $proposition = array_values(array_filter($propositions_data['propositions'], function($p) use ($proposition_id) {
                return $p['id'] === $proposition_id;
            }))[0];
            $success_message = "Statut mis à jour avec succès !";
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer la proposition - Osons Saint-Paul 2026</title>
    <link rel="stylesheet" href="../../styles.css">
    <style>
        .manage-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
        }

        .manage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: var(--cream);
            border-radius: 15px;
        }

        .manage-header h1 {
            font-family: var(--font-script);
            font-size: 1.8rem;
            color: var(--coral);
            margin: 0;
        }

        .back-btn {
            background: var(--deep-green);
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .proposition-detail {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .detail-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--cream);
            border-radius: 10px;
            border-left: 4px solid var(--coral);
        }

        .detail-section h3 {
            font-family: var(--font-script);
            font-size: 1.3rem;
            color: var(--deep-green);
            margin-bottom: 1rem;
        }

        .detail-field {
            margin-bottom: 1rem;
        }

        .detail-label {
            font-weight: 600;
            color: var(--dark-blue);
            display: block;
            margin-bottom: 0.3rem;
        }

        .detail-value {
            color: #666;
            line-height: 1.6;
        }

        .categories-list {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .category-tag {
            background: var(--coral);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
        }

        .actions-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .action-card {
            background: var(--cream);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .action-card:hover {
            border-color: var(--coral);
            transform: translateY(-2px);
        }

        .action-card h4 {
            font-family: var(--font-script);
            color: var(--deep-green);
            margin-bottom: 1rem;
        }

        .action-card p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .btn-action {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-approve {
            background: #28a745;
            color: white;
        }

        .btn-reject {
            background: #dc3545;
            color: white;
        }

        .btn-pending {
            background: #ffc107;
            color: #212529;
        }

        .btn-integrate {
            background: var(--coral);
            color: white;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid #28a745;
        }

        @media (max-width: 768px) {
            .manage-container {
                margin: 1rem;
                padding: 1rem;
            }
            
            .manage-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="manage-container">
        <div class="manage-header">
            <h1>📝 Gérer la proposition</h1>
            <a href="propositions-admin.php" class="back-btn">← Retour à la liste</a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                ✅ <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <div class="proposition-detail">
            <div class="status-badge status-<?= $proposition['status'] ?>">
                <?= ucfirst($proposition['status']) ?>
            </div>

            <div class="detail-section">
                <h3>📋 Informations générales</h3>
                
                <div class="detail-field">
                    <span class="detail-label">ID de la proposition :</span>
                    <span class="detail-value"><?= htmlspecialchars($proposition['id']) ?></span>
                </div>
                
                <div class="detail-field">
                    <span class="detail-label">Date de soumission :</span>
                    <span class="detail-value"><?= date('d/m/Y à H:i', strtotime($proposition['date'])) ?></span>
                </div>
                
                <?php if (isset($proposition['updated_at'])): ?>
                <div class="detail-field">
                    <span class="detail-label">Dernière mise à jour :</span>
                    <span class="detail-value"><?= date('d/m/Y à H:i', strtotime($proposition['updated_at'])) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="detail-section">
                <h3>👤 Informations du proposant</h3>
                
                <div class="detail-field">
                    <span class="detail-label">Email :</span>
                    <span class="detail-value">
                        <a href="mailto:<?= htmlspecialchars($proposition['data']['email']) ?>">
                            <?= htmlspecialchars($proposition['data']['email']) ?>
                        </a>
                    </span>
                </div>
                
                <?php if ($proposition['data']['nom']): ?>
                <div class="detail-field">
                    <span class="detail-label">Nom :</span>
                    <span class="detail-value"><?= htmlspecialchars($proposition['data']['nom']) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if ($proposition['data']['commune']): ?>
                <div class="detail-field">
                    <span class="detail-label">Commune :</span>
                    <span class="detail-value"><?= htmlspecialchars($proposition['data']['commune']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="detail-section">
                <h3>💡 Proposition</h3>
                
                <div class="detail-field">
                    <span class="detail-label">Titre :</span>
                    <span class="detail-value"><?= htmlspecialchars($proposition['data']['titre']) ?></span>
                </div>
                
                <div class="detail-field">
                    <span class="detail-label">Description :</span>
                    <span class="detail-value"><?= nl2br(htmlspecialchars($proposition['data']['description'])) ?></span>
                </div>
                
                <div class="detail-field">
                    <span class="detail-label">Catégories :</span>
                    <div class="categories-list">
                        <?php foreach ($proposition['data']['categories'] as $category): ?>
                            <span class="category-tag"><?= htmlspecialchars($category) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="detail-section">
                <h3>🎯 Impact & faisabilité</h3>
                
                <div class="detail-field">
                    <span class="detail-label">Bénéficiaires :</span>
                    <span class="detail-value"><?= nl2br(htmlspecialchars($proposition['data']['beneficiaires'])) ?></span>
                </div>
                
                <?php if ($proposition['data']['cout']): ?>
                <div class="detail-field">
                    <span class="detail-label">Estimation du coût :</span>
                    <span class="detail-value"><?= htmlspecialchars($proposition['data']['cout']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="detail-section">
                <h3>🤝 Engagement citoyen</h3>
                
                <div class="detail-field">
                    <span class="detail-label">Participation souhaitée :</span>
                    <span class="detail-value"><?= $proposition['data']['engagement'] === 'oui' ? 'Oui' : 'Non' ?></span>
                </div>
                
                <?php if ($proposition['data']['engagement_details']): ?>
                <div class="detail-field">
                    <span class="detail-label">Détails de l'engagement :</span>
                    <span class="detail-value"><?= nl2br(htmlspecialchars($proposition['data']['engagement_details'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="actions-section">
            <h3 style="font-family: var(--font-script); color: var(--deep-green); margin-bottom: 2rem;">⚡ Actions disponibles</h3>
            
            <div class="actions-grid">
                <?php if ($proposition['status'] !== 'approved'): ?>
                <div class="action-card">
                    <h4>✅ Approuver</h4>
                    <p>Marquer cette proposition comme approuvée</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="status" value="approved">
                        <button type="submit" class="btn-action btn-approve" onclick="return confirm('Approuver cette proposition ?')">
                            Approuver
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($proposition['status'] !== 'rejected'): ?>
                <div class="action-card">
                    <h4>❌ Rejeter</h4>
                    <p>Rejeter cette proposition</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="status" value="rejected">
                        <button type="submit" class="btn-action btn-reject" onclick="return confirm('Rejeter cette proposition ?')">
                            Rejeter
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($proposition['status'] !== 'pending'): ?>
                <div class="action-card">
                    <h4>⏳ Remettre en attente</h4>
                    <p>Remettre cette proposition en attente</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="status" value="pending">
                        <button type="submit" class="btn-action btn-pending">
                            En attente
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if ($proposition['status'] === 'approved' && !isset($proposition['integrated'])): ?>
                <div class="action-card">
                    <h4>🚀 Intégrer au programme</h4>
                    <p>Ajouter cette proposition au programme principal du site</p>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_to_programme">
                        <button type="submit" class="btn-action btn-integrate" onclick="return confirm('Ajouter cette proposition au programme principal ?')">
                            Intégrer au programme
                        </button>
                    </form>
                </div>
                <?php endif; ?>

                <?php if (isset($proposition['integrated'])): ?>
                <div class="action-card">
                    <h4>✅ Déjà intégrée</h4>
                    <p>Cette proposition a été intégrée au programme le <?= date('d/m/Y', strtotime($proposition['integrated_at'])) ?></p>
                    <div style="background: #d4edda; padding: 1rem; border-radius: 10px; color: #155724;">
                        <strong>ID programme :</strong> <?= htmlspecialchars($proposition['programme_id']) ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
