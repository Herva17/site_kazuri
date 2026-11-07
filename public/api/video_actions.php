<?php
/* ============================================================
   ressources/api/video_actions.php
   Inclus par index.php?api=video — AUTO-SUFFISANT.
   ============================================================ */

/* 1. Charger User si nécessaire */
if (!class_exists('User')) {
    $candidates = [];
    if (defined('ROOT')) {
        $candidates[] = ROOT . '/Classes/UserClass.php';
    }
    $candidates[] = dirname(__DIR__, 3) . '/Classes/UserClass.php';
    $candidates[] = dirname(__DIR__, 2) . '/Classes/UserClass.php';

    foreach ($candidates as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

/* 2. Récupérer $currentUser si non défini */
if (!isset($currentUser)) {
    $currentUser = class_exists('User') ? User::current() : null;
}

/* 3. Récupérer $pdo si non défini */
if (!isset($pdo)) {
    try {
        if (class_exists('Conn')) {
            $pdo = Conn::getInstance()->getPdo();
        }
    } catch (Throwable $e) {}
}

/* 4. Action demandée */
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* 5. Mode debug : index.php?api=video&debug=1 */
if (isset($_GET['debug'])) {
    echo json_encode([
        'ok'    => true,
        'debug' => [
            'current_user_set'  => isset($currentUser),
            'current_user_type' => is_object($currentUser)
                ? get_class($currentUser)
                : gettype($currentUser),
            'current_user_id'   => is_object($currentUser) && method_exists($currentUser, 'getId')
                ? $currentUser->getId()
                : null,
            'pdo_set'           => isset($pdo),
            'user_class'        => class_exists('User'),
            'conn_class'        => class_exists('Conn'),
            'session_status'    => session_status(),
            'session_id'        => session_id() ?: '(none)',
            'session_keys'      => array_keys($_SESSION ?? []),
            'root'              => defined('ROOT') ? ROOT : '(undefined)',
            'action_received'   => $action ?: '(none)',
        ],
    ], JSON_PRETTY_PRINT);
    return;
}

/* 6. Vérifications */
if (!isset($pdo) || !$pdo instanceof PDO) {
    echo json_encode(['ok' => false, 'error' => 'Connexion BDD indisponible.']);
    return;
}

if (!$currentUser) {
    echo json_encode([
        'ok'         => false,
        'error'      => 'Vous devez être connecté.',
        'need_login' => true,
    ]);
    return;
}

$userId = 0;
if (is_object($currentUser)) {
    if (method_exists($currentUser, 'getId')) $userId = (int)$currentUser->getId();
    elseif (method_exists($currentUser, 'id')) $userId = (int)$currentUser->id();
    elseif (isset($currentUser->id))          $userId = (int)$currentUser->id;
} elseif (is_array($currentUser)) {
    $userId = (int)($currentUser['id'] ?? 0);
}

if ($userId <= 0) {
    echo json_encode([
        'ok'         => false,
        'error'      => 'Session invalide.',
        'need_login' => true,
    ]);
    return;
}

if (in_array($action, ['like', 'subscribe'], true)) {
    $sessionToken = $_SESSION['csrf_token'] ?? null;
    $requestToken = $_POST['csrf_token'] ?? null;

    if (
        ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST'
        || !is_string($sessionToken)
        || $sessionToken === ''
        || !is_string($requestToken)
        || !hash_equals($sessionToken, $requestToken)
    ) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Vérification CSRF échouée.']);
        return;
    }
}

/* 7. Routage */
try {
    switch ($action) {

        case 'like': {
            $videoId = (int)($_POST['video_id'] ?? 0);
            if ($videoId <= 0) {
                echo json_encode(['ok' => false, 'error' => 'Vidéo invalide.']);
                return;
            }

            $st = $pdo->prepare("SELECT id FROM video_likes WHERE video_id = ? AND user_id = ?");
            $st->execute([$videoId, $userId]);
            $existing = $st->fetchColumn();

            if ($existing) {
                $pdo->prepare("DELETE FROM video_likes WHERE id = ?")->execute([$existing]);
                $pdo->prepare("UPDATE videos SET likes = GREATEST(likes - 1, 0) WHERE id = ?")
                    ->execute([$videoId]);
                $liked = false;
            } else {
                $pdo->prepare("INSERT INTO video_likes (video_id, user_id) VALUES (?, ?)")
                    ->execute([$videoId, $userId]);
                $pdo->prepare("UPDATE videos SET likes = likes + 1 WHERE id = ?")
                    ->execute([$videoId]);
                $liked = true;
            }

            $st = $pdo->prepare("SELECT COUNT(*) FROM video_likes WHERE video_id = ?");
            $st->execute([$videoId]);
            $count = (int)$st->fetchColumn();

            echo json_encode(['ok' => true, 'liked' => $liked, 'count' => $count]);
            return;
        }

        case 'subscribe': {
            $channelId = (int)($_POST['channel_id'] ?? 0);
            if ($channelId <= 0) {
                echo json_encode(['ok' => false, 'error' => 'Chaîne invalide.']);
                return;
            }
            if ($channelId === $userId) {
                echo json_encode(['ok' => false, 'error' => 'Impossible de s\'abonner à soi-même.']);
                return;
            }

            $st = $pdo->prepare("SELECT id FROM subscriptions WHERE channel_id = ? AND subscriber_id = ?");
            $st->execute([$channelId, $userId]);
            $existing = $st->fetchColumn();

            if ($existing) {
                $pdo->prepare("DELETE FROM subscriptions WHERE id = ?")->execute([$existing]);
                $subscribed = false;
            } else {
                $pdo->prepare("INSERT INTO subscriptions (channel_id, subscriber_id) VALUES (?, ?)")
                    ->execute([$channelId, $userId]);
                $subscribed = true;
            }

            $st = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE channel_id = ?");
            $st->execute([$channelId]);
            $total = (int)$st->fetchColumn();

            echo json_encode(['ok' => true, 'subscribed' => $subscribed, 'total' => $total]);
            return;
        }

        case 'download': {
            $videoId = (int)($_POST['video_id'] ?? 0);
            if ($videoId <= 0) {
                echo json_encode(['ok' => false, 'error' => 'Vidéo invalide.']);
                return;
            }

            $st = $pdo->prepare("
                SELECT video_url, title, is_premium
                FROM videos
                WHERE id = ? AND status = 'published'
            ");
            $st->execute([$videoId]);
            $video = $st->fetch(PDO::FETCH_ASSOC);

            if (!$video) {
                echo json_encode(['ok' => false, 'error' => 'Vidéo introuvable.']);
                return;
            }
            if (empty($video['video_url'])) {
                echo json_encode(['ok' => false, 'error' => 'Fichier indisponible.']);
                return;
            }

            if (!empty($video['is_premium'])) {
                try {
                    $st = $pdo->prepare("
                        SELECT 1 FROM user_subscriptions
                        WHERE user_id = ? AND status = 'active'
                        AND (expires_at IS NULL OR expires_at > NOW())
                        LIMIT 1
                    ");
                    $st->execute([$userId]);
                    if (!$st->fetchColumn()) {
                        echo json_encode([
                            'ok'           => false,
                            'error'        => 'Un abonnement premium est requis.',
                            'need_premium' => true,
                        ]);
                        return;
                    }
                } catch (Throwable $e) {}
            }

            $safeName = preg_replace('/[^A-Za-z0-9_\-]+/u', '_', $video['title']);

            echo json_encode([
                'ok'       => true,
                'url'      => $video['video_url'],
                'filename' => ($safeName ?: 'video_' . $videoId) . '.mp4',
            ]);
            return;
        }

        default:
            echo json_encode(['ok' => false, 'error' => 'Action inconnue.']);
            return;
    }
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'Erreur : ' . $e->getMessage()]);
}