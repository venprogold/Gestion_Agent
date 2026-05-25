<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

// Récupérer la photo actuelle
$profilePhoto = getUserProfilePhoto($db, $user_id);

// Mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telephone = trim($_POST['telephone'] ?? '');
    $secteur = trim($_POST['secteur'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    $query = "UPDATE users SET telephone = :telephone, secteur = :secteur, email = :email WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':telephone' => $telephone,
        ':secteur' => $secteur,
        ':email' => $email,
        ':id' => $user_id
    ]);
    
    $_SESSION['user_email'] = $email;
    $_SESSION['user_telephone'] = $telephone;
    $_SESSION['user_secteur'] = $secteur;
    
    // Upload de la photo de profil
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        require_once 'config/upload.php';
        $upload = uploadFile($_FILES['profile_photo'], PROFILE_DIR, ['jpg','jpeg','png','gif','webp'], MAX_IMAGE_SIZE);
        if ($upload['success']) {
            $photoStmt = $db->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
            $photoStmt->execute([$upload['filename'], $user_id]);
            $_SESSION['profile_photo'] = $upload['filename'];
            $profilePhoto = 'uploads/profiles/' . $upload['filename'];
            $success = "Profil et photo mis à jour avec succès.";
        } else {
            $error = $upload['error'];
        }
    } else {
        $success = "Profil mis à jour avec succès.";
    }
}

// Récupérer les infos utilisateur
$userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Compter les messages non lus
$unreadMsgCount = 0;
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unreadMsgCount = (int) $stmt->fetchColumn();
} catch (Exception $e) { $unreadMsgCount = 0; }

$unreadCount = 0;
try {
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unreadCount = (int) $stmt->fetchColumn();
} catch (Exception $e) { $unreadCount = 0; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
        }
        .profile-info h2 {
            margin: 0 0 5px 0;
            color: #333;
        }
        .profile-info p {
            margin: 0;
            color: #666;
        }
        .profile-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <nav class="navbar">
        <h1>👤 Mon Profil</h1>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <?php if ($_SESSION['user_role'] === 'responsable'): ?>
                <a href="schedules.php">Horaires</a>
                <a href="tasks.php">Tâches</a>
                <a href="view_reports.php">Rapports</a>
            <?php else: ?>
                <a href="my_schedules.php">Mes horaires</a>
                <a href="my_tasks.php">Mes tâches</a>
                <a href="submit_report.php">Rapport</a>
            <?php endif; ?>
            <a href="messages.php">💬 Messages <?= $unreadMsgCount > 0 ? "<span style='background:red; color:white; border-radius:50%; padding:2px 6px;'>$unreadMsgCount</span>" : '' ?></a>
            <a href="notifications.php">🔔 Notifications <?= $unreadCount > 0 ? "<span style='background:red; color:white; border-radius:50%; padding:2px 6px;'>$unreadCount</span>" : '' ?></a>
            <a href="profile.php" class="active">Profil</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </nav>
    
    <div class="content">
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- En-tête du profil avec photo -->
        <div class="profile-header">
            <img src="<?= $profilePhoto ?>" alt="Photo de profil" class="profile-avatar" onerror="this.src='images/default-avatar.png'">
            <div class="profile-info">
                <h2><?= htmlspecialchars($user['username']) ?></h2>
                <p><?= $user['role'] === 'responsable' ? '👑 Responsable' : '🧹 Agent d\'entretien' ?></p>
                <p>📧 <?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>
        
        <!-- Formulaire d'édition -->
        <div class="profile-card">
            <h3>Modifier mes informations</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nom d'utilisateur</label>
                    <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Secteur d'affectation</label>
                    <input type="text" name="secteur" value="<?= htmlspecialchars($user['secteur'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Changer la photo de profil</label>
                    <input type="file" name="profile_photo" accept="image/*">
                    <div class="info-text">Formats : JPG, PNG, GIF. Max 10 Mo.</div>
                </div>
                
                <button type="submit" class="btn-primary">Mettre à jour</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>