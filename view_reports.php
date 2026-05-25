<?php
require_once 'config/database.php';
requireRole('responsable');

$database = new Database();
$db = $database->getConnection();

// Récupérer tous les rapports avec les infos agent
$reports = $db->query("SELECT wr.*, u.username, u.secteur 
                       FROM work_reports wr
                       JOIN users u ON wr.user_id = u.id
                       ORDER BY wr.created_at DESC")->fetchAll();

// Traitement : approuver/rejeter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'])) {
    $report_id = $_POST['report_id'];
    $action = $_POST['action'];
    $admin_comment = trim($_POST['admin_comment'] ?? '');
    
    $new_status = ($action === 'approve') ? 'approved' : 'rejected';
    
    $stmt = $db->prepare("UPDATE work_reports SET status = ?, admin_comment = ? WHERE id = ?");
    $stmt->execute([$new_status, $admin_comment, $report_id]);
    
    // Notifier l'agent
    $reportStmt = $db->prepare("SELECT user_id, title FROM work_reports WHERE id = ?");
    $reportStmt->execute([$report_id]);
    $report = $reportStmt->fetch();
    
    $notifStmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'report_reviewed', 'Rapport traité', ?, 'my_reports.php')");
    $notifStmt->execute([$report['user_id'], "Votre rapport « {$report['title']} » a été {$new_status}."]);
    
    header("Location: view_reports.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des rapports - Responsable</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .report-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-submitted { background: #FF9800; color: white; }
        .status-approved { background: #4CAF50; color: white; }
        .status-rejected { background: #F44336; color: white; }
        .media-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 15px 0;
        }
        .media-item {
            width: 100px;
            height: 100px;
            border-radius: 5px;
            overflow: hidden;
            cursor: pointer;
        }
        .media-item img, .media-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .admin-action {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .btn-approve { background: #4CAF50; }
        .btn-reject { background: #F44336; }
    </style>
</head>
<body>
<div class="dashboard">
    <nav class="navbar">
        <h1>📊 Rapports des agents</h1>
        <div class="nav-links">
            <a href="responsable_dashboard.php">Dashboard</a>
            <a href="schedules.php">Horaires</a>
            <a href="tasks.php">Tâches</a>
            <a href="view_reports.php" class="active">Rapports</a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </nav>
    <div class="content">
        <?php if (empty($reports)): ?>
            <div class="alert alert-info">Aucun rapport soumis pour le moment.</div>
        <?php else: ?>
            <?php foreach ($reports as $report): ?>
                <div class="report-card">
                    <div style="display: flex; justify-content: space-between;">
                        <h3><?= htmlspecialchars($report['title']) ?></h3>
                        <span class="report-status status-<?= $report['status'] ?>">
                            <?= ['submitted'=>'📤 Soumis', 'approved'=>'✅ Approuvé', 'rejected'=>'❌ Rejeté'][$report['status']] ?>
                        </span>
                    </div>
                    <div><strong>Agent :</strong> <?= htmlspecialchars($report['username']) ?> | <strong>Secteur :</strong> <?= htmlspecialchars($report['secteur'] ?? 'Non défini') ?></div>
                    <div><strong>Date travail :</strong> <?= date('d/m/Y', strtotime($report['work_date'])) ?> | <strong>Heures :</strong> <?= $report['hours_spent'] ?? '-' ?></div>
                    <div><strong>Lieu :</strong> <?= htmlspecialchars($report['location'] ?? '-') ?></div>
                    <p><strong>Description :</strong><br><?= nl2br(htmlspecialchars($report['description'])) ?></p>
                    
                    <?php
                    $mediaStmt = $db->prepare("SELECT * FROM report_media WHERE report_id = ?");
                    $mediaStmt->execute([$report['id']]);
                    $media = $mediaStmt->fetchAll();
                    if ($media): ?>
                        <div class="media-gallery">
                            <?php foreach ($media as $m): ?>
                                <div class="media-item" onclick="window.open('<?= $m['file_path'] ?>', '_blank')">
                                    <?php if ($m['file_type'] === 'image'): ?>
                                        <img src="<?= $m['file_path'] ?>" alt="Photo">
                                    <?php else: ?>
                                        <video src="<?= $m['file_path'] ?>"></video>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($report['status'] === 'submitted'): ?>
                        <div class="admin-action">
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                <input type="hidden" name="action" value="approve">
                                <textarea name="admin_comment" rows="2" placeholder="Commentaire (optionnel)" style="width: 100%; margin-bottom: 10px;"></textarea>
                                <button type="submit" class="btn-small btn-approve">✅ Approuver</button>
                            </form>
                            <form method="POST" style="display: inline-block; margin-left: 10px;">
                                <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                <input type="hidden" name="action" value="reject">
                                <textarea name="admin_comment" rows="2" placeholder="Motif du rejet" style="width: 100%; margin-bottom: 10px;" required></textarea>
                                <button type="submit" class="btn-small btn-reject">❌ Rejeter</button>
                            </form>
                        </div>
                    <?php elseif ($report['admin_comment']): ?>
                        <div style="background: #e3f2fd; padding: 10px; border-radius: 5px; margin-top: 10px;">
                            <strong>📝 Commentaire :</strong><br><?= nl2br(htmlspecialchars($report['admin_comment'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>