<?php
require_once 'config/database.php';
requireRole('agent');

$database = new Database();
$db = $database->getConnection();
$agent_id = $_SESSION['user_id'];

// Récupérer les évaluations
$reviews = $db->prepare("SELECT pr.*, u.username as reviewer_name 
                         FROM performance_reviews pr
                         JOIN users u ON pr.reviewer_id = u.id
                         WHERE pr.agent_id = ?
                         ORDER BY pr.review_date DESC");
$reviews->execute([$agent_id]);
$reviews = $reviews->fetchAll();

// Récupérer le total des heures travaillées (dernier mois)
$hoursStmt = $db->prepare("SELECT SUM(hours_worked) as total_hours FROM work_logs WHERE user_id = ? AND work_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$hoursStmt->execute([$agent_id]);
$totalHours = $hoursStmt->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ma performance</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="dashboard">
    <nav class="navbar">
        <h1>📈 Ma performance</h1>
        <div class="nav-links">
            <a href="agent_dashboard.php">Dashboard</a>
            <a href="my_tasks.php">Tâches</a>
            <a href="work_log.php">Heures</a>
            <a href="my_performance.php" class="active">Performance</a>
            <a href="notifications.php">Notifications</a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Déconnexion</a>
            <a href="messages.php">
    💬 Messages
    <?php 
    $unreadMsgCount = getUnreadMessagesCount($db, $_SESSION['user_id']);
    if ($unreadMsgCount > 0): ?>
        <span style="background:red; color:white; border-radius:50%; padding:2px 6px;"><?= $unreadMsgCount ?></span>
    <?php endif; ?>
</a>
        </div>
    </nav>
    <div class="content">
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Heures travaillées (30j)</h3>
                <p class="stat-number"><?= $totalHours ?> h</p>
            </div>
            <?php 
            $moyenne = 0;
            if (count($reviews) > 0) {
                $somme = array_sum(array_column($reviews, 'overall_rating'));
                $moyenne = round($somme / count($reviews), 1);
            }
            ?>
            <div class="stat-card">
                <h3>Note moyenne</h3>
                <p class="stat-number"><?= $moyenne ?> /10</p>
            </div>
        </div>

        <h2>📋 Mes évaluations</h2>
        <?php if (empty($reviews)): ?>
            <div class="empty-state">Aucune évaluation pour le moment.</div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Période</th><th>Note générale</th><th>Qualité</th><th>Ponctualité</th><th>Commentaires</th><th>Feedback</th><th>Évalué par</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($r['period_start'])) ?> - <?= date('d/m/Y', strtotime($r['period_end'])) ?></td>
                        <td><?= $r['overall_rating'] ?>/10</td>
                        <td><?= $r['quality_rating'] ?>/10</td>
                        <td><?= $r['punctuality_rating'] ?>/10</td>
                        <td><?= nl2br(htmlspecialchars($r['comments'] ?? '')) ?></td>
                        <td><?= nl2br(htmlspecialchars($r['feedback'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($r['reviewer_name']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>