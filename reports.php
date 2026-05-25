<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
requireRole('responsable');

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// 1. CORRECTION : Supprimer les alias avec des mots réservés
$taskstats = $db->query("SELECT
    SUM(CASE WHEN status = 'terminee' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'en_cours' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'a_faire' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'en_retard' THEN 1 ELSE 0 END) as task_delayed,
    SUM(CASE WHEN status = 'annulee' THEN 1 ELSE 0 END) as task_cancelled
    FROM tasks")->fetch(PDO::FETCH_ASSOC);

//2. Taches par agent (Top 5)    
$topAgents = $db->query("SELECT 
    u.username,
    COUNT(t.id) as total_tasks,
    SUM(CASE WHEN t.status = 'terminee' THEN 1 ELSE 0 END) as completed_tasks,
    ROUND(AVG(CASE WHEN t.status = 'terminee' AND t.actual_hours IS NOT NULL THEN t.actual_hours ELSE NULL END), 1) as avg_hours
    FROM users u 
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.role = 'agent'
    GROUP BY u.id
    ORDER BY completed_tasks DESC
    LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

//3. Heures travaillées par mois (derniers 6 mois)
$hoursByMonth = $db->query("SELECT 
    DATE_FORMAT(work_date, '%Y-%m') as month,
    SUM(hours_worked) as total_hours
    FROM work_logs
    WHERE work_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(work_date, '%Y-%m')
    ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);

//4. Évaluations moyennes par agent
    $avgRatings = $db->query("SELECT
    u.username,
    ROUND(AVG(r.rating), 1) as avg_rating,
    COUNT(pr.id) as review_count
    FROM users u
    LEFT JOIN performance_reviews pr ON u.id = pr.agent_id
    WHERE u.role = 'agent'
    GROUP BY u.id
    ORDER BY avg_rating DESC")->fetchAll(PDO::FETCH_ASSOC);

//5. Taches complétées par mois (derniers 6 mois)
$completedByMonth = $db->query("SELECT
DATE_FORMAT(completed_at, '%Y-%m') as month,
COUNT (*) as task_count
FROM tasks
WHERE status = 'terminee' AND completed_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
GROUP BY DATE_FORMAT(completed_at, '%Y-%m')
ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);

//6. Rapport de performance global
$globalReport = $db->query("SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'agent') as total_agents,
    (SELECT COUNT(*) FROM tasks) as total_tasks,
    (SELECT COUNT(*) FROM tasks WHERE status = 'terminee') as completed_tasks,
    (SELECT ROUND(AVG(overall_rating), 1) FROM performance_reviews) as avg_rating,
    (SELECT SUM(hours_worked) FROM work_logs WHERE work_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as hours_last_month
    ")->fetch(PDO::FETCH_ASSOC);

// Récupérer le nombre de messages non lus pour le menu
$unreadMsgCount = 0;
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unreadMsgCount = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    $unreadMsgCount = 0;
}

$unreadCount = 0;
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unreadCount = (int) $stmt->fetchColumn();
} catch (Exception $e) {
    $unreadCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapports et statistiques - Gestion des Agents</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .stat-card-big{
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card-big:hover {
            transform: translateY(-5px);
        }
        .stat-number-big {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
        }
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chart-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            overflow-x: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #667eea;
            color: white;
        }
        .export-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            justify-content: flex-end;
        }
        .btn-export {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-export-excel {
            background: #2196F3;
        }
        .rating-high {
            color: #4CAF50;
            font-weight: bold;
        }
        .rating-medium {
            color: #FF9800;
            font-weight: bold;
        }
        .rating-low {
            color: #F44336;
            font-weight: bold;
        }
        @media print{
            .navbar, .export-buttons {
                display: none;
            }
             body{ background: white; padding: 20px;}
                .stat-card-big, .chart-container, .table-container {
                    break-inside: avoid;
                    box-shadow: none;
                    border: 1px solid #ddd;
                }
        }
    </style>
</head>
<body>
    <div class="dashboard">
    <nav class="navbar">
        <h1>📊 Rapports et statistiques</h1>
        <div class="nav-links">
            <a href="responsable_dashboard.php">Dashboard</a>
            <a href="schedules.php">Horaires</a>
            <a href="tasks.php">Tâches</a>
            <a href="performance.php">Performances</a>
            <a href="reports.php" class="active">Rapports</a>
            <a href="messages.php">💬 Messages <?= $unreadMsgCount > 0 ? "<span style='background:red; color:white; border-radius:50%; padding:2px 6px;'>$unreadMsgCount</span>" : '' ?></a>
            <a href="notifications.php">🔔 Notifications <?= $unreadCount > 0 ? "<span style='background:red; color:white; border-radius:50%; padding:2px 6px;'>$unreadCount</span>" : '' ?></a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </nav>
    
    <div class="content">
        <div class="export-buttons">
            <button class="btn-export" onclick="window.print()">📄 Exporter en PDF</button>
            <button class="btn-export btn-export-excel" onclick="exportToExcel()">📊 Exporter en Excel</button>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card-big">
                <div class="stat-number-big"><?= $globalReport['total_agents'] ?? 0 ?></div>
                <div class="stat-label">Agents actifs</div>
            </div>
            <div class="stat-card-big">
                <div class="stat-number-big"><?= $globalReport['total_tasks'] ?? 0 ?></div>
                <div class="stat-label">Tâches totales</div>
            </div>
            <div class="stat-card-big">
                <div class="stat-number-big"><?= $globalReport['completed_tasks'] ?? 0 ?></div>
                <div class="stat-label">Tâches terminées</div>
            </div>
            <div class="stat-card-big">
                <div class="stat-number-big"><?= $globalReport['avg_rating'] ?? 0 ?>/10</div>
                <div class="stat-label">Note moyenne</div>
            </div>
            <div class="stat-card-big">
                <div class="stat-number-big"><?= $globalReport['hours_last_month'] ?? 0 ?> h</div>
                <div class="stat-label">Heures (30 derniers jours)</div>
            </div>
        </div>
        
        <div class="chart-row">
            <div class="chart-container">
                <h3>📈 État des tâches</h3>
                <canvas id="tasksChart" width="400" height="300"></canvas>
            </div>
            <div class="chart-container">
                <h3>⭐ Top 5 agents (tâches complétées)</h3>
                <canvas id="topAgentsChart" width="400" height="300"></canvas>
            </div>
        </div>
        
        <div class="chart-row">
            <div class="chart-container">
                <h3>⏱️ Évolution des heures travaillées</h3>
                <canvas id="hoursChart" width="400" height="300"></canvas>
            </div>
            <div class="chart-container">
                <h3>✅ Tâches complétées par mois</h3>
                <canvas id="completedChart" width="400" height="300"></canvas>
            </div>
        </div>
        
        <div class="table-container">
            <h3>👥 Performance des agents</h3>
            <table>
                <thead><tr><th>Agent</th><th>Note moyenne</th><th>Évaluations</th><th>Performance</th></tr></thead>
                <tbody>
                    <?php foreach ($avgRatings as $agent): ?>
                        <?php 
                        $perfClass = '';
                        $perfText = '';
                        if ($agent['avg_rating'] >= 8) {
                            $perfClass = 'rating-high';
                            $perfText = 'Excellent';
                        } elseif ($agent['avg_rating'] >= 6) {
                            $perfClass = 'rating-medium';
                            $perfText = 'Bon';
                        } elseif ($agent['avg_rating'] > 0) {
                            $perfClass = 'rating-low';
                            $perfText = 'À améliorer';
                        } else {
                            $perfText = 'Non évalué';
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($agent['username']) ?></td>
                            <td class="<?= $perfClass ?>"><?= $agent['avg_rating'] ?: '-' ?>/10</td>
                            <td><?= $agent['review_count'] ?></td>
                            <td><?= $perfText ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="table-container">
            <h3>🏆 Top 5 agents (tâches complétées)</h3>
            <table>
                <thead><tr><th>Agent</th><th>Tâches totales</th><th>Tâches complétées</th><th>Taux de réussite</th><th>Temps moyen (h)</th></tr></thead>
                <tbody>
                    <?php foreach ($topAgents as $agent): ?>
                        <?php $successRate = $agent['total_tasks'] > 0 ? round(($agent['completed_tasks'] / $agent['total_tasks']) * 100, 1) : 0; ?>
                        <tr>
                            <td><?= htmlspecialchars($agent['username']) ?></td>
                            <td><?= $agent['total_tasks'] ?></td>
                            <td><?= $agent['completed_tasks'] ?></td>
                            <td><?= $successRate ?>%</td>
                            <td><?= $agent['avg_hours'] ?: '-' ?> h</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Graphique des tâches par statut
new Chart(document.getElementById('tasksChart'), {
    type: 'doughnut',
    data: {
        labels: ['Terminées', 'En cours', 'En attente', 'En retard', 'Annulées'],
        datasets: [{
            data: [
                <?= $tasksStats['completed'] ?? 0 ?>,
                <?= $tasksStats['in_progress'] ?? 0 ?>,
                <?= $tasksStats['pending'] ?? 0 ?>,
                <?= $tasksStats['task_delayed'] ?? 0 ?>,
                <?= $tasksStats['task_cancelled'] ?? 0 ?>
            ],
            backgroundColor: ['#4CAF50', '#FF9800', '#2196F3', '#F44336', '#9E9E9E']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Graphique Top agents
new Chart(document.getElementById('topAgentsChart'), {
    type: 'bar',
    data: {
        labels: [<?php foreach ($topAgents as $a) echo "'" . addslashes($a['username']) . "',"; ?>],
        datasets: [{
            label: 'Tâches complétées',
            data: [<?php foreach ($topAgents as $a) echo $a['completed_tasks'] . ","; ?>],
            backgroundColor: '#667eea'
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// Graphique heures par mois
new Chart(document.getElementById('hoursChart'), {
    type: 'line',
    data: {
        labels: [<?php foreach ($hoursByMonth as $h) echo "'" . $h['month'] . "',"; ?>],
        datasets: [{
            label: 'Heures travaillées',
            data: [<?php foreach ($hoursByMonth as $h) echo $h['total_hours'] . ","; ?>],
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: { responsive: true }
});

// Graphique tâches complétées par mois
new Chart(document.getElementById('completedChart'), {
    type: 'bar',
    data: {
        labels: [<?php foreach ($completedByMonth as $c) echo "'" . $c['month'] . "',"; ?>],
        datasets: [{
            label: 'Tâches complétées',
            data: [<?php foreach ($completedByMonth as $c) echo $c['task_count'] . ","; ?>],
            backgroundColor: '#2196F3'
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// Export Excel
function exportToExcel() {
    let csv = [];
    csv.push(["Rapport de performance - Gestion des Agents"]);
    csv.push(["Date d'export", new Date().toLocaleString()]);
    csv.push([]);
    csv.push(["Statistiques globales"]);
    csv.push(["Total agents", "<?= $globalReport['total_agents'] ?>"]);
    csv.push(["Total tâches", "<?= $globalReport['total_tasks'] ?>"]);
    csv.push(["Tâches terminées", "<?= $globalReport['completed_tasks'] ?>"]);
    csv.push(["Note moyenne", "<?= $globalReport['avg_rating'] ?>"]);
    csv.push([]);
    csv.push(["Performance des agents"]);
    csv.push(["Agent", "Note moyenne", "Évaluations"]);
    <?php foreach ($avgRatings as $a): ?>
    csv.push(["<?= addslashes($a['username']) ?>", "<?= $a['avg_rating'] ?>", "<?= $a['review_count'] ?>"]);
    <?php endforeach; ?>
    
    const blob = new Blob([csv.join("\n")], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.href = url;
    link.setAttribute('download', 'rapport_performance.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>
</body>
</html>