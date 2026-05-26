<?php
 require_once 'config/database.php';
 requireRole('agent');

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Récupérer les rapports de l'agent
$reports = $db->prepare("SELECT * FROM work_reports WHERE user_id = ? ORDER BY created_at DESC");
$reports->execute([$user_id]);
$reports = $reports->fetchAll();

//Récupérer les médias pour chaque rapport
$media = [];
foreach ($reports as $r) {
    $mediaStmt = $db->prepare("SELECT * FROM report_media WHERE report_id = ?");
    $mediaStmt->execute([$r['id']]);
    $media[$r['id']] = $mediaStmt->fetchAll();
}
?>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes rapports - Agent</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .report-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .report-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 0.8em;
        }
        .status-submitted { background: #FF9800; color: white; }
        .status-approved { background: #4CAF50; color: white; }
        .status-rejected { background: #F44336; color: white; }
        .media-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        .media-item {
            width: 120px;
            height: 120px;
            border-radius: 5px;
            overflow: hidden;
            cursor: pointer;
        }
        .media-item img, .media-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            max-width: 90%;
            max-height: 90%;
        }
        .modal-content img, .modal-content video {
            max-width: 100%;
            max-height: 90vh;
        }
        .close-modal {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <nav class="navbar">
        <h1>📋 Mes rapports de travail</h1>
        <div class="nav-links">
            <a href="agent_dashboard.php">Dashboard</a>
            <a href="my_tasks.php">Mes tâches</a>
            <a href="submit_report.php">Nouveau rapport</a>
            <a href="my_reports.php" class="active">Mes rapports</a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </nav>
    <div class="content">
        <?php if (empty($reports)): ?>
            <div class="alert alert-info">Vous n'avez pas encore soumis de rapport. <a href="submit_report.php">Créer un rapport</a></div>
        <?php else: ?>
            <?php foreach ($reports as $report): ?>
                <div class="report-card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3><?= htmlspecialchars($report['title']) ?></h3>
                        <span class="report-status status-<?= $report['status'] ?>">
                            <?php
                            $statusLabels = ['submitted' => 'Soumis', 'approved' => 'Approuvé', 'rejected' => 'Rejeté'];
                            echo $statusLabels[$report['status']];
                            ?>
                        </span>
                    </div>
                    <div style="color: #666; font-size: 0.9em; margin: 10px 0;">
                        📅 <?= date('d/m/Y', strtotime($report['work_date'])) ?>
                        <?php if ($report['hours_spent']): ?> | ⏱️ <?= $report['hours_spent'] ?> heures <?php endif; ?>
                        <?php if ($report['location']): ?> | 📍 <?= htmlspecialchars($report['location']) ?> <?php endif; ?>
                    </div>
                    <p><strong>Description :</strong><br><?= nl2br(htmlspecialchars($report['description'])) ?></p>
                    
                    <?php if (!empty($media[$report['id']])): ?>
                        <div class="media-gallery">
                            <?php foreach ($media[$report['id']] as $m): ?>
                                <div class="media-item" onclick="openMedia('<?= $m['file_path'] ?>', '<?= $m['file_type'] ?>')">
                                    <?php if ($m['file_type'] === 'image'): ?>
                                        <img src="<?= $m['file_path'] ?>" alt="Photo">
                                    <?php else: ?>
                                        <video src="<?= $m['file_path'] ?>"></video>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($report['admin_comment']): ?>
                        <div style="background: #e3f2fd; padding: 10px; border-radius: 5px; margin-top: 10px;">
                            <strong>💬 Commentaire du responsable :</strong><br>
                            <?= nl2br(htmlspecialchars($report['admin_comment'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 15px; font-size: 0.85em; color: #999;">
                        Soumis le : <?= date('d/m/Y H:i', strtotime($report['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour afficher les médias -->
<div id="mediaModal" class="modal">
    <span class="close-modal" onclick="closeMedia()">&times;</span>
    <div class="modal-content" id="modalContent"></div>
</div>

<script>
function openMedia(path, type) {
    const modal = document.getElementById('mediaModal');
    const content = document.getElementById('modalContent');
    if (type === 'image') {
        content.innerHTML = '<img src="' + path + '">';
    } else {
        content.innerHTML = '<video src="' + path + '" controls autoplay style="max-width:100%; max-height:90vh;"></video>';
    }
    modal.style.display = 'flex';
}

function closeMedia() {
    document.getElementById('mediaModal').style.display = 'none';
    document.getElementById('modalContent').innerHTML = '';
}
</script>
</body>
</html>