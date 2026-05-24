<?php
// cron_tasks.php - Version autonome pour cron
// Ce fichier N'INCLUT PAS config/database.php pour éviter les redirections

// Connexion directe à la BDD sans passer par config/database.php
$host = 'localhost';
$dbname = 'gestion_agents';
$username_db = 'root';
$password_db = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Trouver les tâches en retard
$query = "SELECT t.id, t.title, t.assigned_to, u.username 
          FROM tasks t
          JOIN users u ON t.assigned_to = u.id
          WHERE t.status != 'terminee' 
            AND t.status != 'annulee'
            AND t.status != 'en_retard'
            AND t.due_date < CURDATE()";

$stmt = $pdo->prepare($query);
$stmt->execute();
$delayedTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updatedCount = 0;

foreach ($delayedTasks as $task) {
    // Mettre à jour le statut
    $update = $pdo->prepare("UPDATE tasks SET status = 'en_retard' WHERE id = ?");
    $update->execute([$task['id']]);
    $updatedCount++;
    
    // Vérifier si la table notifications existe
    try {
        $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link) 
                       VALUES (?, 'task_overdue', 'Tâche en retard', ?, 'my_tasks.php')")
            ->execute([
                $task['assigned_to'],
                "La tâche « {$task['title']} » est en retard. Veuillez la terminer au plus vite."
            ]);
    } catch (Exception $e) {
        // Table notifications peut ne pas exister
    }
}

// Affichage (uniquement pour test)
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cron - Tâches en retard</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f0f0f0; }
        .box { max-width: 500px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        h1 { color: #667eea; }
        .success { color: green; }
        .date { color: #666; font-size: 0.9em; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="box">
    <h1>🔍 Vérification des tâches en retard (Autonome)</h1>
    <div class="date"><?= date('Y-m-d H:i:s') ?></div>
    
    <?php if ($updatedCount > 0): ?>
        <p class="success">✅ <?= $updatedCount ?> tâche(s) marquée(s) comme en retard.</p>
    <?php else: ?>
        <p>✅ Aucune tâche en retard trouvée.</p>
    <?php endif; ?>
    
    <p style="margin-top: 20px;"><a href="javascript:history.back()">← Retour</a></p>
</div>
</body>
</html>