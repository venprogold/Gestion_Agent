<?php
require_once 'config/database.php';
requireRole('responsable');

$database = new Database();
$db = $database->getConnection();
$responsable_id = $_SESSION['user_id'];

// Récupérer la liste des agents
$agents = $db->query("SELECT id, username, secteur FROM users WHERE role = 'agent' ORDER BY username")->fetchAll();

// Ajouter / modifier une évaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_review') {
        $agent_id = $_POST['agent_id'];
        $period_start = $_POST['period_start'];
        $period_end = $_POST['period_end'];
        $review_date = $_POST['review_date'];
        $overall = $_POST['overall_rating'];
        $quality = $_POST['quality_rating'];
        $punctuality = $_POST['punctuality_rating'];
        $comments = trim($_POST['comments']);
        $feedback = trim($_POST['feedback']);
        
        $stmt = $db->prepare("INSERT INTO performance_reviews 
            (agent_id, reviewer_id, review_date, period_start, period_end, overall_rating, quality_rating, punctuality_rating, comments, feedback)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$agent_id, $responsable_id, $review_date, $period_start, $period_end, $overall, $quality, $punctuality, $comments, $feedback]);
        
        // Notifier l'agent
        $notifStmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'performance', 'Nouvelle évaluation', ?, '/my_performance.php')");
        $notifStmt->execute([$agent_id, "Une nouvelle évaluation de performance est disponible pour la période du $period_start au $period_end."]);
        
        $_SESSION['message'] = "Évaluation ajoutée.";
        header("Location: performance.php");
        exit();
    }
}

// Récupérer toutes les évaluations avec noms
$reviews = $db->query("SELECT pr.*, u.username as agent_name 
                       FROM performance_reviews pr
                       JOIN users u ON pr.agent_id = u.id
                       ORDER BY pr.review_date DESC")->fetchAll();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des performances</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .rating-badge { padding: 2px 8px; border-radius: 12px; background: #4CAF50; color: white; display: inline-block; }
        .rating-low { background: #F44336; }
        .rating-medium { background: #FF9800; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="dashboard">
    <nav class="navbar">
        <h1>📊 Performances des agents</h1>
        <div class="nav-links">
            <a href="responsable_dashboard.php">Dashboard</a>
            <a href="schedules.php">Horaires</a>
            <a href="tasks.php">Tâches</a>
            <a href="performance.php" class="active">Performances</a>
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
        <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        
        <!-- Bouton pour ajouter une évaluation -->
        <div style="text-align: right; margin-bottom: 20px;">
            <button class="btn-primary" onclick="openReviewModal()">➕ Nouvelle évaluation</button>
        </div>
        
        <h2>📋 Historique des évaluations</h2>
        <?php if (empty($reviews)): ?>
            <div class="empty-state">Aucune évaluation pour le moment.</div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Agent</th><th>Période</th><th>Note générale</th><th>Qualité</th><th>Ponctualité</th><th>Commentaires</th><th>Date évaluation</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['agent_name']) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['period_start'])) ?> - <?= date('d/m/Y', strtotime($r['period_end'])) ?></td>
                        <td><span class="rating-badge <?= $r['overall_rating']<5?'rating-low':($r['overall_rating']<8?'rating-medium':'') ?>"><?= $r['overall_rating'] ?>/10</span></td>
                        <td><?= $r['quality_rating'] ?>/10</td>
                        <td><?= $r['punctuality_rating'] ?>/10</td>
                        <td><?= nl2br(htmlspecialchars($r['comments'] ?? '')) ?></td>
                        <td><?= date('d/m/Y', strtotime($r['review_date'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour ajouter une évaluation -->
<div id="reviewModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2>Évaluer un agent</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add_review">
            <div class="form-group">
                <label>Agent</label>
                <select name="agent_id" required>
                    <option value="">-- Choisir --</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= $agent['id'] ?>"><?= htmlspecialchars($agent['username']) ?> (<?= htmlspecialchars($agent['secteur'] ?? 'secteur?') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Période du</label>
                <input type="date" name="period_start" required>
            </div>
            <div class="form-group">
                <label>au</label>
                <input type="date" name="period_end" required>
            </div>
            <div class="form-group">
                <label>Date de l'évaluation</label>
                <input type="date" name="review_date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label>Note générale (0-10)</label>
                <input type="number" step="0.5" name="overall_rating" min="0" max="10" required>
            </div>
            <div class="form-group">
                <label>Qualité du travail (0-10)</label>
                <input type="number" step="0.5" name="quality_rating" min="0" max="10" required>
            </div>
            <div class="form-group">
                <label>Ponctualité (0-10)</label>
                <input type="number" step="0.5" name="punctuality_rating" min="0" max="10" required>
            </div>
            <div class="form-group">
                <label>Commentaires</label>
                <textarea name="comments" rows="3" placeholder="Points forts / axes d'amélioration..."></textarea>
            </div>
            <div class="form-group">
                <label>Feedbacks constructifs</label>
                <textarea name="feedback" rows="3" placeholder="Suggestions pour progresser..."></textarea>
            </div>
            <button type="submit" class="btn-primary">Enregistrer l'évaluation</button>
        </form>
    </div>
</div>

<script>
    function openReviewModal() { document.getElementById('reviewModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('reviewModal').style.display = 'none'; }
    window.onclick = function(e) { let m = document.getElementById('reviewModal'); if (e.target == m) m.style.display = 'none'; }
</script>
</body>
</html>