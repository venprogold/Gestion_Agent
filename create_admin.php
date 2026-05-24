<?php
// create_admin.php - À SUPPRIMER APRÈS UTILISATION
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Vérifier si admin existe déjà
$checkStmt = $db->prepare("SELECT id FROM users WHERE username = 'admin'");
$checkStmt->execute();

if ($checkStmt->fetch()) {
    echo "❌ L'utilisateur 'admin' existe déjà.<br>";
    echo "<a href='login.php'>Aller à la connexion</a>";
} else {
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $email = 'admin@faculte.sciences';
    $role = 'responsable';
    $telephone = '77 123 45 67';
    $secteur = 'Administration';
    $is_approved = 1; // Déjà approuvé
    
    $insertStmt = $db->prepare("INSERT INTO users (username, password, email, role, telephone, secteur, is_approved) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($insertStmt->execute([$username, $password, $email, $role, $telephone, $secteur, $is_approved])) {
        echo "✅ Compte administrateur créé avec succès !<br>";
        echo "📌 Nom d'utilisateur : <strong>admin</strong><br>";
        echo "📌 Mot de passe : <strong>admin123</strong><br>";
        echo "<br><a href='login.php'>Se connecter</a>";
    } else {
        echo "❌ Erreur lors de la création.";
    }
}
?>