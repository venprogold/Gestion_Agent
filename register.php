<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/database.php';
startSecureSession();

// Si déjà connecté, rediriger
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'agent';
    $telephone = trim($_POST['telephone'] ?? '');
    $secteur = trim($_POST['secteur'] ?? '');
    
    // Validations
    if (empty($username) || empty($password) || empty($email)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "L'adresse email n'est pas valide.";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        
        // Vérifier si l'utilisateur existe déjà
        $checkStmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        
        if ($checkStmt->fetch()) {
            $error = "Ce nom d'utilisateur ou cet email est déjà utilisé.";
        } else {
            // Hash du mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Si responsable, is_approved = 0 (en attente), sinon 1
            $is_approved = ($role === 'responsable') ? 0 : 1;
            
            // Insertion du nouvel utilisateur
            $insertStmt = $db->prepare("INSERT INTO users (username, password, email, role, telephone, secteur, is_approved) 
                                        VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if ($insertStmt->execute([$username, $hashed_password, $email, $role, $telephone, $secteur, $is_approved])) {
                
                if ($role === 'responsable') {
                    // Notification aux responsables existants (approuvés)
                    $respStmt = $db->prepare("SELECT id FROM users WHERE role = 'responsable' AND is_approved = 1");
                    $respStmt->execute();
                    $responsables = $respStmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $notifStmt = $db->prepare("INSERT INTO notifications (user_id, type, title, message, link) 
                                               VALUES (?, 'approval_needed', 'Nouveau compte responsable à valider', 
                                               'L\'utilisateur « $username » a créé un compte responsable. Veuillez le valider.', 
                                               'admin_approve.php')");
                    
                    foreach ($responsables as $resp) {
                        $notifStmt->execute([$resp['id']]);
                    }
                    
                    $success = "Compte responsable créé avec succès ! En attente de validation par un autre responsable.";
                } else {
                    $success = "Compte agent créé avec succès ! Vous pouvez maintenant vous connecter.";
                }
            } else {
                $error = "Erreur lors de la création du compte. Veuillez réessayer.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Gestion des Agents</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .register-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
        }
        .register-card h1 {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #333;
            text-align: center;
        }
        .register-card h2 {
            margin-bottom: 20px;
            color: #667eea;
            text-align: center;
            font-size: 1.2em;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            margin-top: 10px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info-text {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
        .required {
            color: #F44336;
        }
        .secteur-field {
            transition: all 0.3s ease;
        }
        .secteur-field.hidden {
            display: none;
        }
    </style>
    <script>
        function toggleSecteurField() {
            const role = document.getElementById('role').value;
            const secteurField = document.getElementById('secteurField');
            if (role === 'responsable') {
                secteurField.style.display = 'none';
            } else {
                secteurField.style.display = 'block';
            }
        }
    </script>
</head>
<body>
<div class="register-container">
    <div class="register-card">
        <h1>🔧 Gestion des Agents d'Entretien</h1>
        <h2>Créer un compte</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <div class="login-link">
                <a href="login.php">→ Se connecter ←</a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom d'utilisateur <span class="required">*</span></label>
                        <input type="text" name="username" required placeholder="ex: jean.dupont">
                    </div>
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" required placeholder="jean@faculte.fr">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Mot de passe <span class="required">*</span></label>
                        <input type="password" name="password" required placeholder="6 caractères minimum">
                    </div>
                    <div class="form-group">
                        <label>Confirmer <span class="required">*</span></label>
                        <input type="password" name="confirm_password" required placeholder="retapez le mot de passe">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Rôle <span class="required">*</span></label>
                        <select name="role" id="role" required onchange="toggleSecteurField()">
                            <option value="agent">Agent d'entretien</option>
                            <option value="responsable">Responsable</option>
                        </select>
                        <div class="info-text" id="roleInfo">Les comptes responsables doivent être validés par un responsable existant.</div>
                    </div>
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" name="telephone" placeholder="Optionnel">
                    </div>
                </div>
                
                <div class="form-group secteur-field" id="secteurField">
                    <label>Secteur d'affectation</label>
                    <input type="text" name="secteur" placeholder="Ex: Bâtiment A - Niveau 1">
                    <div class="info-text">Indiquez votre secteur de travail habituel.</div>
                </div>
                
                <button type="submit" class="btn-primary">Créer mon compte</button>
            </form>
            
            <div class="login-link">
                Déjà un compte ? <a href="login.php">Se connecter</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Initialiser l'affichage du champ secteur au chargement
    document.addEventListener('DOMContentLoaded', function() {
        toggleSecteurField();
    });
</script>
</body>
</html>