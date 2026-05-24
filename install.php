<?php
// install.php - Version améliorée avec génération automatique des hashs
require_once 'config/database.php';

// Générer les hashs automatiquement
$responsableHash = password_hash('responsable123', PASSWORD_DEFAULT);
$agentHash = password_hash('agent123', PASSWORD_DEFAULT);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Lire le fichier SQL
    $sql = file_get_contents('sql/database.sql');
    
    // Remplacer les placeholders par les vrais hashs
    $sql = str_replace('$2y$10$JNNDaRPrmGUCELWmo73Gq.zrOi9gHN8BOj2VNzy7W7QNQWttB5rJS', $responsableHash, $sql);
    $sql = str_replace('$2y$10$.5anjbrLA/.BBVerjuIt9.HZluGBGSY4bD5pz1W1T5qBWSHEy5ZtO', $agentHash, $sql);
    
    // Exécuter les requêtes
    $db->exec($sql);
    
    echo "<h2 style='color: green;'>✅ Installation réussie !</h2>";
    echo "<p>Hashs générés automatiquement :</p>";
    echo "<ul>";
    echo "<li>Responsable : <code>" . htmlspecialchars($responsableHash) . "</code></li>";
    echo "<li>Agent : <code>" . htmlspecialchars($agentHash) . "</code></li>";
    echo "</ul>";
    echo "<p><a href='login.php'>Aller à la page de connexion</a></p>";
    
} catch(PDOException $e) {
    echo "<h2 style='color: red;'>❌ Erreur lors de l'installation</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Vérifie que la base de données 'gestion_agents' existe.</p>";
}
?>