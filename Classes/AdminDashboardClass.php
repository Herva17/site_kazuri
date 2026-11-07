<?php
declare(strict_types=1);

require_once __DIR__ . '/Conn.php';

/**
 * Classe AdminDashboard — Récupère les statistiques pour le tableau de bord Admin.
 */
final class AdminDashboard
{
    /**
     * Récupère toutes les statistiques globales.
     */
    public static function getStats(): array
    {
        $pdo = Conn::getInstance()->getPdo();
        $stats = [];

        try {
            // 1. Nombre total d'utilisateurs
            $stats['total_users'] = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

            // 2. Vidéos en attente de validation
            $stats['pending_videos'] = (int)$pdo->query("SELECT COUNT(*) FROM videos WHERE status = 'pending'")->fetchColumn();

            // 3. Nombre d'abonnés Premium (statut actif)
            $stats['premium_subscribers'] = (int)$pdo->query("SELECT COUNT(*) FROM suscriptions WHERE status = 'active'")->fetchColumn();

            // 4. Revenus du mois en cours
            $sqlRevenue = "SELECT COALESCE(SUM(amount_paid), 0) 
                           FROM suscriptions 
                           WHERE MONTH(start_date) = MONTH(CURRENT_DATE()) 
                           AND YEAR(start_date) = YEAR(CURRENT_DATE())";
            $stats['month_revenue'] = (float)$pdo->query($sqlRevenue)->fetchColumn();

        } catch (Throwable $e) {
            // Valeurs par défaut en cas d'erreur SQL (ex: tables vides au début)
            $stats = [
                'total_users' => 0,
                'pending_videos' => 0,
                'premium_subscribers' => 0,
                'month_revenue' => 0.0
            ];
        }

        return $stats;
    }

    /**
     * Récupère les dernières activités (inscriptions et soumissions de vidéos).
     */
    public static function getRecentActivities(int $limit = 5): array
    {
        $pdo = Conn::getInstance()->getPdo();
        $activities = [];

        try {
            // Récupérer les dernières inscriptions
            $users = $pdo->query("SELECT CONCAT(first_name, ' ', last_name) as name, email, created_at FROM users ORDER BY created_at DESC LIMIT $limit")->fetchAll();
            foreach ($users as $u) {
                $activities[] = [
                    'badge' => 'badge-success',
                    'type' => 'Inscription',
                    'user' => $u['name'],
                    'details' => 'Nouvel utilisateur inscrit (' . $u['email'] . ')',
                    'timestamp' => strtotime($u['created_at'])
                ];
            }

            // Récupérer les dernières vidéos soumises
            $videos = $pdo->query("SELECT title, status, created_at FROM videos ORDER BY created_at DESC LIMIT $limit")->fetchAll();
            foreach ($videos as $v) {
                $activities[] = [
                    'badge' => 'badge-warning',
                    'type' => 'Vidéo',
                    'user' => 'Créateur',
                    'details' => 'Vidéo "' . htmlspecialchars($v['title']) . '" soumise (Statut : ' . $v['status'] . ')',
                    'timestamp' => strtotime($v['created_at'])
                ];
            }

            // Trier toutes les activités par date (la plus récente en premier)
            usort($activities, function($a, $b) {
                return $b['timestamp'] - $a['timestamp'];
            });

        } catch (Throwable $e) {
            // Ignorer les erreurs pour l'affichage
        }

        return array_slice($activities, 0, $limit);
    }
}