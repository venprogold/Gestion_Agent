<?php
require_once 'config/database.php';
requireRole('responsable');

$database = new Database();
$db = $database->getConnection();
$unreadCount = getUnreadNotificationsCount($db, $_SESSION['user_id']);
$profilePhoto = getUserProfilePhoto($db, $_SESSION['user_id']);

// Récupérer la liste des agents
$query = "SELECT id, username, email, telephone, secteur, created_at FROM users WHERE role = 'agent' ORDER BY username";
$stmt = $db->prepare($query);
$stmt->execute();
$agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter le nombre d'agents
$agentCount = count($agents);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Responsable - Gestion des Agents</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="dashboard">
      <nav class="navbar">
    <h1>📋 Panel Responsable</h1>
    <div class="nav-links">
        <img src="<?= $profilePhoto ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 10px;" onerror="this.src='images/default-avatar.png'">
        <span>👋 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="responsable_dashboard.php" class="active">Dashboard</a>
        <a href="schedules.php">Horaires</a>
        <a href="tasks.php">Tâches</a>
        <a href="view_reports.php">📊 Rapports agents</a>
        <a href="admin_approve.php">👥 Valider comptes</a>
        <a href="reports.php">📊 Statistiques</a>
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
            <div class="stats-cards">
                <div class="stat-card">
                    <h3>Total Agents</h3>
                    <p class="stat-number"><?php echo $agentCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Tâches en cours</h3>
                    <p class="stat-number">0</p>
                    <small>(À venir)</small>
                </div>
                <div class="stat-card">
                    <h3>Taux d'achèvement</h3>
                    <p class="stat-number">0%</p>
                    <small>(À venir)</small>
                </div>
            </div>
            
            <h2>📋 Liste des agents d'entretien</h2>
            
            <?php if (empty($agents)): ?>
                <div class="alert alert-info">Aucun agent pour le moment.</div>
            <?php else: ?>
                <table class="agents-table">
                    <thead>
                        <tr>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Secteur</th>
                            <th>Téléphone</th>
                            <th>Date d'inscription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agents as $agent): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($agent['username']); ?></td>
                            <td><?php echo htmlspecialchars($agent['email']); ?></td>
                            <td><?php echo htmlspecialchars($agent['secteur'] ?? 'Non défini'); ?></td>
                            <td><?php echo htmlspecialchars($agent['telephone'] ?? 'Non défini'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($agent['created_at'])); ?></td>
                            <td>
                                <button class="btn-small" onclick="alert('Fonctionnalité à venir')">Voir détails</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>