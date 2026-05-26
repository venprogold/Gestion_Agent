<?php
require_once 'config/database.php';
require_once 'config/upload.php';
requireRole('agent');

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $work_date = $_POST['work_date'] ?? date('Y-m-d');
    $location = trim($_POST['location'] ?? '');
    $hours_spent = $_POST['hours_spent'] ?? null;
    
    if (empty($title) || empty($description)) {
        $error = "Veuillez remplir le titre et la description.";
    } else {
        // Insérer le rapport
        $stmt = $db->prepare("INSERT INTO work_reports (user_id, title, description, work_date, location, hours_spent, status) 
                              VALUES (?, ?, ?, ?, ?, ?, 'submitted')");
        $stmt->execute([$user_id, $title, $description, $work_date, $location, $hours_spent]);
        $report_id = $db->lastInsertId();
        
        // Upload des fichiers
        if (!empty($_FILES['media_files']['name'][0])) {
            $uploaded_count = 0;
            foreach ($_FILES['media_files']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['media_files']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['media_files']['name'][$key],
                        'tmp_name' => $_FILES['media_files']['tmp_name'][$key],
                        'error' => $_FILES['media_files']['error'][$key],
                        'size' => $_FILES['media_files']['size'][$key]
                    ];
                    
                    $file_type = in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), ['mp4', 'webm', 'avi', 'mov']) ? 'video' : 'image';
                    $max_size = ($file_type === 'video') ? MAX_VIDEO_SIZE : MAX_IMAGE_SIZE;
                    
                    $upload = uploadFile($file, REPORT_DIR, ['jpg','jpeg','png','gif','webp','mp4','webm'], $max_size);
                    
                    if ($upload['success']) {
                        $mediaStmt = $db->prepare("INSERT INTO report_media (report_id, file_name, file_path, file_type, file_size) 
                                                   VALUES (?, ?, ?, ?, ?)");
                        $mediaStmt->execute([$report_id, $upload['filename'], 'uploads/reports/' . $upload['filename'], $file_type, $file['size']]);
                        $uploaded_count++;
                    }
                }
            }
        }
        
        // Notification aux responsables
        $respStmt = $db->query("SELECT id FROM users WHERE role = 'responsable'");
        $notifStmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) 
                                   VALUES (?, 'report_submitted', 'Nouveau rapport', ?, 'view_reports.php')");
        while ($resp = $respStmt->fetch()) {
            $notifStmt->execute([$resp['id'], "{$_SESSION['username']} a soumis un rapport de travail."]);
        }
        
        $success = "Rapport soumis avec succès !";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Soumettre un rapport - Agent</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .preview-item {
            position: relative;
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .preview-item img, .preview-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .remove-preview {
            position: absolute;
            top: 2px;
            right: 2px;
            background: red;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            text-align: center;
            line-height: 18px;
            font-size: 12px;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <nav class="navbar">
        <h1>📝 Soumettre un rapport de travail</h1>
        <div class="nav-links">
            <a href="agent_dashboard.php">Dashboard</a>
            <a href="my_tasks.php">Mes tâches</a>
            <a href="my_schedules.php">Horaires</a>
            <a href="submit_report.php" class="active">Rapport</a>
            <a href="my_reports.php">Mes rapports</a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Déconnexion</a>
        </div>
    </nav>
    <div class="content">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <div class="card" style="background: white; padding: 25px; border-radius: 10px;">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Titre du rapport *</label>
                    <input type="text" name="title" required placeholder="Ex: Nettoyage du bâtiment A">
                </div>
                
                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Date de travail</label>
                        <input type="date" name="work_date" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Heures passées</label>
                        <input type="number" step="0.5" name="hours_spent" placeholder="Ex: 3.5">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Lieu / Secteur</label>
                    <input type="text" name="location" placeholder="Ex: Bâtiment A, 3ème étage">
                </div>
                
                <div class="form-group">
                    <label>Description détaillée *</label>
                    <textarea name="description" rows="5" required placeholder="Décrivez le travail effectué, les difficultés rencontrées, etc."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Photos / Vidéos (optionnel)</label>
                    <input type="file" name="media_files[]" multiple accept="image/*,video/*" onchange="previewFiles(this)">
                    <div class="info-text">Formats acceptés : JPG, PNG, GIF, MP4, WEBM. Max 10Mo/image, 50Mo/vidéo.</div>
                    <div id="preview" class="preview-container"></div>
                </div>
                
                <button type="submit" class="btn-primary">Soumettre le rapport</button>
                <a href="my_reports.php" class="btn-secondary" style="background: #666; padding: 10px 20px; border-radius: 5px; color: white; text-decoration: none;">Voir mes rapports</a>
            </form>
        </div>
    </div>
</div>

<script>
function previewFiles(input) {
    const preview = document.getElementById('preview');
    preview.innerHTML = '';
    
    for (let i = 0; i < input.files.length; i++) {
        const file = input.files[i];
        const reader = new FileReader();
        const div = document.createElement('div');
        div.className = 'preview-item';
        
        reader.onload = function(e) {
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = e.target.result;
                div.appendChild(img);
            } else if (file.type.startsWith('video/')) {
                const video = document.createElement('video');
                video.src = e.target.result;
                video.controls = true;
                div.appendChild(video);
            }
            const removeBtn = document.createElement('div');
            removeBtn.className = 'remove-preview';
            removeBtn.innerHTML = '×';
            removeBtn.onclick = function() { div.remove(); };
            div.appendChild(removeBtn);
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    }
}
</script>
</body>
</html>