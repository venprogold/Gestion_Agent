<?php
require_once 'config/database.php';
requireRole('agent');

$database = new Database();
$db = $database->getConnection();

$unreadCount = getUnreadNotificationsCount($db, $_SESSION['user_id']);
$profilePhoto = getUserProfilePhoto($db, $_SESSION['user_id']);

// Récupérer les tâches récentes (non terminées)
$recentTasksQuery = "SELECT * FROM tasks 
                     WHERE assigned_to = :agent_id 
                     AND status != 'terminee' 
                     ORDER BY due_date ASC 
                     LIMIT 5";
$recentTasksStmt = $db->prepare($recentTasksQuery);
$recentTasksStmt->execute([':agent_id' => $_SESSION['user_id']]);
$recentTasks = $recentTasksStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Agent</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="dashboard">
     <nav class="navbar">
    <h1>🧹 Panel Agent d'entretien</h1>
    <div class="nav-links">
        <img src="<?= $profilePhoto ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 10px;" onerror="this.src='images/default-avatar.png'">
        <span>👋 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="agent_dashboard.php" class="active">Dashboard</a>
        <a href="my_schedules.php">Mes horaires</a>
        <a href="my_tasks.php">Mes tâches</a>
        <a href="submit_report.php">📝 Soumettre rapport</a>
        <a href="my_reports.php">📋 Mes rapports</a>
        <a href="messages.php">💬 Messages
            <?php 
            $unreadMsgCount = getUnreadMessagesCount($db, $_SESSION['user_id']);
            if ($unreadMsgCount > 0): ?>
                <span style="background:red; color:white; border-radius:50%; padding:2px 6px;"><?= $unreadMsgCount ?></span>
            <?php endif; ?>
        </a>
        <a href="notifications.php">🔔 Notifications
            <?php if ($unreadCount > 0): ?>
                <span style="background:red; color:white; border-radius:50%; padding:2px 6px;"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a href="profile.php">Mon profil</a>
        <a href="logout.php">Déconnexion</a>
    </div>
</nav>
        
        <div class="content">
            <div class="welcome-card">
                <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> !</h2>
                <p>Vous êtes connecté en tant qu'<strong>agent d'entretien</strong>.</p>
                <p>Secteur : <?php echo htmlspecialchars($_SESSION['user_secteur'] ?? 'Non défini'); ?></p>
            </div>
            
            <div class="stats-cards">
                <div class="stat-card">
                    <h3>Mes tâches en cours</h3>
                    <p class="stat-number">
                        <?php 
                        $enCours = array_filter($recentTasks, function($t) { return $t['status'] == 'en_cours'; });
                        echo count($enCours);
                        ?>
                    </p>
                </div>
                <div class="stat-card">
                    <h3>Tâches à faire</h3>
                    <p class="stat-number">
                        <?php 
                        $aFaire = array_filter($recentTasks, function($t) { return $t['status'] == 'a_faire'; });
                        echo count($aFaire);
                        ?>
                    </p>
                </div>
                <div class="stat-card">
                    <h3>Prochain délai</h3>
                    <p class="stat-number">
                        <?php 
                        if (!empty($recentTasks)) {
                            $plusProche = min(array_column($recentTasks, 'due_date'));
                            echo date('d/m', strtotime($plusProche));
                        } else {
                            echo '-';
                        }
                        ?>
                    </p>
                </div>
            </div>
            
            <div class="info-box">
                <h3>📌 Tâches récentes</h3>
                <?php if (empty($recentTasks)): ?>
                    <p>Aucune tâche active pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($recentTasks as $task): ?>
                        <div style="border-bottom: 1px solid #eee; padding: 10px 0;">
                            <strong><?php echo htmlspecialchars($task['title']); ?></strong>
                            <span style="float: right;">
                                <?php 
                                $statusLabels = ['a_faire' => '📋 À faire', 'en_cours' => '⚙️ En cours', 'en_retard' => '⚠️ En retard'];
                                echo $statusLabels[$task['status']];
                                ?>
                            </span>
                            <br>
                            <small>Échéance: <?php echo date('d/m/Y', strtotime($task['due_date'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                    <div style="margin-top: 15px; text-align: center;">
                        <a href="my_tasks.php" class="btn-small">Voir toutes mes tâches →</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="info-box">
                <h3>📅 Prochains plannings</h3>
                <p><em>Consultez <a href="my_schedules.php">mes horaires</a> pour voir vos prochaines sessions de travail.</em></p>
            </div>
        </div>
    </div>
</body>
</html>