<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'responsable') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$sender_id = $_SESSION['user_id'];
$sender_name = $_SESSION['username'];

$recipients = $_POST['recipients'] ?? [];
$message = trim($_POST['message'] ?? '');

if (empty($recipients) || empty($message)) {
    $_SESSION['message'] = "Veuillez sélectionner au moins un destinataire et écrire un message.";
    header("Location: messages.php");
    exit();
}

// Fonction pour créer une conversation
function getOrCreateConversation($db, $user1, $user2) {
    $stmt = $db->prepare("SELECT id FROM conversations WHERE (participant1 = ? AND participant2 = ?) OR (participant1 = ? AND participant2 = ?)");
    $stmt->execute([$user1, $user2, $user2, $user1]);
    $conv = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($conv) return $conv['id'];
    $stmt = $db->prepare("INSERT INTO conversations (participant1, participant2) VALUES (?, ?)");
    $stmt->execute([$user1, $user2]);
    return $db->lastInsertId();
}

$sent = 0;
foreach ($recipients as $recipient_id) {
    $conv_id = getOrCreateConversation($db, $sender_id, $recipient_id);
    
    // Insérer le message
    $stmt = $db->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$conv_id, $sender_id, $recipient_id, $message]);
    
    // Mettre à jour le dernier message
    $stmt = $db->prepare("UPDATE conversations SET last_message = ?, last_message_time = NOW() WHERE id = ?");
    $stmt->execute([$message, $conv_id]);
    
    // Notification
    $stmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'group_message', 'Message du responsable', ?, 'messages.php')");
    $stmt->execute([$recipient_id, "$sender_name vous a envoyé un message: " . substr($message, 0, 100)]);
    $sent++;
}

$_SESSION['message'] = "Message envoyé à $sent agent(s).";
header("Location: messages.php");
exit();
?>