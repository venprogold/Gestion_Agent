<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
requireRole('responsable');

$database = new Database();
$db = $database->getConnection();
$admin_id = $_SESSION['user_id'];

// Vérifier que l'administrateur connecté est approuvé (pour éviter les abus)
$adminCheck = $db->prepare("SELECT is_approved FROM users WHERE id = ? AND role = 'responsable'");
$adminCheck->execute([$admin_id]);
$admin = $adminCheck->fetch();
if (!$admin || $admin['is_approved'] == 0) {
    header("Location: dashboard.php");
    exit();
}

// Approuver un compte
if (isset($_GET['approve_id'])) {
    $approve_id = intval($_GET['approve_id']);
    $stmt = $db->prepare("UPDATE users SET is_approved = 1 WHERE id = ? AND role = 'responsable'");
    $stmt->execute([$approve_id]);
    
    // Notification à l'utilisateur approuvé
    $notifStmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) 
                               VALUES (?, 'account_approved', 'Compte approuvé', 
                               'Votre compte responsable a été validé. Vous pouvez maintenant vous connecter.', 
                               'login.php')");
    $notifStmt->execute([$approve_id]);
    
    header("Location: admin_approve.php?msg=approved");
    exit();
}

// Rejeter un compte
if (isset($_GET['reject_id'])) {
    $reject_id = intval($_GET['reject_id']);
    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'responsable' AND is_approved = 0");
    $stmt->execute([$reject_id]);
    header("Location: admin_approve.php?msg=rejected");
    exit();
}

// Récupérer les comptes en attente
$pendingUsers = $db->query("SELECT id, username, email, telephone, created_at FROM users WHERE role = 'responsable' AND is_approved = 0 ORDER BY created_at ASC")->fetchAll();

$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'approved') $message = "✅ Compte approuvé avec succès.";
    if ($_GET['msg'] === 'rejected') $message = "❌ Compte rejeté et supprimé.";
}

$profilePhoto = getUserProfilePhoto($db, $admin_id);
$unreadCount = getUnreadNotificationsCount($db, $admin_id);
$unreadMsgCount = getUnreadMessagesCount($db, $admin_id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Validation des comptes - Responsable</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .pending-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #FF9800;
        }
        .btn-approve {
            background: #4CAF50;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-reject {
            background: #F44336;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
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
        <h1>👥 Validation des comptes responsables</h1>
        <div class="nav-links">
            <img src="<?= $profilePhoto ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover;" onerror="this.src='images/default-avatar.png'">
            <span>👋 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="responsable_dashboard.php">Dashboard</a>
            <a href="tasks.php">Tâches</a>
            <a href="view_reports.php">Rapports agents</a>
            <a href="admin_approve.php" class="active">Validation</a>
            <a href="messages.php">💬 Messages <?= $unreadMsgCount > 0 ? "<span style='background:red; color:white; border-radius:50%; padding:2px 6px;'>$unreadMsgCount</span>" : '' ?></a>
            <a href="notifications.php">🔔 Notifications <?= $unreadCount > 0 ? "<span style='background:red; color:white; border-radius:50%; padding:2px 6px;'>$unreadCount</span>" : '' ?></a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </nav>
    <div class="content">
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <h2>📋 Comptes responsables en attente de validation</h2>
        
        <?php if (empty($pendingUsers)): ?>
            <div class="empty-state">
                <p>✅ Aucun compte en attente de validation.</p>
            </div>
        <?php else: ?>
            <?php foreach ($pendingUsers as $user): ?>
                <div class="pending-card">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3><?= htmlspecialchars($user['username']) ?></h3>
                            <p>📧 <?= htmlspecialchars($user['email']) ?></p>
                            <?php if ($user['telephone']): ?>
                                <p>📞 <?= htmlspecialchars($user['telephone']) ?></p>
                            <?php endif; ?>
                            <p>📅 Inscrit le : <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></p>
                        </div>
                        <div>
                            <a href="?approve_id=<?= $user['id'] ?>" class="btn-approve" onclick="return confirm('Valider ce compte responsable ?')">✅ Approuver</a>
                            <a href="?reject_id=<?= $user['id'] ?>" class="btn-reject" onclick="return confirm('Rejeter et supprimer ce compte ?')">❌ Rejeter</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 10px;">
            <h3>ℹ️ Information</h3>
            <p>Les responsables doivent être validés par un responsable existant pour pouvoir accéder à l'application.</p>
        </div>
    </div>
</div>
</body>
</html>