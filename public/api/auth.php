<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

try {
    $userClassPath = dirname(__DIR__, 2) . '/Classes/UserClass.php';
    
    if (!file_exists($userClassPath)) {
        throw new Exception("Fichier introuvable : " . $userClassPath);
    }
    
    require_once $userClassPath;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Méthode non autorisée");
    }

    $action = $_POST['action'] ?? '';

    // ==========================================
    // INSCRIPTION
    // ==========================================
    if ($action === 'register') {
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        
        // ✅ SÉCURITÉ : On accepte seulement 'user' ou 'contributor'
        $requestedRole = $_POST['role'] ?? 'user';
        $allowedRoles = ['user', 'contributor']; 
        $role = in_array($requestedRole, $allowedRoles, true) ? $requestedRole : 'user';
        
        $user = User::register($email, $password, $firstName, $lastName, null, $role);
        
        // ✅ On connecte l'utilisateur IMMÉDIATEMENT
        $user->login();

        // ✅ REDIRECTION INTELLIGENTE
        $redirectUrl = 'index.php?p=home'; 
        if ($user->isAdmin()) {
            $redirectUrl = 'index.php?p=admin';
        } elseif ($user->hasRole('contributor')) {
            $redirectUrl = 'index.php?p=subscription'; // ✅ CORRIGÉ ICI
        }

        echo json_encode([
            'ok'       => true,
            'message'  => 'Votre compte a été créé avec succès ! Bienvenue ' . $firstName . '.',
            'redirect' => $redirectUrl,
        ]);
        exit;
    }

    // ==========================================
    // CONNEXION
    // ==========================================
    if ($action === 'login') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $user = User::attempt($email, $password);
        if (!$user) {
            throw new Exception("Email ou mot de passe incorrect.");
        }
        
        $user->login();

        // ✅ REDIRECTION INTELLIGENTE
        $redirectUrl = 'index.php?p=home'; 
        
        if ($user->isAdmin()) {
            $redirectUrl = 'index.php?p=admin';
        } elseif ($user->hasRole('contributor')) {
            // On l'envoie d'abord vers l'abonnement
            $redirectUrl = 'index.php?p=subscription'; 
        }

        echo json_encode([
            'ok'       => true,
            'user'     => $user->toArray(),
            'redirect' => $redirectUrl,
        ]);
        exit;
    }

    throw new Exception("Action inconnue");

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Erreur serveur : ' . $e->getMessage()
    ]);
    exit;
}