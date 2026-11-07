<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 2) . '/Classes/UserClass.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Méthode non autorisée");
    }

    $user = User::current();
    if (!$user) {
        throw new Exception("Vous devez être connecté pour uploader.");
    }

    $pdo = Conn::getInstance()->getPdo();

    // Récupération des données POST
    $userId = (int)$_POST['user_id'];
    $categoryId = (int)$_POST['category_id'];
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration = (int)($_POST['duration_seconds'] ?? 0);

    if (!$title || empty($_FILES['video_file']) || empty($_FILES['thumbnail_file'])) {
        throw new Exception("Tous les champs obligatoires doivent être remplis.");
    }

    // Configuration des dossiers d'upload
    $uploadDir = dirname(__DIR__) . '/uploads/';
    $videoDir = $uploadDir . 'videos/';
    $thumbDir = $uploadDir . 'thumbnails/';

    if (!is_dir($videoDir)) mkdir($videoDir, 0777, true);
    if (!is_dir($thumbDir)) mkdir($thumbDir, 0777, true);

    // Traitement de la vidéo
    $videoFile = $_FILES['video_file'];
    $videoExt = pathinfo($videoFile['name'], PATHINFO_EXTENSION);
    $videoName = uniqid('vid_') . '.' . $videoExt;
    $videoPath = $videoDir . $videoName;
    
    if (!move_uploaded_file($videoFile['tmp_name'], $videoPath)) {
        throw new Exception("Erreur lors de la sauvegarde de la vidéo.");
    }

    // Traitement de la miniature
    $thumbFile = $_FILES['thumbnail_file'];
    $thumbExt = pathinfo($thumbFile['name'], PATHINFO_EXTENSION);
    $thumbName = uniqid('thumb_') . '.' . $thumbExt;
    $thumbPath = $thumbDir . $thumbName;
    
    if (!move_uploaded_file($thumbFile['tmp_name'], $thumbPath)) {
        throw new Exception("Erreur lors de la sauvegarde de la miniature.");
    }

    // Chemins relatifs pour la base de données (pour l'affichage sur le site)
    $videoUrl = 'uploads/videos/' . $videoName;
    $thumbnailUrl = 'uploads/thumbnails/' . $thumbName;

    // Insertion dans la base de données (Statut: pending)
    $sql = "INSERT INTO videos (user_id, category_id, title, description, video_url, thumbnail_url, duration_seconds, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId, 
        $categoryId, 
        $title, 
        $description, 
        $videoUrl, 
        $thumbnailUrl, 
        $duration
    ]);

    echo json_encode(['ok' => true, 'message' => 'Vidéo uploadée avec succès.']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
}