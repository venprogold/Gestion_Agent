<?php
// check_delayed_tasks.php - Détection automatique des tâches en retard
// Ce fichier est destiné à être appelé automatiquement (cron ou AJAX)
// Il ne doit PAS rediriger vers login.php

// Désactiver toute redirection automatique
define('DISABLE_AUTH_REDIRECT', true);

// Activer l'affichage des erreurs pour debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

// Ne pas utiliser requireLogin() ici !

function checkAndNotifyDelayedTasks() {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            error_log("Erreur de connexion à la base de données");
            return 0;
        }
        
        // Trouver les tâches en retard
        $query = "SELECT t.id, t.title, t.assigned_to, u.username 
                  FROM tasks t
                  JOIN users u ON t.assigned_to = u.id
                  WHERE t.status != 'terminee' 
                    AND t.status != 'annulee'
                    AND t.status != 'en_retard'
                    AND t.due_date < CURDATE()";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $delayedTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updatedCount = 0;
        
        foreach ($delayedTasks as $task) {
            // Mettre à jour le statut de la tâche
            $update = "UPDATE tasks SET status = 'en_retard' WHERE id = :id";
            $upStmt = $db->prepare($update);
            $upStmt->execute([':id' => $task['id']]);
            $updatedCount++;
            
            // Vérifier si la table notifications existe
            try {
                // Vérifier l'existence de la table
                $tableCheck = $db->query("SHOW TABLES LIKE 'notifications'");
                if ($tableCheck->rowCount() > 0) {
                    // Notification pour l'agent
                    $notifQuery = "INSERT INTO notifications (user_id, type, title, message, link) 
                                   VALUES (:user_id, 'task_overdue', 'Tâche en retard', :message, 'my_tasks.php')";
                    $notifStmt = $db->prepare($notifQuery);
                    $notifStmt->execute([
                        ':user_id' => $task['assigned_to'],
                        ':message' => "La tâche « {$task['title']} » est en retard. Veuillez la terminer au plus vite."
                    ]);
                    
                    // Notification pour tous les responsables
                    $respQuery = "SELECT id FROM users WHERE role = 'responsable'";
                    $respStmt = $db->prepare($respQuery);
                    $respStmt->execute();
                    $responsables = $respStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($responsables as $resp) {
                        $notifStmt2 = $db->prepare($notifQuery);
                        $notifStmt2->execute([
                            ':user_id' => $resp['id'],
                            ':message' => "La tâche « {$task['title']} » assignée à {$task['username']} est en retard."
                        ]);
                    }
                }
            } catch (Exception $e) {
                // Ignorer les erreurs de notification
                error_log("Erreur notification: " . $e->getMessage());
            }
        }
        
        return $updatedCount;
        
    } catch (PDOException $e) {
        error_log("Erreur SQL dans check_delayed_tasks: " . $e->getMessage());
        return 0;
    } catch (Exception $e) {
        error_log("Erreur générale dans check_delayed_tasks: " . $e->getMessage());
        return 0;
    }
}

// Exécuter la vérification
$count = checkAndNotifyDelayedTasks();

// Affichage simple (pas de template HTML avec connexion)
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification des tâches en retard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            margin-bottom: 20px;
        }
        .success {
            color: green;
            background: #e8f5e9;
            padding: 10px;
            border-radius: 5px;
        }
        .info {
            color: blue;
            background: #e3f2fd;
            padding: 10px;
            border-radius: 5px;
        }
        .date {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 20px;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Vérification des tâches en retard</h1>
    <div class="date"><?= date('Y-m-d H:i:s') ?></div>
    
    <?php if ($count === false): ?>
        <div class="info">⚠️ La vérification a rencontré des problèmes. Vérifiez les logs.</div>
    <?php elseif ($count > 0): ?>
        <div class="success">✅ <?= $count ?> tâche(s) marquée(s) comme en retard.</div>
    <?php else: ?>
        <div class="info">✅ Aucune tâche en retard trouvée.</div>
    <?php endif; ?>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <a href="javascript:history.back()" class="back-link">← Retour</a>
    </div>
</div>
</body>
</html>