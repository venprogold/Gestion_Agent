<?php
require_once 'config/database.php';
requireLogin();

// Rediriger vers le dashboard approprié selon le rôle
if ($_SESSION['user_role'] === 'responsable') {
    header("Location: responsable_dashboard.php");
} else {
    header("Location: agent_dashboard.php");
}
exit();
?>