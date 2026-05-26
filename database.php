<?php
class Database {
    private $host = "localhost";
    private $db_name = "gestion_agents";
    private $username = "root";  // À modifier selon ta config
    private $password = "";      // À modifier selon ta config
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Erreur de connexion : " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Fonction helper pour démarrer la session sécurisée
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
function getUnreadNotificationsCount($pdo, $user_id) {
    if (!($pdo instanceof PDO)) {
        return 0;
    }
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    startSecureSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Fonction pour vérifier le rôle
function hasRole($role) {
    startSecureSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Fonction pour rediriger si non connecté
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Fonction pour rediriger si rôle incorrect
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header("Location: dashboard.php");
        exit();
    }
}
// À ajouter dans config/database.php
function getOrCreateConversation($db, $user1_id, $user2_id) {
    // Vérifier si la conversation existe déjà
    $stmt = $db->prepare("SELECT id FROM conversations WHERE (participant1 = ? AND participant2 = ?) OR (participant1 = ? AND participant2 = ?)");
    $stmt->execute([$user1_id, $user2_id, $user2_id, $user1_id]);
    $conv = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($conv) {
        return $conv['id'];
    }
    
    // Créer une nouvelle conversation
    $stmt = $db->prepare("INSERT INTO conversations (participant1, participant2) VALUES (?, ?)");
    $stmt->execute([$user1_id, $user2_id]);
    return $db->lastInsertId();
}

function getUnreadMessagesCount($db, $user_id) {
    if (!$db) return 0;
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}
// Ajoute cette fonction à la fin de config/database.php
function getUserProfilePhoto($db, $user_id) {
    $stmt = $db->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && !empty($result['profile_photo']) && file_exists('uploads/profiles/' . $result['profile_photo'])) {
        return 'uploads/profiles/' . $result['profile_photo'];
    }
    return 'images/default-avatar.png';
}
?>