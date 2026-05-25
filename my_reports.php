<?php
 require_once 'config/database.php';
 requireRole('agent');

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Récupérer les rapports de l'agent
$reports = $db->prepare("SELECT * FROM work_reports WHERE user_id = ? ORDER BY created_at DESC");
$reports->execute([$user_id]);
$reports = $reports->fetchAll();


?>