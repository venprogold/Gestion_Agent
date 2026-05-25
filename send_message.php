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

// Vérifier si c'est une requête AJAX ou formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Mode AJAX (depuis messages.php)
    if (isset($_POST['conv_id']) && isset($_POST['message'])) {
        $conv_id = intval($_POST['conv_id']);
        $message = trim($_POST['message']);
        
        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message vide']);
            exit();
        }
        
        // Récupérer le destinataire
        $stmt = $db->prepare("SELECT participant1, participant2 FROM conversations WHERE id = ?");
        $stmt->execute([$conv_id]);
        $conv = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$conv) {
            echo json_encode(['success' => false, 'error' => 'Conversation introuvable']);
            exit();
        }
        
        $receiver_id = ($conv['participant1'] == $user_id) ? $conv['participant2'] : $conv['participant1'];
        
        // Insérer le message
        $stmt = $db->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$conv_id, $user_id, $receiver_id, $message]);
        
        // Mettre à jour le dernier message de la conversation
        $stmt = $db->prepare("UPDATE conversations SET last_message = ?, last_message_time = NOW() WHERE id = ?");
        $stmt->execute([$message, $conv_id]);
        
        // Notifier le destinataire (notification dans la base)
        $stmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'message', 'Nouveau message', ?, 'messages.php')");
        $stmt->execute([$receiver_id, "Vous avez reçu un nouveau message de " . $_SESSION['username']]);
        
        echo json_encode(['success' => true]);
        exit();
    }
    
    // Mode formulaire (depuis nouvelle conversation)
    elseif (isset($_POST['receiver_id']) && isset($_POST['first_message'])) {
        $receiver_id = intval($_POST['receiver_id']);
        $message = trim($_POST['first_message']);
        
        if ($receiver_id == $user_id) {
            $_SESSION['message'] = "Vous ne pouvez pas vous envoyer un message à vous-même.";
            header("Location: messages.php");
            exit();
        }
        
        // Obtenir ou créer la conversation
        require_once 'config/database.php';
        $conv_id = getOrCreateConversation($db, $user_id, $receiver_id);
        
        // Insérer le message
        $stmt = $db->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$conv_id, $user_id, $receiver_id, $message]);
        
        // Mettre à jour le dernier message
        $stmt = $db->prepare("UPDATE conversations SET last_message = ?, last_message_time = NOW() WHERE id = ?");
        $stmt->execute([$message, $conv_id]);
        
        // Notifier le destinataire
        $stmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'message', 'Nouveau message', ?, 'messages.php')");
        $stmt->execute([$receiver_id, "Vous avez reçu un nouveau message de " . $_SESSION['username']]);
        
        $_SESSION['message'] = "Message envoyé avec succès !";
        header("Location: messages.php");
        exit();
    }
}

echo json_encode(['success' => false, 'error' => 'Requête invalide']);
?>