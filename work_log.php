<?php
require_once 'config/database.php';
requireRole('agent');

$database = new Database();
$db = $database->getConnection();
$agent_id = $_SESSION['user_id'];

// Ajouter une entrée d’heures
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $work_date = $_POST['work_date'];
    $hours_worked = floatval($_POST['hours_worked']);
    $description = trim($_POST['description']);
    $task_id = !empty($_POST['task_id']) ? $_POST['task_id'] : null;
    
    if ($hours_worked > 0) {
        $stmt = $db->prepare("INSERT INTO work_logs (user_id, task_id, work_date, hours_worked, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$agent_id, $task_id, $work_date, $hours_worked, $description]);
        $_SESSION['message'] = "Heures de travail enregistrées.";
    } else {
        $_SESSION['error'] = "Veuillez entrer un nombre d'heures valide.";
    }
    header("Location: work_log.php");
    exit();
}

// Récupérer l'historique des heures
$logsQuery = $db->prepare("SELECT wl.*, t.title as task_title 
                           FROM work_logs wl
                           LEFT JOIN tasks t ON wl.task_id = t.id
                           WHERE wl.user_id = ?
                           ORDER BY wl.work_date DESC
                           LIMIT 50");
$logsQuery->execute([$agent_id]);
$work_logs = $logsQuery->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les tâches en cours (pour lier les heures à une tâche)
$tasksQuery = $db->prepare("SELECT id, title FROM tasks WHERE assigned_to = ? AND status != 'terminee' ORDER BY due_date");
$tasksQuery->execute([$agent_id]);
$tasks = $tasksQuery->fetchAll(PDO::FETCH_ASSOC);

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes heures de travail</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="dashboard">
    <nav class="navbar">
        <h1>⏱️ Mes heures de travail</h1>
        <div class="nav-links">
            <a href="agent_dashboard.php">Dashboard</a>
            <a href="my_tasks.php">Mes tâches</a>
            <a href="my_schedules.php">Horaires</a>
            <a href="work_log.php" class="active">Heures</a>
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
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        
        <div class="card">
            <h2>📝 Ajouter une entrée d'heures</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="work_date" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label>Heures travaillées</label>
                    <input type="number" step="0.5" name="hours_worked" required placeholder="Ex: 3.5">
                </div>
                <div class="form-group">
                    <label>Tâche associée (optionnel)</label>
                    <select name="task_id">
                        <option value="">-- Aucune --</option>
                        <?php foreach ($tasks as $task): ?>
                            <option value="<?= $task['id'] ?>"><?= htmlspecialchars($task['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Description (ce qui a été fait)</label>
                    <textarea name="description" rows="2" placeholder="Ex: Nettoyage des vitres bâtiment A"></textarea>
                </div>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </form>
        </div>
        
        <h2 style="margin-top: 30px;">📋 Historique des heures</h2>
        <?php if (empty($work_logs)): ?>
            <div class="empty-state">Aucune heure enregistrée pour le moment.</div>
        <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Date</th><th>Heures</th><th>Tâche</th><th>Description</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($work_logs as $log): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($log['work_date'])) ?></td>
                        <td><?= $log['hours_worked'] ?> h</td>
                        <td><?= htmlspecialchars($log['task_title'] ?? '-') ?></td>
                        <td><?= nl2br(htmlspecialchars($log['description'] ?? '')) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>