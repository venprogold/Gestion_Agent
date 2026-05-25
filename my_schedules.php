<?php
require_once 'config/database.php';
requireRole('agent');

$database = new Database();
$db = $database->getConnection();
$statusLabels = [
    'planifie' => 'Planifié',
    'en_cours' => 'En cours',
    'termine' => 'Terminé',
    'annule' => 'Annulé'
];
$unreadCount = getUnreadNotificationsCount($db, $_SESSION['user_id']);

// Récupérer les plannings de l'agent connecté
$query = "SELECT * FROM schedules 
          WHERE agent_id = :agent_id 
          ORDER BY start_datetime DESC";
$stmt = $db->prepare($query);
$stmt->execute([':agent_id' => $_SESSION['user_id']]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les plannings à venir
$upcomingQuery = "SELECT * FROM schedules 
                  WHERE agent_id = :agent_id 
                  AND start_datetime > NOW() 
                  AND status != 'annule'
                  ORDER BY start_datetime ASC 
                  LIMIT 5";
$upcomingStmt = $db->prepare($upcomingQuery);
$upcomingStmt->execute([':agent_id' => $_SESSION['user_id']]);
$upcomingSchedules = $upcomingStmt->fetchAll(PDO::FETCH_ASSOC);

// Mettre à jour le statut d'un planning (pour l'agent)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $schedule_id = $_POST['schedule_id'];
    $status = $_POST['status'];
    
    $updateQuery = "UPDATE schedules SET status = :status WHERE id = :id AND agent_id = :agent_id";
    $updateStmt = $db->prepare($updateQuery);
    $updateStmt->execute([
        ':status' => $status,
        ':id' => $schedule_id,
        ':agent_id' => $_SESSION['user_id']
    ]);
    
    $_SESSION['message'] = "Statut mis à jour !";
    header("Location: my_schedules.php");
    exit();
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes horaires - Agent</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .schedule-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .schedule-card:hover {
            transform: translateY(-2px);
        }
        .schedule-card.upcoming {
            border-left: 4px solid #667eea;
        }
        .schedule-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .status-planifie { background: #2196F3; color: white; }
        .status-en_cours { background: #FF9800; color: white; }
        .status-termine { background: #4CAF50; color: white; }
        .status-annule { background: #F44336; color: white; }
        .empty-state {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <nav class="navbar">
            <h1>🧹 Mes horaires de travail</h1>
            <div class="nav-links">
                <a href="agent_dashboard.php">Dashboard</a>
                <a href="my_schedules.php" class="active">Mes horaires</a>
                <a href="notifications.php">🔔 Notifications</a>
                <a href="profile.php">Mon profil</a>
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
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <div class="welcome-card">
                <h2>📅 Vos prochains plannings</h2>
                <p>Voici les tâches qui vous sont assignées prochainement.</p>
            </div>
            
            <?php if (empty($upcomingSchedules)): ?>
                <div class="empty-state">
                    <p>🎉 Aucun planning à venir pour le moment.</p>
                    <p>Vous serez notifié quand des tâches vous seront assignées.</p>
                </div>
            <?php else: ?>
                <?php foreach ($upcomingSchedules as $schedule): ?>
                    <div class="schedule-card upcoming">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <h3><?php echo htmlspecialchars($schedule['title']); ?></h3>
                                <span class="schedule-status status-<?php echo $schedule['status']; ?>">
                                    <?php 
                                    $statusLabels = ['planifie' => 'Planifié', 'en_cours' => 'En cours', 'termine' => 'Terminé', 'annule' => 'Annulé'];
                                    echo $statusLabels[$schedule['status']];
                                    ?>
                                </span>
                            </div>
                            <div style="font-size: 0.9em; color: #667eea;">
                                📅 <?php echo date('d/m/Y', strtotime($schedule['start_datetime'])); ?>
                            </div>
                        </div>
                        
                        <p style="margin-top: 15px;">
                            <strong>⏰ Horaires :</strong> 
                            <?php echo date('H:i', strtotime($schedule['start_datetime'])); ?> - 
                            <?php echo date('H:i', strtotime($schedule['end_datetime'])); ?>
                        </p>
                        
                        <?php if ($schedule['description']): ?>
                            <p><strong>📝 Description :</strong> <?php echo nl2br(htmlspecialchars($schedule['description'])); ?></p>
                        <?php endif; ?>
                        
                        <form method="POST" style="margin-top: 15px;">
                            <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                            <input type="hidden" name="action" value="update_status">
                            <select name="status" onchange="this.form.submit()" style="padding: 8px; border-radius: 5px;">
                                <option value="planifie" <?php echo $schedule['status'] == 'planifie' ? 'selected' : ''; ?>>📋 Planifié</option>
                                <option value="en_cours" <?php echo $schedule['status'] == 'en_cours' ? 'selected' : ''; ?>>⚙️ En cours</option>
                                <option value="termine" <?php echo $schedule['status'] == 'termine' ? 'selected' : ''; ?>>✅ Terminé</option>
                            </select>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <h2 style="margin-top: 40px;">📋 Historique complet</h2>
            
            <?php if (empty($schedules)): ?>
                <div class="empty-state">
                    <p>Aucun historique de planning.</p>
                </div>
            <?php else: ?>
                <?php foreach ($schedules as $schedule): ?>
                    <div class="schedule-card">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <h3><?php echo htmlspecialchars($schedule['title']); ?></h3>
                            <span class="schedule-status status-<?php echo $schedule['status']; ?>">
                                <?php echo $statusLabels[$schedule['status']]; ?>
                            </span>
                        </div>
                        <p><strong>📅 Date :</strong> <?php echo date('d/m/Y', strtotime($schedule['start_datetime'])); ?></p>
                        <p><strong>⏰ Horaires :</strong> <?php echo date('H:i', strtotime($schedule['start_datetime'])); ?> - <?php echo date('H:i', strtotime($schedule['end_datetime'])); ?></p>
                        <?php if ($schedule['description']): ?>
                            <p><strong>📝 Description :</strong> <?php echo nl2br(htmlspecialchars($schedule['description'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>