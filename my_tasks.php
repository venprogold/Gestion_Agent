<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
requireRole('agent');

$database = new Database();
$db = $database->getConnection();
$agent_id = $_SESSION['user_id'];

// Fonction pour ajouter une notification
function addNotification($db, $user_id, $type, $title, $message, $link = null) {
    $stmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $type, $title, $message, $link]);
}

// Récupérer les tâches de l'agent
$tasksStmt = $db->prepare("SELECT t.*, u.username as assigned_by_name 
                           FROM tasks t 
                           JOIN users u ON t.assigned_by = u.id 
                           WHERE t.assigned_to = ? 
                           ORDER BY 
                               CASE WHEN t.status = 'en_retard' THEN 1 
                                    WHEN t.status = 'en_cours' THEN 2
                                    WHEN t.status = 'a_faire' THEN 3
                                    ELSE 4 END,
                               t.due_date ASC");
$tasksStmt->execute([$agent_id]);
$tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les commentaires
$comments = [];
if (!empty($tasks)) {
    $taskIds = array_column($tasks, 'id');
    $placeholders = implode(',', array_fill(0, count($taskIds), '?'));
    $commentsStmt = $db->prepare("SELECT tc.*, u.username 
                                  FROM task_comments tc 
                                  JOIN users u ON tc.user_id = u.id 
                                  WHERE tc.task_id IN ($placeholders) 
                                  ORDER BY tc.created_at DESC");
    $commentsStmt->execute($taskIds);
    $allComments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allComments as $comment) {
        $comments[$comment['task_id']][] = $comment;
    }
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Mise à jour du statut
    if ($action === 'update_status' && isset($_POST['task_id'])) {
        $tid = $_POST['task_id'];
        $status = $_POST['status'];
        $completed_date = ($status === 'terminee') ? date('Y-m-d') : null;
        $actual_hours = isset($_POST['actual_hours']) ? floatval($_POST['actual_hours']) : null;
        
        $updateStmt = $db->prepare("UPDATE tasks SET status = ?, completed_date = ?, actual_hours = ? WHERE id = ? AND assigned_to = ?");
        $updateStmt->execute([$status, $completed_date, $actual_hours, $tid, $agent_id]);
        
        // Récupérer les infos pour la notification
        $taskStmt = $db->prepare("SELECT assigned_by, title FROM tasks WHERE id = ?");
        $taskStmt->execute([$tid]);
        $task = $taskStmt->fetch(PDO::FETCH_ASSOC);
        
        // 👇 NOTIFICATION POUR LE RESPONSABLE (LIEN = tasks.php)
        $statusLabels = ['a_faire' => 'À faire', 'en_cours' => 'En cours', 'terminee' => 'Terminée'];
        addNotification($db, $task['assigned_by'], 'status_changed', 'Statut de tâche modifié',
            "L'agent a changé le statut de « {$task['title']} » en « {$statusLabels[$status]} »." . ($actual_hours ? " ({$actual_hours}h effectuées)" : ""),
            'tasks.php');
        
        $_SESSION['message'] = "Statut mis à jour !";
        header("Location: my_tasks.php");
        exit();
    }
    
    // Ajout d'un commentaire
    elseif ($action === 'add_comment' && isset($_POST['task_id'])) {
        $tid = $_POST['task_id'];
        $comment = trim($_POST['comment']);
        
        if (!empty($comment)) {
            $insertStmt = $db->prepare("INSERT INTO task_comments (task_id, user_id, comment) VALUES (?, ?, ?)");
            $insertStmt->execute([$tid, $agent_id, $comment]);
            
            // Récupérer le responsable pour la notification
            $taskStmt = $db->prepare("SELECT assigned_by, title FROM tasks WHERE id = ?");
            $taskStmt->execute([$tid]);
            $task = $taskStmt->fetch(PDO::FETCH_ASSOC);
            
            // 👇 NOTIFICATION POUR LE RESPONSABLE (LIEN = tasks.php)
            addNotification($db, $task['assigned_by'], 'comment_added', 'Nouveau commentaire',
                "Sur la tâche « {$task['title']} » : " . substr($comment, 0, 100) . (strlen($comment) > 100 ? '...' : ''),
                'tasks.php');
            
            $_SESSION['message'] = "Commentaire ajouté !";
        }
        header("Location: my_tasks.php");
        exit();
    }
    
    // Signaler un problème
    elseif ($action === 'report_issue' && isset($_POST['task_id'])) {
        $tid = $_POST['task_id'];
        $issue = trim($_POST['issue']);
        
        if (!empty($issue)) {
            // Récupérer les notes actuelles
            $notesStmt = $db->prepare("SELECT notes FROM tasks WHERE id = ?");
            $notesStmt->execute([$tid]);
            $currentNotes = $notesStmt->fetchColumn();
            
            $newNotes = "[SIGNALÉ PAR AGENT - " . date('d/m/Y H:i') . "] " . $issue . "\n\n" . ($currentNotes ?: '');
            
            $updateStmt = $db->prepare("UPDATE tasks SET notes = ? WHERE id = ? AND assigned_to = ?");
            $updateStmt->execute([$newNotes, $tid, $agent_id]);
            
            // Récupérer le responsable pour la notification
            $taskStmt = $db->prepare("SELECT assigned_by, title FROM tasks WHERE id = ?");
            $taskStmt->execute([$tid]);
            $task = $taskStmt->fetch(PDO::FETCH_ASSOC);
            
            // 👇 NOTIFICATION POUR LE RESPONSABLE (LIEN = tasks.php)
            addNotification($db, $task['assigned_by'], 'task_overdue', 'Problème signalé',
                "L'agent signale un problème sur « {$task['title']} » : " . substr($issue, 0, 150),
                'tasks.php');
            
            $_SESSION['message'] = "Problème signalé au responsable !";
        }
        header("Location: my_tasks.php");
        exit();
    }
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Statistiques pour l'affichage
$total = count($tasks);
$aFaire = count(array_filter($tasks, fn($t) => $t['status'] == 'a_faire'));
$enCours = count(array_filter($tasks, fn($t) => $t['status'] == 'en_cours'));
$terminees = count(array_filter($tasks, fn($t) => $t['status'] == 'terminee'));
$enRetard = count(array_filter($tasks, fn($t) => $t['status'] == 'en_retard'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes tâches - Agent</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .task-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .task-card:hover { transform: translateX(5px); }
        .priority-haute { border-left-color: #F44336; }
        .priority-urgente { border-left-color: #9C27B0; }
        .priority-moyenne { border-left-color: #FF9800; }
        .priority-basse { border-left-color: #4CAF50; }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .status-a_faire { background: #2196F3; color: white; }
        .status-en_cours { background: #FF9800; color: white; }
        .status-terminee { background: #4CAF50; color: white; }
        .status-en_retard { background: #F44336; color: white; }
        .deadline-warning { color: #F44336; font-weight: bold; }
        .deadline-ok { color: #4CAF50; }
        .comment {
            background: #f9f9f9;
            padding: 10px;
            margin-top: 8px;
            border-radius: 5px;
            font-size: 0.9em;
        }
        .comment-author { font-weight: bold; color: #667eea; }
        .comment-date { font-size: 0.75em; color: #999; margin-left: 8px; }
        .stats-summary {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-badge {
            background: white;
            padding: 8px 16px;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            font-size: 0.9em;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }
        .close {
            float: right;
            font-size: 28px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <nav class="navbar">
        <h1>✅ Mes tâches</h1>
        <div class="nav-links">
            <a href="agent_dashboard.php">Dashboard</a>
            <a href="my_schedules.php">Mes horaires</a>
            <a href="my_tasks.php" class="active">Mes tâches</a>
            <a href="work_log.php">Mes heures</a>
            <a href="my_performance.php">Performance</a>
            <a href="notifications.php">🔔 Notifications</a>
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
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <div class="stats-summary">
            <div class="stat-badge">📊 Total: <?= $total ?></div>
            <div class="stat-badge">🔵 À faire: <?= $aFaire ?></div>
            <div class="stat-badge">🟠 En cours: <?= $enCours ?></div>
            <div class="stat-badge">✅ Terminées: <?= $terminees ?></div>
            <?php if ($enRetard > 0): ?>
                <div class="stat-badge" style="background:#F44336; color:white;">⚠️ En retard: <?= $enRetard ?></div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($tasks)): ?>
            <div class="alert alert-info" style="text-align: center; padding: 40px;">
                🎉 Aucune tâche assignée pour le moment.
            </div>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <div class="task-card priority-<?= $task['priority'] ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="font-size: 1.1em;"><?= htmlspecialchars($task['title']) ?></strong>
                            <span class="status-badge status-<?= $task['status'] ?>" style="margin-left: 10px;">
                                <?= $task['status'] ?>
                            </span>
                        </div>
                        <div>
                            <?php 
                            $priorityLabels = ['basse' => '🟢 Basse', 'moyenne' => '🟠 Moyenne', 'haute' => '🔴 Haute', 'urgente' => '⚠️ Urgente'];
                            echo $priorityLabels[$task['priority']];
                            ?>
                        </div>
                    </div>
                    
                    <div style="margin: 10px 0; color: #666; font-size: 0.9em;">
                        <span>👤 Assigné par: <?= htmlspecialchars($task['assigned_by_name']) ?></span> |
                        <span>📅 Début: <?= date('d/m/Y', strtotime($task['start_date'])) ?></span> |
                        <span>⏰ Échéance: 
                            <?= date('d/m/Y', strtotime($task['due_date'])) ?>
                            <?php if ($task['status'] != 'terminee' && strtotime($task['due_date']) < time()): ?>
                                <span class="deadline-warning">(EN RETARD!)</span>
                            <?php endif; ?>
                        </span>
                        <?php if ($task['estimated_hours']): ?>
                            | <span>⏱️ Estimé: <?= $task['estimated_hours'] ?>h</span>
                        <?php endif; ?>
                        <?php if ($task['actual_hours'] && $task['status'] == 'terminee'): ?>
                            | <span>✅ Réel: <?= $task['actual_hours'] ?>h</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($task['description']): ?>
                        <div style="background: #f9f9f9; padding: 10px; border-radius: 5px; margin: 10px 0;">
                            <strong>📝 Description:</strong><br>
                            <?= nl2br(htmlspecialchars($task['description'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($task['equipment_needed']): ?>
                        <div style="margin: 10px 0;">
                            <strong>🛠️ Équipements:</strong>
                            <?php 
                            $equipments = explode(',', $task['equipment_needed']);
                            foreach ($equipments as $eq):
                                $eq = trim($eq);
                                if ($eq):
                            ?>
                                <span style="display: inline-block; background:#e3f2fd; color:#1976d2; padding:2px 8px; border-radius:12px; margin:2px; font-size:0.85em;">
                                    <?= htmlspecialchars($eq) ?>
                                </span>
                            <?php endif; endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Commentaires -->
                    <div style="margin-top: 15px;">
                        <strong>💬 Commentaires</strong>
                        <form method="POST" style="margin-top: 8px;">
                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                            <input type="hidden" name="action" value="add_comment">
                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="comment" placeholder="Ajouter un commentaire..." style="flex: 1; padding: 8px;" required>
                                <button type="submit" class="btn-small">Envoyer</button>
                            </div>
                        </form>
                        
                        <?php if (isset($comments[$task['id']])): ?>
                            <?php foreach ($comments[$task['id']] as $comment): ?>
                                <div class="comment">
                                    <div>
                                        <span class="comment-author"><?= htmlspecialchars($comment['username']) ?></span>
                                        <span class="comment-date"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></span>
                                    </div>
                                    <div><?= nl2br(htmlspecialchars($comment['comment'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Actions -->
                    <div style="margin-top: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php if ($task['status'] != 'terminee' && $task['status'] != 'annulee'): ?>
                            <form method="POST" style="display: inline-block;" onsubmit="if(this.status.value=='terminee'){var h=prompt('Heures réelles effectuées ? (ex: 2.5)'); if(h) this.actual_hours.value=h; else return false;} return true;">
                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="actual_hours" value="">
                                <select name="status" style="padding: 8px;" required>
                                    <option value="a_faire" <?= $task['status']=='a_faire' ? 'selected' : '' ?>>📋 À faire</option>
                                    <option value="en_cours" <?= $task['status']=='en_cours' ? 'selected' : '' ?>>⚙️ En cours</option>
                                    <option value="terminee">✅ Terminée</option>
                                </select>
                                <button type="submit" class="btn-small">Mettre à jour</button>
                            </form>
                            <button class="btn-small" style="background: #FF9800;" onclick="openReportModal(<?= $task['id'] ?>, '<?= addslashes($task['title']) ?>')">
                                ⚠️ Signaler un problème
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal pour signaler un problème -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeReportModal()">&times;</span>
        <h2>⚠️ Signaler un problème</h2>
        <form method="POST">
            <input type="hidden" name="task_id" id="report_task_id">
            <input type="hidden" name="action" value="report_issue">
            <div class="form-group">
                <label>Description du problème</label>
                <textarea name="issue" rows="5" required placeholder="Décrivez précisément le problème rencontré (matériel manquant, accès impossible, planning trop serré, etc.)"></textarea>
            </div>
            <button type="submit" class="btn-primary">Envoyer au responsable</button>
        </form>
    </div>
</div>

<script>
    function openReportModal(taskId, taskTitle) {
        document.getElementById('report_task_id').value = taskId;
        document.getElementById('reportModal').style.display = 'flex';
    }
    function closeReportModal() {
        document.getElementById('reportModal').style.display = 'none';
    }
    window.onclick = function(e) {
        var modal = document.getElementById('reportModal');
        if (e.target == modal) modal.style.display = 'none';
    }
</script>
</body>
</html>