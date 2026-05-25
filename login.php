<?php
//require_once 'check_delayed_tasks.php';
//checkAndNotifyDelayedTasks();
require_once 'config/database.php';
startSecureSession();

// Si déjà connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT id, username, password, role, email, telephone, secteur FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
          if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $user['password'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_telephone'] = $user['telephone'];
                $_SESSION['user_secteur'] = $user['secteur'];
                
                header("Location: dashboard.php");
                exit();
    }
      
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Nom d'utilisateur introuvable.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Gestion des Agents</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1>🔧 Gestion des Agents d'Entretien</h1>
            <h2>Connexion</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit" class="btn-primary">Se connecter</button>
            </form>
            <div class="register-link" style="text-align: center; margin-top: 20px;">
    Pas encore de compte ? <a href="register.php">Créer un compte</a>
</div>
        </div>
    </div>
</body>
</html>