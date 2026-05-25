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
?>