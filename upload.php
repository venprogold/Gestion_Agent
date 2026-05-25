<?php
// config/upload.php - Configuration des uploads
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('PROFILE_DIR', UPLOAD_DIR . 'profiles/');
define('REPORT_DIR', UPLOAD_DIR . 'reports/');

// Taille maximale : 10 Mo pour les images, 50 Mo pour les vidéos
define('MAX_IMAGE_SIZE', 10 * 1024 * 1024);
define('MAX_VIDEO_SIZE', 50 * 1024 * 1024);

// Extensions autorisées
$allowed_images = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$allowed_videos = ['mp4', 'webm', 'avi', 'mov', 'mpeg'];

function uploadFile($file, $target_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'], $max_size = 10485760) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erreur lors de l\'upload'];
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'error' => 'Fichier trop volumineux'];
    }
    
    $new_filename = uniqid() . '.' . $file_extension;
    $destination = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $new_filename];
    }
    
    return ['success' => false, 'error' => 'Erreur lors de l\'enregistrement'];
}
?>