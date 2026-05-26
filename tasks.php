<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
requireRole('responsable');

$database = new Database();
$db = $database->getConnection();

// Fonction pour ajouter une notification
function addNotification($db, $user_id, $type, $title, $message, $link = null) {
    $stmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $type, $title, $message, $link]);
}

// Récupérer la liste des agents
$agents = $db->query("SELECT id, username, secteur FROM users WHERE role = 'agent' ORDER BY username")->fetchAll();

// Récupérer les statistiques
$stats = $db->query("SELECT 
    COUNT(*) as total_tasks,
    SUM(CASE WHEN status = 'terminee' THEN 1 ELSE 0 END) as completed_tasks,
    SUM(CASE WHEN status = 'en_retard' THEN 1 ELSE 0 END) as late_tasks,
    SUM(CASE WHEN status = 'a_faire' THEN 1 ELSE 0 END) as pending_tasks
    FROM tasks")->fetch(PDO::FETCH_ASSOC);

// Récupérer toutes les tâches
$tasks = $db->query("SELECT t.*, 
    u_assigned.username as assigned_to_name,
    u_assigned.secteur as assigned_secteur,
    u_assigned_by.username as assigned_by_name
    FROM tasks t
    JOIN users u_assigned ON t.assigned_to = u_assigned.id
    JOIN users u_assigned_by ON t.assigned_by = u_assigned_by.id
    ORDER BY 
        CASE WHEN t.status = 'en_retard' THEN 1 
             WHEN t.status = 'en_cours' THEN 2
             WHEN t.status = 'a_faire' THEN 3
             ELSE 4 END,
        t.due_date ASC")->fetchAll();

// Traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $stmt = $db->prepare("INSERT INTO tasks (title, description, priority, assigned_to, assigned_by, start_date, due_date, estimated_hours, notes) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['priority'],
            $_POST['assigned_to'],
            $_SESSION['user_id'],
            $_POST['start_date'],
            $_POST['due_date'],
            $_POST['estimated_hours'] ?: null,
            $_POST['notes'] ?? ''
        ]);
        $task_id = $db->lastInsertId();
        
        // 👇 NOTIFICATION POUR L'AGENT (LIEN = my_tasks.php)
        addNotification($db, $_POST['assigned_to'], 'task_assigned', 'Nouvelle tâche', 
            "Une nouvelle tâche « {$_POST['title']} » vous a été assignée (échéance : " . date('d/m/Y', strtotime($_POST['due_date'])) . ").", 
            'my_tasks.php');
        
        // 👇 NOTIFICATION POUR LES AUTRES RESPONSABLES (LIEN = tasks.php)
        $respStmt = $db->prepare("SELECT id FROM users WHERE role = 'responsable' AND id != ?");
        $respStmt->execute([$_SESSION['user_id']]);
        $responsables = $respStmt->fetchAll();
        foreach ($responsables as $resp) {
            addNotification($db, $resp['id'], 'task_assigned', 'Nouvelle tâche créée',
                "Une tâche « {$_POST['title']} » a été assignée à l'agent.",
                'tasks.php');
        }
        
        $_SESSION['message'] = "Tâche ajoutée avec succès !";
        header("Location: tasks.php");
        exit();
    }
    elseif ($action === 'delete' && isset($_POST['task_id'])) {
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$_POST['task_id']]);
        $_SESSION['message'] = "Tâche supprimée !";
        header("Location: tasks.php");
        exit();
    }
    elseif ($action === 'update_status' && isset($_POST['task_id'])) {
        $status = $_POST['status'];
        $completed_date = ($status === 'terminee') ? date('Y-m-d') : null;
        $stmt = $db->prepare("UPDATE tasks SET status = ?, completed_date = ? WHERE id = ?");
        $stmt->execute([$status, $completed_date, $_POST['task_id']]);
        
        // Récupérer l'agent et le titre pour la notification
        $taskStmt = $db->prepare("SELECT assigned_to, title FROM tasks WHERE id = ?");
        $taskStmt->execute([$_POST['task_id']]);
        $task = $taskStmt->fetch(PDO::FETCH_ASSOC);
        
        // Notification à l'agent
        $statusLabels = ['a_faire' => 'À faire', 'en_cours' => 'En cours', 'terminee' => 'Terminée', 'annulee' => 'Annulée', 'en_retard' => 'En retard'];
        addNotification($db, $task['assigned_to'], 'status_changed', 'Statut modifié',
            "Le statut de la tâche « {$task['title']} » a été changé en « {$statusLabels[$status]} ».",
            'my_tasks.php');
        
        $_SESSION['message'] = "Statut mis à jour !";
        header("Location: tasks.php");
        exit();
    }
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des tâches - Responsable</title>
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
        .status-annulee { background: #9E9E9E; color: white; }
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
            padding: 30px;
            border-radius: 10px;
            max-width: 550px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .close {
            float: right;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        .close:hover { color: #333; }
    </style>
</head>
<body>
<div class="dashboard">
    <nav class="navbar">
        <h1>✅ Gestion des tâches</h1>
        <div class="nav-links">
            <a href="responsable_dashboard.php">Dashboard</a>
            <a href="schedules.php">Horaires</a>
            <a href="tasks.php" class="active">Tâches</a>
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
        
        <div class="stats-cards">
            <div class="stat-card"><h3>Total tâches</h3><p class="stat-number"><?= $stats['total_tasks'] ?? 0 ?></p></div>
            <div class="stat-card"><h3>Terminées</h3><p class="stat-number" style="color:#4CAF50;"><?= $stats['completed_tasks'] ?? 0 ?></p></div>
            <div class="stat-card"><h3>En retard</h3><p class="stat-number" style="color:#F44336;"><?= $stats['late_tasks'] ?? 0 ?></p></div>
            <div class="stat-card"><h3>En attente</h3><p class="stat-number" style="color:#FF9800;"><?= $stats['pending_tasks'] ?? 0 ?></p></div>
        </div>
        
        <div style="text-align: right; margin-bottom: 20px;">
            <button class="btn-primary" onclick="openModal()">+ Nouvelle tâche</button>
        </div>
        
        <?php if (empty($tasks)): ?>
            <div class="alert alert-info">Aucune tâche pour le moment.</div>
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
                        <span>👤 Agent: <?= htmlspecialchars($task['assigned_to_name']) ?></span> |
                        <span>📍 Secteur: <?= htmlspecialchars($task['assigned_secteur'] ?? 'Non défini') ?></span> |
                        <span>📅 Début: <?= date('d/m/Y', strtotime($task['start_date'])) ?></span> |
                        <span>⏰ Échéance: <?= date('d/m/Y', strtotime($task['due_date'])) ?></span>
                        <?php if ($task['estimated_hours']): ?>
                            | <span>⏱️ Est.: <?= $task['estimated_hours'] ?>h</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($task['description']): ?>
                        <div style="background: #f9f9f9; padding: 10px; border-radius: 5px; margin: 10px 0;">
                            <strong>📝 Description:</strong><br>
                            <?= nl2br(htmlspecialchars($task['description'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($task['notes']): ?>
                        <div style="background: #fff3e0; padding: 10px; border-radius: 5px; margin: 10px 0;">
                            <strong>📌 Notes:</strong><br>
                            <?= nl2br(htmlspecialchars($task['notes'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 15px; display: flex; gap: 10px;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                            <input type="hidden" name="action" value="update_status">
                            <select name="status" onchange="this.form.submit()" style="padding: 5px;">
                                <option value="a_faire" <?= $task['status']=='a_faire' ? 'selected' : '' ?>>📋 À faire</option>
                                <option value="en_cours" <?= $task['status']=='en_cours' ? 'selected' : '' ?>>⚙️ En cours</option>
                                <option value="terminee" <?= $task['status']=='terminee' ? 'selected' : '' ?>>✅ Terminée</option>
                                <option value="annulee" <?= $task['status']=='annulee' ? 'selected' : '' ?>>❌ Annulée</option>
                            </select>
                        </form>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cette tâche ?')">
                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" style="background:#dc3545; color:white; border:none; padding:5px 12px; border-radius:5px; cursor:pointer;">🗑 Supprimer</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal d'ajout -->
<div id="taskModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>➕ Nouvelle tâche</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Titre *</label>
                <input type="text" name="title" required placeholder="Ex: Nettoyage des amphithéâtres">
            </div>
            <div class="form-group">
                <label>Agent assigné *</label>
                <select name="assigned_to" required>
                    <option value="">-- Sélectionner un agent --</option>
                    <?php foreach ($agents as $agent): ?>
                        <option value="<?= $agent['id'] ?>"><?= htmlspecialchars($agent['username']) ?> (<?= htmlspecialchars($agent['secteur'] ?? 'Secteur?') ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Priorité *</label>
                <select name="priority">
                    <option value="basse">Basse</option>
                    <option value="moyenne" selected>Moyenne</option>
                    <option value="haute">Haute</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date de début *</label>
                <input type="date" name="start_date" required>
            </div>
            <div class="form-group">
                <label>Date d'échéance *</label>
                <input type="date" name="due_date" required>
            </div>
            <div class="form-group">
                <label>Durée estimée (heures)</label>
                <input type="number" step="0.5" name="estimated_hours" placeholder="Ex: 2.5">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Description détaillée..."></textarea>
            </div>
            <div class="form-group">
                <label>Notes supplémentaires</label>
                <textarea name="notes" rows="2" placeholder="Informations complémentaires..."></textarea>
            </div>
            <button type="submit" class="btn-primary">Assigner la tâche</button>
        </form>
    </div>
</div>

<script>
    function openModal() { document.getElementById('taskModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('taskModal').style.display = 'none'; }
    window.onclick = function(e) { if (e.target == document.getElementById('taskModal')) closeModal(); }
</script>
</body>
</html>