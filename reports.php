<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
requireRole('responsable');

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// 1. CORRECTION : Supprimer les alias avec des mots réservés
$taskstats = $db->query("SELECT
    SUM(CASE WHEN status = 'terminee' THEN 1 ELSE 0 END) as completed,
    SUM(CASE WHEN status = 'en_cours' THEN 1 ELSE 0 END) as in_progress,
    SUM(CASE WHEN status = 'a_faire' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'en_retard' THEN 1 ELSE 0 END) as task_delayed,
    SUM(CASE WHEN status = 'annulee' THEN 1 ELSE 0 END) as task_cancelled
    FROM tasks")->fetch(PDO::FETCH_ASSOC);

//2. Taches par agent (Top 5)    
$topAgents = $db->query("SELECT 
    u.username,
    COUNT(t.id) as total_tasks,
    SUM(CASE WHEN t.status = 'terminee' THEN 1 ELSE 0 END) as completed_tasks,
    ROUND(AVG(CASE WHEN t.status = 'terminee' AND t.actual_hours IS NOT NULL THEN t.actual_hours ELSE NULL END), 1) as avg_hours
    FROM users u 
    LEFT JOIN tasks t ON u.id = t.assigned_to
    WHERE u.role = 'agent'
    GROUP BY u.id
    ORDER BY completed_tasks DESC
    LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

//3. Heures travaillées par mois (derniers 6 mois)
$hoursByMonth = $db->query("SELECT 
    DATE_FORMAT(work_date, '%Y-%m') as month,
    SUM(hours_worked) as total_hours
    FROM work_logs
    WHERE work_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(work_date, '%Y-%m')
    ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);

//4. Évaluations moyennes par agent
    $avgRatings = $db->query("SELECT
    u.username,
    ROUND(AVG(r.rating), 1) as avg_rating,
    COUNT(pr.id) as review_count
    FROM users u
    LEFT JOIN performance_reviews pr ON u.id = pr.agent_id
    WHERE u.role = 'agent'
    GROUP BY u.id
    ORDER BY avg_rating DESC")->fetchAll(PDO::FETCH_ASSOC);

//5. Taches complétées par mois (derniers 6 mois)
$completedByMonth = $db->query("SELECT
DATE_FORMAT(completed_at, '%Y-%m') as month,
COUNT (*) as task_count
FROM tasks
WHERE status = 'terminee' AND completed_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
GROUP BY DATE_FORMAT(completed_at, '%Y-%m')
ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);

//6. Rapport de performance global
?>