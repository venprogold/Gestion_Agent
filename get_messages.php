<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

if (!isset($_GET['conv_id'])) {
    echo json_encode(['success' => false, 'error' => 'ID conversation manquant']);
    exit();
}

$conv_id = intval($_GET['conv_id']);

// Vérifier que l'utilisateur a accès à cette conversation
$stmt = $db->prepare("SELECT id FROM conversations WHERE id = ? AND (participant1 = ? OR participant2 = ?)");
$stmt->execute([$conv_id, $user_id, $user_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
    exit();
}

// Récupérer les messages
$stmt = $db->prepare("SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC LIMIT 100");
$stmt->execute([$conv_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['success' => true, 'messages' => $messages]);
?>