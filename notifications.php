<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Marquer une notification comme lue
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $update = "UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($update);
    $stmt->execute([':id' => $_GET['mark_read'], ':user_id' => $user_id]);
    header("Location: notifications.php");
    exit();
}

// Marquer toutes comme lues
if (isset($_GET['mark_all_read'])) {
    $update = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id";
    $stmt = $db->prepare($update);
    $stmt->execute([':user_id' => $user_id]);
    header("Location: notifications.php");
    exit();
}

// Récupérer les notifications (30 dernières)
$query = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 30";
$stmt = $db->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unreadCount = count(array_filter($notifications, fn($n) => !$n['is_read']));

// Fonction pour déterminer le texte du lien selon le rôle et le type
function getLinkText($link, $user_role) {
    if (empty($link)) return null;
    
    // Si c'est déjà un chemin correct, on le garde
    if (strpos($link, '.php') !== false) {
        return $link;
    }
    
    // Sinon, on détermine selon le rôle
    if ($user_role === 'responsable') {
        return 'tasks.php';
    } else {
        return 'my_tasks.php';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes notifications</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .notification-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .notification-item.unread {
            background: #e8f0fe;
            border-left-color: #F44336;
        }
        .notification-title {
            font-weight: bold;
            font-size: 1.1em;
        }
        .notification-message {
            margin: 8px 0;
            color: #555;
        }
        .notification-date {
            font-size: 0.8em;
            color: #999;
        }
        .btn-small {
            display: inline-block;
            padding: 5px 12px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.85em;
            margin-top: 8px;
        }
        .btn-small:hover {
            background: #5a67d8;
        }
        .mark-read {
            float: right;
            color: #667eea;
            text-decoration: none;
            font-size: 0.85em;
        }
        .mark-read:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <nav class="navbar">
            <h1>🔔 Mes notifications</h1>
            <div class="nav-links">
                <?php if ($user_role === 'responsable'): ?>
                    <a href="responsable_dashboard.php">Dashboard</a>
                    <a href="schedules.php">Horaires</a>
                    <a href="tasks.php">Tâches</a>
                <?php else: ?>
                    <a href="agent_dashboard.php">Dashboard</a>
                    <a href="my_schedules.php">Mes horaires</a>
                    <a href="my_tasks.php">Mes tâches</a>
                <?php endif; ?>
                <a href="notifications.php" class="active">Notifications (<?php echo $unreadCount; ?>)</a>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>📬 Centre de notifications</h2>
                <?php if ($unreadCount > 0): ?>
                    <a href="?mark_all_read=1" class="btn-small">✓ Tout marquer comme lu</a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($notifications)): ?>
                <div class="alert alert-info" style="text-align: center; padding: 40px;">
                    ✨ Aucune notification pour le moment.
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <div class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <span class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></span>
                            <span class="notification-date"><?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?></span>
                        </div>
                        <div class="notification-message">
                            <?php echo nl2br(htmlspecialchars($notif['message'])); ?>
                        </div>
                        <div style="margin-top: 10px;">
                            <?php if ($notif['link'] && !$notif['is_read']): ?>
                                <a href="<?php echo htmlspecialchars($notif['link']); ?>" class="btn-small">👁️ Voir la tâche</a>
                            <?php endif; ?>
                            <?php if (!$notif['is_read']): ?>
                                <a href="?mark_read=<?php echo $notif['id']; ?>" class="mark-read">✓ Marquer comme lu</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>