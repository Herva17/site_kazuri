<?php
declare(strict_types=1);

require_once __DIR__ . '/Conn.php';

/**
 * Classe Video — Gère les actions sur les vidéos (Modération, Publication).
 */
final class Video
{
    /**
     * Récupère toutes les vidéos en attente de modération.
     */
    public static function getPendingVideos(): array
    {
        $pdo = Conn::getInstance()->getPdo();
        $sql = "SELECT v.id, v.title, v.description, v.video_url, v.thumbnail_url, 
                       v.duration_seconds, v.created_at, 
                       CONCAT(u.first_name, ' ', u.last_name) as author_name
                FROM videos v
                JOIN users u ON v.user_id = u.id
                WHERE v.status = 'pending'
                ORDER BY v.created_at ASC";
        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Approuve une vidéo (statut 'published').
     */
    public static function approveVideo(int $id): bool
    {
        try {
            $pdo = Conn::getInstance()->getPdo();
            $st = $pdo->prepare("UPDATE videos SET status = 'published', published_at = NOW() WHERE id = ?");
            return $st->execute([$id]);
        } catch (Throwable $e) {
            error_log("Erreur approveVideo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rejette une vidéo avec une raison.
     */
    public static function rejectVideo(int $id, string $reason): bool
    {
        try {
            $pdo = Conn::getInstance()->getPdo();
            $st = $pdo->prepare("UPDATE videos SET status = 'rejected', reject_reason = ? WHERE id = ?");
            return $st->execute([$reason, $id]);
        } catch (Throwable $e) {
            error_log("Erreur rejectVideo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met une vidéo à la une sur la page d'accueil et l'approuve.
     */
    public static function featureVideo(int $id): bool
    {
        try {
            $pdo = Conn::getInstance()->getPdo();
            
            // Tentative 1 : avec la colonne is_featured
            $pdo->exec("UPDATE videos SET is_featured = 0");
            $st = $pdo->prepare("UPDATE videos SET status = 'published', is_featured = 1, published_at = NOW() WHERE id = ?");
            return $st->execute([$id]);
            
        } catch (Throwable $e) {
            // Si la colonne n'existe pas, on approuve juste la vidéo sans la mettre à la une
            error_log("Colonne is_featured manquante, fallback vers approveVideo : " . $e->getMessage());
            return self::approveVideo($id);
        }
    }
}