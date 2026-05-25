<?php
 require_once 'config/database.php';
 requireRole('agent');

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];
?>