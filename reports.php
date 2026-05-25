<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
requireRole('responsable');

$database = new Database();
$db = $database->getConnection();
$user_id = $_SESSION['user_id'];


?>