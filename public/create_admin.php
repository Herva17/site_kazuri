<?php
declare(strict_types=1);

// ⚠️ Vérifiez ces informations de connexion !
$host = 'localhost';
$dbname = 'kazur2501119'; // Le nom de votre base de données
$db_user = 'root';
$db_pass = '';

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? 'Super');
    $lastName = trim($_POST['last_name'] ?? 'Admin');

    try {
        if (empty($email) || empty($password)) {
            throw new Exception("L'email et le mot de passe sont obligatoires.");
        }

        // Connexion directe à la base de données
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // Vérifier si l'email existe déjà
        $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $st->execute([$email]);
        if ($st->fetch()) {
            throw new Exception("Cet email est déjà utilisé.");
        }

        // Créer l'utilisateur
        $st = $pdo->prepare("INSERT INTO users (email, password_hash, first_name, last_name, is_active) VALUES (?, ?, ?, ?, 1)");
        $st->execute([
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $firstName,
            $lastName
        ]);
        $userId = (int)$pdo->lastInsertId();

        // Chercher l'ID du rôle superadmin
        $st = $pdo->prepare("SELECT id FROM roles WHERE name = 'superadmin'");
        $st->execute();
        $roleId = $st->fetchColumn();
        
        // Si le rôle n'existe pas dans la table `roles`, on le crée
        if (!$roleId) {
            $pdo->prepare("INSERT INTO roles (name) VALUES ('superadmin')")->execute();
            $roleId = (int)$pdo->lastInsertId();
        }

        // Assigner le rôle à l'utilisateur dans la table user_roles
        $st = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $st->execute([$userId, $roleId]);

        $status = 'success';
        $message = "Compte Super Admin créé avec succès ! Vous pouvez maintenant vous connecter.";
    } catch (Throwable $e) {
        $status = 'error';
        $message = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créateur d'Admin — Kazuri</title>
    <style>
        body { background: #0f0f0f; color: #fff; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: #1e1e1e; border: 1px solid #333; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; }
        h1 { color: #ff2d3a; text-align: center; margin-top: 0; }
        p { text-align: center; color: #aaa; font-size: 14px; margin-bottom: 24px; }
        .field { margin-bottom: 16px; }
        label { display: block; margin-bottom: 8px; color: #ddd; font-size: 14px; }
        input { width: 100%; padding: 12px; border-radius: 6px; border: 1px solid #444; background: #121212; color: #fff; box-sizing: border-box; }
        button { width: 100%; padding: 14px; background: #ff2d3a; color: #fff; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        button:hover { background: #e02532; }
        .msg { padding: 12px; border-radius: 6px; margin-bottom: 16px; text-align: center; font-size: 14px; }
        .success { background: rgba(74, 222, 128, 0.1); color: #4ade80; border: 1px solid #4ade80; }
        .error { background: rgba(255, 75, 92, 0.1); color: #ff4b5c; border: 1px solid #ff4b5c; }
        .link { display: block; text-align: center; margin-top: 20px; color: #3ea6ff; text-decoration: none; font-size: 14px; }
        .link:hover { text-decoration: underline; }
        .warning { background: #facc15; color: #000; padding: 10px; border-radius: 6px; text-align: center; font-size: 13px; font-weight: bold; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="box">
    <h1>Kazuri Admin</h1>
    
    <div class="warning">
        ⚠️ Outil de test — À supprimer après utilisation !
    </div>

    <?php if ($message): ?>
        <div class="msg <?= $status ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($status === 'success'): ?>
        <a href="index.php?p=account" class="link">➔ Aller à la page de connexion</a>
    <?php else: ?>
        <p>Créez un compte Administrateur pour tester le Dashboard.</p>
        <form method="POST">
            <div class="field">
                <label>Prénom</label>
                <input type="text" name="first_name" value="Super" required>
            </div>
            <div class="field">
                <label>Nom</label>
                <input type="text" name="last_name" value="Admin" required>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" value="admin@kazuri.tv" required>
            </div>
            <div class="field">
                <label>Mot de passe</label>
                <input type="password" name="password" value="admin123456" required>
            </div>
            <button type="submit">Créer le compte Admin</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>