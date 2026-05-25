<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['conv_id'])) {
    exit();
}

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
$conv_id = intval($_GET['conv_id']);

// Marquer tous les messages reçus comme lus
$stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND receiver_id = ? AND is_read = 0");
$stmt->execute([$conv_id, $user_id]);

echo json_encode(['success' => true]);
?>