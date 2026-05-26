<?php
require_once 'config/database.php';
requireRole('responsable');

$database = new Database();
$db = $database->getConnection();
$unreadCount = getUnreadNotificationsCount($db, $_SESSION['user_id']);

// Récupérer la liste des agents
$agentsQuery = "SELECT id, username, secteur FROM users WHERE role = 'agent' ORDER BY username";
$agentsStmt = $db->prepare($agentsQuery);
$agentsStmt->execute();
$agents = $agentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les événements pour le calendrier
$events = [];
$schedulesQuery = "SELECT s.*, u.username as agent_name, u.secteur 
                   FROM schedules s 
                   JOIN users u ON s.agent_id = u.id 
                   ORDER BY s.start_datetime DESC";
$schedulesStmt = $db->prepare($schedulesQuery);
$schedulesStmt->execute();
$schedules = $schedulesStmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $agent_id = $_POST['agent_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $start_datetime = $_POST['start_datetime'];
        $end_datetime = $_POST['end_datetime'];
        $created_by = $_SESSION['user_id'];
        
        $insertQuery = "INSERT INTO schedules (agent_id, title, description, start_datetime, end_datetime, created_by) 
                        VALUES (:agent_id, :title, :description, :start_datetime, :end_datetime, :created_by)";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([
            ':agent_id' => $agent_id,
            ':title' => $title,
            ':description' => $description,
            ':start_datetime' => $start_datetime,
            ':end_datetime' => $end_datetime,
            ':created_by' => $created_by
        ]);
        
        $_SESSION['message'] = "Planning ajouté avec succès !";
        header("Location: schedules.php");
        exit();
    }
    
    elseif ($_POST['action'] === 'delete' && isset($_POST['schedule_id'])) {
        $deleteQuery = "DELETE FROM schedules WHERE id = :id";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->execute([':id' => $_POST['schedule_id']]);
        
        $_SESSION['message'] = "Planning supprimé !";
        header("Location: schedules.php");
        exit();
    }
    
    elseif ($_POST['action'] === 'update_status' && isset($_POST['schedule_id'])) {
        $status = $_POST['status'];
        $updateQuery = "UPDATE schedules SET status = :status WHERE id = :id";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([':status' => $status, ':id' => $_POST['schedule_id']]);
        
        $_SESSION['message'] = "Statut mis à jour !";
        header("Location: schedules.php");
        exit();
    }
}

// Récupérer le message si existant
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des horaires - Responsable</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/fr.js"></script>
    <style>
        .calendar-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .close {
            cursor: pointer;
            font-size: 28px;
            font-weight: bold;
            color: #999;
        }
        .close:hover {
            color: #333;
        }
        .schedule-item {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
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
        .btn-small-danger {
            background: #dc3545;
            color: white;
        }
        .btn-small-danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <nav class="navbar">
            <h1>📅 Gestion des horaires</h1>
            <div class="nav-links">
                <a href="responsable_dashboard.php">Dashboard</a>
                <a href="notifications.php">🔔 Notifications</a>
                <a href="schedules.php" class="active">Horaires</a>
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
            
            <div class="stats-cards">
                <div class="stat-card">
                    <h3>Total plannings</h3>
                    <p class="stat-number"><?php echo count($schedules); ?></p>
                </div>
                <div class="stat-card">
                    <h3>En cours</h3>
                    <p class="stat-number">
                        <?php 
                        $enCours = array_filter($schedules, function($s) { return $s['status'] === 'en_cours'; });
                        echo count($enCours);
                        ?>
                    </p>
                </div>
                <div class="stat-card">
                    <h3>À venir</h3>
                    <p class="stat-number">
                        <?php 
                        $aVenir = array_filter($schedules, function($s) { 
                            return $s['status'] === 'planifie' && strtotime($s['start_datetime']) > time();
                        });
                        echo count($aVenir);
                        ?>
                    </p>
                </div>
            </div>
            
            <div class="calendar-container">
                <h2>📆 Calendrier des plannings</h2>
                <div id="calendar"></div>
                <div style="margin-top: 20px; text-align: center;">
                    <button class="btn-primary" onclick="openAddModal()">+ Ajouter un planning</button>
                </div>
            </div>
            
            <h2>📋 Liste des plannings</h2>
            <div class="schedules-list">
                <?php foreach ($schedules as $schedule): ?>
                    <div class="schedule-item">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h3><?php echo htmlspecialchars($schedule['title']); ?></h3>
                            <span class="schedule-status status-<?php echo $schedule['status']; ?>">
                                <?php 
                                $statusLabels = ['planifie' => 'Planifié', 'en_cours' => 'En cours', 'termine' => 'Terminé', 'annule' => 'Annulé'];
                                echo $statusLabels[$schedule['status']];
                                ?>
                            </span>
                        </div>
                        <p><strong>Agent :</strong> <?php echo htmlspecialchars($schedule['agent_name']); ?> (<?php echo htmlspecialchars($schedule['secteur']); ?>)</p>
                        <p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($schedule['start_datetime'])); ?> - <?php echo date('d/m/Y H:i', strtotime($schedule['end_datetime'])); ?></p>
                        <p><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($schedule['description'] ?? 'Aucune description')); ?></p>
                        <div style="margin-top: 10px;">
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                <input type="hidden" name="action" value="update_status">
                                <select name="status" onchange="this.form.submit()" style="padding: 5px; margin-right: 10px;">
                                    <option value="planifie" <?php echo $schedule['status'] == 'planifie' ? 'selected' : ''; ?>>Planifié</option>
                                    <option value="en_cours" <?php echo $schedule['status'] == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                                    <option value="termine" <?php echo $schedule['status'] == 'termine' ? 'selected' : ''; ?>>Terminé</option>
                                    <option value="annule" <?php echo $schedule['status'] == 'annule' ? 'selected' : ''; ?>>Annulé</option>
                                </select>
                            </form>
                            <form method="POST" style="display: inline-block;" onsubmit="return confirm('Supprimer ce planning ?')">
                                <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-small btn-small-danger">🗑 Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal d'ajout -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>➕ Ajouter un planning</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Agent *</label>
                    <select name="agent_id" required>
                        <option value="">Sélectionner un agent</option>
                        <?php foreach ($agents as $agent): ?>
                            <option value="<?php echo $agent['id']; ?>">
                                <?php echo htmlspecialchars($agent['username'] . ' - ' . ($agent['secteur'] ?? 'Secteur non défini')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Titre *</label>
                    <input type="text" name="title" required placeholder="Ex: Nettoyage bâtiment A">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Détails de la tâche, équipements nécessaires..."></textarea>
                </div>
                <div class="form-group">
                    <label>Date et heure de début *</label>
                    <input type="datetime-local" name="start_datetime" required>
                </div>
                <div class="form-group">
                    <label>Date et heure de fin *</label>
                    <input type="datetime-local" name="end_datetime" required>
                </div>
                <button type="submit" class="btn-primary">Ajouter le planning</button>
            </form>
        </div>
    </div>
    
    <script>
        // Initialisation du calendrier
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'fr',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: [
                <?php foreach ($schedules as $schedule): ?>
                {
                    title: '<?php echo addslashes($schedule['title'] . ' - ' . $schedule['agent_name']); ?>',
                    start: '<?php echo $schedule['start_datetime']; ?>',
                    end: '<?php echo $schedule['end_datetime']; ?>',
                    backgroundColor: '<?php 
                        $colors = ['planifie' => '#2196F3', 'en_cours' => '#FF9800', 'termine' => '#4CAF50', 'annule' => '#F44336'];
                        echo $colors[$schedule['status']];
                    ?>',
                    borderColor: '<?php 
                        echo $colors[$schedule['status']];
                    ?>'
                },
                <?php endforeach; ?>
            ],
            eventClick: function(info) {
                alert('Détails : ' + info.event.title);
            }
        });
        calendar.render();
        
        // Gestion du modal
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        
        // Fermer le modal en cliquant en dehors
        window.onclick = function(event) {
            var modal = document.getElementById('addModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>