<?php
// Page d'analyse des propositions citoyennes
session_start();

require_once __DIR__ . '/../config.php';

// Vérification de l'authentification admin
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: ../index.php');
    exit;
}

// Chargement des propositions
$propositions_data = json_decode(file_get_contents('../../data/propositions.json'), true);
$propositions = $propositions_data['propositions'] ?? [];

// Statistiques
$total = count($propositions);
$pending = count(array_filter($propositions, fn($p) => $p['status'] === 'pending'));
$approved = count(array_filter($propositions, fn($p) => $p['status'] === 'approved'));
$rejected = count(array_filter($propositions, fn($p) => $p['status'] === 'rejected'));
$integrated = count(array_filter($propositions, fn($p) => $p['integrated'] ?? false));

// Statistiques par catégorie
$categories_stats = [];
foreach ($propositions as $proposition) {
    foreach ($proposition['data']['categories'] ?? [] as $category) {
        $categories_stats[$category] = ($categories_stats[$category] ?? 0) + 1;
    }
}

// Statistiques par mois
$monthly_stats = [];
foreach ($propositions as $proposition) {
    $month = date('Y-m', strtotime($proposition['date']));
    $monthly_stats[$month] = ($monthly_stats[$month] ?? 0) + 1;
}

// Statistiques par commune
$commune_stats = [];
foreach ($propositions as $proposition) {
    $commune = $proposition['data']['commune'] ?: 'Non renseignée';
    $commune_stats[$commune] = ($commune_stats[$commune] ?? 0) + 1;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Analyse des Propositions Citoyennes</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .analytics-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .analytics-header h1 {
            color: #2d5a3d;
            margin-bottom: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 5px solid;
        }
        
        .stat-card.pending { border-left-color: #ffc107; }
        .stat-card.approved { border-left-color: #28a745; }
        .stat-card.rejected { border-left-color: #dc3545; }
        .stat-card.integrated { border-left-color: #17a2b8; }
        .stat-card.total { border-left-color: #6c757d; }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .chart-container {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d5a3d;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .filters-section {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            font-weight: 600;
            color: #2d5a3d;
            margin-bottom: 0.5rem;
        }
        
        .filter-group select,
        .filter-group input {
            padding: 0.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
        }
        
        .propositions-table {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #2d5a3d;
            color: white;
            padding: 1rem;
            font-weight: 600;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2d5a3d;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .category-tag {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 0.2rem 0.5rem;
            border-radius: 8px;
            font-size: 0.8rem;
            margin: 0.1rem;
        }
        
        .actions-cell {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            border-radius: 6px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        
        .export-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .btn-export {
            background: #28a745;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-export:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="analytics-container">
        <!-- Header -->
        <div class="analytics-header">
            <h1>📊 Analyse des Propositions Citoyennes</h1>
            <p>Tableau de bord complet pour analyser toutes les propositions soumises</p>
            <a href="schema_admin.php" class="btn-export">← Retour à l'admin</a>
        </div>

        <!-- Statistiques principales -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?= $total ?></div>
                <div class="stat-label">Total des propositions</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?= $pending ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-card approved">
                <div class="stat-number"><?= $approved ?></div>
                <div class="stat-label">Approuvées</div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-number"><?= $rejected ?></div>
                <div class="stat-label">Rejetées</div>
            </div>
            <div class="stat-card integrated">
                <div class="stat-number"><?= $integrated ?></div>
                <div class="stat-label">Intégrées au programme</div>
            </div>
        </div>

        <!-- Graphiques -->
        <div class="charts-grid">
            <div class="chart-container">
                <div class="chart-title">📈 Évolution mensuelle</div>
                <canvas id="monthlyChart"></canvas>
            </div>
            <div class="chart-container">
                <div class="chart-title">🏷️ Répartition par catégorie</div>
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="chart-container">
                <div class="chart-title">🏘️ Répartition par commune</div>
                <canvas id="communeChart"></canvas>
            </div>
            <div class="chart-container">
                <div class="chart-title">📊 Statuts des propositions</div>
                <canvas id="statusChart"></canvas>
            </div>
        </div>

        <!-- Filtres et export -->
        <div class="filters-section">
            <h3>🔍 Filtres et Export</h3>
            <div class="export-buttons">
                <button class="btn-export" onclick="exportToCSV()">📊 Exporter CSV</button>
                <button class="btn-export" onclick="exportToJSON()">📄 Exporter JSON</button>
                <button class="btn-export" onclick="printReport()">🖨️ Imprimer</button>
            </div>
            
            <div class="filters-grid">
                <div class="filter-group">
                    <label>Statut</label>
                    <select id="statusFilter" onchange="filterTable()">
                        <option value="">Tous les statuts</option>
                        <option value="pending">En attente</option>
                        <option value="approved">Approuvées</option>
                        <option value="rejected">Rejetées</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Catégorie</label>
                    <select id="categoryFilter" onchange="filterTable()">
                        <option value="">Toutes les catégories</option>
                        <?php foreach (array_keys($categories_stats) as $category): ?>
                            <option value="<?= htmlspecialchars($category) ?>"><?= htmlspecialchars($category) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Commune</label>
                    <select id="communeFilter" onchange="filterTable()">
                        <option value="">Toutes les communes</option>
                        <?php foreach (array_keys($commune_stats) as $commune): ?>
                            <option value="<?= htmlspecialchars($commune) ?>"><?= htmlspecialchars($commune) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Recherche</label>
                    <input type="text" id="searchFilter" placeholder="Titre, description..." onkeyup="filterTable()">
                </div>
            </div>
        </div>

        <!-- Tableau des propositions -->
        <div class="propositions-table">
            <div class="table-header">
                📋 Détail de toutes les propositions (<?= $total ?>)
            </div>
            <div class="table-container">
                <table id="propositionsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Catégories</th>
                            <th>Contact</th>
                            <th>Bénéficiaires</th>
                            <th>Coût</th>
                            <th>Engagement</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($propositions as $proposition): ?>
                            <tr data-status="<?= $proposition['status'] ?>" 
                                data-categories="<?= htmlspecialchars(implode(',', $proposition['data']['categories'] ?? [])) ?>"
                                data-commune="<?= htmlspecialchars($proposition['data']['commune'] ?: 'Non renseignée') ?>"
                                data-search="<?= htmlspecialchars(strtolower($proposition['data']['titre'] . ' ' . $proposition['data']['description'])) ?>">
                                <td><?= date('d/m/Y H:i', strtotime($proposition['date'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $proposition['status'] ?>">
                                        <?= $proposition['status'] === 'pending' ? 'En attente' : 
                                            ($proposition['status'] === 'approved' ? 'Approuvée' : 'Rejetée') ?>
                                    </span>
                                </td>
                                <td><strong><?= htmlspecialchars($proposition['data']['titre']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($proposition['data']['description'], 0, 100)) ?>...</td>
                                <td>
                                    <?php foreach ($proposition['data']['categories'] ?? [] as $category): ?>
                                        <span class="category-tag"><?= htmlspecialchars($category) ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($proposition['data']['nom']) ?></div>
                                    <div style="font-size: 0.8rem; color: #6c757d;">
                                        <?= htmlspecialchars($proposition['data']['email']) ?>
                                        <?php if ($proposition['data']['telephone']): ?>
                                            <br><?= htmlspecialchars($proposition['data']['telephone']) ?>
                                        <?php endif; ?>
                                        <?php if ($proposition['data']['commune']): ?>
                                            <br><?= htmlspecialchars($proposition['data']['commune']) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($proposition['data']['beneficiaires'] ?? '') ?></td>
                                <td><?= htmlspecialchars($proposition['data']['cout'] ?? '') ?></td>
                                <td>
                                    <?php if ($proposition['data']['engagement'] ?? false): ?>
                                        ✅ Oui
                                        <?php if ($proposition['data']['engagement_details'] ?? ''): ?>
                                            <br><small><?= htmlspecialchars($proposition['data']['engagement_details']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        ❌ Non
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="../../forms/admin/manage-proposition.php?id=<?= $proposition['id'] ?>" 
                                           class="btn-sm btn-primary" target="_blank">👁️ Voir</a>
                                        <?php if ($proposition['status'] === 'pending'): ?>
                                            <button class="btn-sm btn-success" onclick="approveProposal('<?= $proposition['id'] ?>')">✅ Approuver</button>
                                            <button class="btn-sm btn-danger" onclick="rejectProposal('<?= $proposition['id'] ?>')">❌ Rejeter</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Données pour les graphiques
        const monthlyData = <?= json_encode($monthly_stats) ?>;
        const categoryData = <?= json_encode($categories_stats) ?>;
        const communeData = <?= json_encode($commune_stats) ?>;
        const statusData = {
            'En attente': <?= $pending ?>,
            'Approuvées': <?= $approved ?>,
            'Rejetées': <?= $rejected ?>
        };

        // Graphique évolution mensuelle
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: Object.keys(monthlyData).sort(),
                datasets: [{
                    label: 'Propositions',
                    data: Object.keys(monthlyData).sort().map(month => monthlyData[month]),
                    borderColor: '#2d5a3d',
                    backgroundColor: 'rgba(45, 90, 61, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Graphique catégories
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(categoryData),
                datasets: [{
                    data: Object.values(categoryData),
                    backgroundColor: [
                        '#2d5a3d', '#ec654f', '#fcc549', '#4e9eb0', '#004a6d',
                        '#65ae99', '#6c757d', '#17a2b8', '#ffc107', '#dc3545'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Graphique communes
        const communeCtx = document.getElementById('communeChart').getContext('2d');
        new Chart(communeCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(communeData),
                datasets: [{
                    label: 'Propositions',
                    data: Object.values(communeData),
                    backgroundColor: '#ec654f'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Graphique statuts
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: ['#ffc107', '#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Filtrage du tableau
        function filterTable() {
            const statusFilter = document.getElementById('statusFilter').value;
            const categoryFilter = document.getElementById('categoryFilter').value;
            const communeFilter = document.getElementById('communeFilter').value;
            const searchFilter = document.getElementById('searchFilter').value.toLowerCase();

            const rows = document.querySelectorAll('#propositionsTable tbody tr');
            
            rows.forEach(row => {
                const status = row.dataset.status;
                const categories = row.dataset.categories;
                const commune = row.dataset.commune;
                const search = row.dataset.search;

                const statusMatch = !statusFilter || status === statusFilter;
                const categoryMatch = !categoryFilter || categories.includes(categoryFilter);
                const communeMatch = !communeFilter || commune === communeFilter;
                const searchMatch = !searchFilter || search.includes(searchFilter);

                row.style.display = (statusMatch && categoryMatch && communeMatch && searchMatch) ? '' : 'none';
            });
        }

        // Export CSV
        function exportToCSV() {
            const table = document.getElementById('propositionsTable');
            const rows = Array.from(table.querySelectorAll('tr'));
            
            let csv = rows.map(row => {
                return Array.from(row.querySelectorAll('th, td')).map(cell => {
                    return '"' + cell.textContent.replace(/"/g, '""') + '"';
                }).join(',');
            }).join('\n');
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'propositions-citoyennes-' + new Date().toISOString().split('T')[0] + '.csv';
            a.click();
        }

        // Export JSON
        function exportToJSON() {
            const data = <?= json_encode($propositions) ?>;
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'propositions-citoyennes-' + new Date().toISOString().split('T')[0] + '.json';
            a.click();
        }

        // Impression
        function printReport() {
            window.print();
        }

        // Actions sur les propositions
        function approveProposal(id) {
            if (confirm('Approuver cette proposition ?')) {
                // Rediriger vers l'admin avec l'action
                window.open('schema_admin.php#citizen-proposals', '_blank');
            }
        }

        function rejectProposal(id) {
            if (confirm('Rejeter cette proposition ?')) {
                // Rediriger vers l'admin avec l'action
                window.open('schema_admin.php#citizen-proposals', '_blank');
            }
        }
    </script>
</body>
</html>
